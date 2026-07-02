# PTS HRMS — Human Resource Management System

Enterprise HR platform for **Premier Transport & Tour Services Ltd (PTS Rwanda)** —
a luxury transportation, chauffeur, airport-transfer, corporate mobility, car rental,
safari and tourism company. The HRMS is one module of the wider PTS ERP and is designed
to integrate with Operations, Fleet, Finance, Procurement, Administration, Compliance
and the Managing Director's office.

**Stack:** PHP 8+, MySQL 8 / MariaDB 10.6+, Bootstrap 5, Chart.js, DataTables,
SweetAlert2, vanilla-JS AJAX, REST API. Custom lightweight MVC — no framework required.

## Quick start

```bash
# 1. Database
mysql -uroot -p --default-character-set=utf8mb4 < database/schema.sql
mysql -uroot -p --default-character-set=utf8mb4 < database/seed.sql

# 2. Run (dev server; use Apache + public/.htaccess in production)
DB_USER=youruser DB_PASS=yourpass php -S 0.0.0.0:8080 -t public public/router.php
```

Sign in at `http://localhost:8080` with **admin / Admin@2026** — change the password
immediately (Insight & Admin → User Accounts).

## Modules

| Area | Modules |
|---|---|
| People | Employees (full profile, photo, statutory data), Departments, Positions, Branches, Contracts, Document Vault (versioned, expiry alerts) |
| Time & Pay | Leave (2-step workflow, balances, holiday-aware day calculation), Attendance (GPS clock in/out, lateness), Shifts, Holidays, Payroll preparation (RSSB + PAYE, CSV export for Finance/QuickBooks) |
| Talent | Vacancies, Applicants (funnel), Interviews, Offers, Performance reviews & KPIs, Goals, Training courses/sessions/participants with certificate expiry |
| Fleet | Drivers (license/permit/medical expiry), Vehicles, Trips (fuel, ratings), Driver incidents (accidents, fines, warnings) |
| Workplace | Internal requests (travel, salary advance, IT…), Assets & assignments, Disciplinary cases with appeals, Medical checkups, Incident reports, Announcements |
| Admin | 14 canned HR reports with CSV export, role-based User Accounts (13 roles, 36 permissions), full Audit Trail, REST API (`/api/v1`), in-app + email notifications, optional email 2FA |

## Layout

```
hrms/
├── public/            web root: front controller, .htaccess, assets (all vendored — works offline)
├── app/
│   ├── Config/        config.php (env-driven), modules.php (metadata-driven CRUD modules)
│   ├── Core/          router/kernel, PDO wrapper, Auth/RBAC, Audit, Mailer, View
│   ├── Controllers/   dedicated (Employee, Leave, Attendance, Payroll, Report, Api…)
│   │                  + ResourceController powering ~25 metadata modules
│   └── Views/         Bootstrap 5 views (layout, dashboard, per-module, generic resource)
├── database/          schema.sql (44 tables), seed.sql (roles, permissions, demo data)
├── storage/uploads/   uploaded files (outside web root, streamed with auth)
└── docs/              architecture, ERD, installation, user manual, API reference, roadmap
```

See [`docs/`](docs/) for the full documentation set.
