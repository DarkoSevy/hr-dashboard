# System Architecture

## Overview

PTS HRMS is a server-rendered PHP 8 MVC application backed by MySQL, with a
token-authenticated JSON REST API for ERP integration. It deliberately avoids a
heavyweight framework: the entire kernel is ~6 small classes, so the IT team can
audit and extend every line.

```
Browser ──HTTP──▶ Apache/Nginx ──▶ public/index.php (front controller)
                                        │
                                   App (router)
                              ┌─────────┼──────────────┐
                        Web modules   /api/v1/*    /storage/*
                        (session +    (Bearer      (authenticated
                         CSRF + RBAC)  API key)     file streaming)
                              │           │
                        Controllers ──▶ Core services
                              │      (Auth · Audit · Mailer · Database)
                            Views            │
                        (Bootstrap 5)      MySQL
```

## Request lifecycle (web)

1. `public/.htaccess` rewrites everything to `public/index.php`.
2. `App\Core\App` starts the session (idle timeout enforced), parses the URL as
   `/module[/id][/action]` and maps it to a controller method:
   `GET /employees/5/edit → EmployeeController::edit(5)`.
3. Public routes (`login`, `otp`, `logout`) bypass auth; everything else requires
   a session, and all POSTs are CSRF-verified.
4. Controllers enforce permissions via `Auth::require('module.action')`, run
   queries through the prepared-statement `Database` wrapper, write to the
   `Audit` trail, and render a view inside the shared layout.

## Two kinds of modules

* **Dedicated controllers** for workflow-heavy features: Employees (profile with
  tabbed history), Leave (two-step approval + balances), Attendance (GPS clock
  in/out + lateness), Payroll (PAYE/RSSB computation + CSV export), Reports,
  Audit, Notifications, Auth, the REST API and secure file streaming.
* **Metadata modules** (`app/Config/modules.php`): ~25 CRUD modules (departments,
  vacancies, drivers, assets, disciplinary…) are declared as data — table name,
  permissions, list columns, form fields (text/select/relation/file/…).
  `ResourceController` renders lists and forms, validates, uploads files, audits
  changes. Adding a new module is a config entry, not new code.

## Security model

| Concern | Implementation |
|---|---|
| Authentication | Session (bcrypt), account lockout after 5 failures, optional email OTP 2FA |
| Authorization | 13 roles × 36 fine-grained permissions (`role_permissions` matrix), checked server-side per action and reflected in the sidebar |
| Password policy | 8+ chars, upper/lower/digit/symbol; expiry window configurable |
| CSRF | Per-session token on every POST form |
| SQL injection | 100 % prepared statements via the PDO wrapper |
| XSS | All view output through `e()` (htmlspecialchars) |
| Uploads | Extension + size whitelist, random filenames, stored **outside** the web root, streamed by `StorageController` with path-traversal guard, only to authenticated users |
| API | SHA-256-hashed bearer keys, resource/column whitelists, audited writes |
| Audit | Every create/update/delete/login/export logged with old/new values, IP, user agent |
| Session | HttpOnly + SameSite cookies, idle timeout, ID regeneration on login |

## Integration points

* **Finance / QuickBooks** — payroll periods export locked CSV with RSSB, PAYE,
  bank details per employee; the REST API exposes the same data as JSON.
* **Operations / Fleet** — `drivers`, `vehicles`, `driver_trips` and
  `driver_vehicle_assignments` are readable/writable through `/api/v1`, so the
  Fleet module can push trips and pull driver availability/expiry data.
* **Email** — `App\Core\Mailer` uses PHPMailer over SMTP when installed
  (Outlook/Google Workspace compatible) and falls back to `mail()`. All sends
  are best-effort so HR workflows never fail on SMTP outages.
* **RSSB / RRA** — statutory numbers stored per employee; contribution and PAYE
  rates configurable in `settings`; export formats extendable in
  `PayrollController`.
* **SMS-ready** — `notifications.channel` already supports `sms`; plug a gateway
  into `Mailer::notify()`.

## Scaling & operations

* Stateless PHP behind a load balancer works out of the box (move sessions to
  Redis via `session.save_handler` when clustering).
* All hot query paths are covered by indexes (see schema); list endpoints are
  paginated at the API and capped in the UI (DataTables client paging).
* Frontend libraries are vendored under `public/assets/vendor/` — the system
  runs on an intranet with **no internet access**.
* Daily backups: `mysqldump pts_hrms` + `storage/uploads/` (documented in the
  installation guide).
