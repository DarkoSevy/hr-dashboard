# Future Enhancement Roadmap

## Phase 1 — Hardening (first quarter after go-live)

* Password self-service reset (schema ready: `password_resets`).
* TOTP authenticator-app 2FA in addition to email OTP.
* Server-side pagination for very large registers (DataTables ajax mode).
* PDF exports (payslips, offer letters, reports) via dompdf; branded templates.
* Redis session + query cache when clustering.
* Database migration runner (`database/migrations/`) with versioning.

## Phase 2 — Deeper HR automation

* Scheduled notifier (cron) that emails upcoming birthdays, contract/probation
  ends, license & medical expiries, training due and RSSB deadlines — all the
  queries already power the dashboard; wrap them in a `bin/notify.php` cron.
* Leave calendar view (team calendar, conflict highlighting) and carry-over
  automation at year end.
* Org-chart visualisation from `departments.parent_id` / `positions.reports_to`.
* Payslip portal: publish locked payroll entries to employee self-service.
* Recruitment: public careers page posting `published` vacancies with online
  application form feeding `applicants`; offer-letter generation.
* Onboarding/offboarding checklists (asset return, access revocation).

## Phase 3 — ERP integration

* QuickBooks Online API push (replace CSV) for payroll journals.
* Fleet module two-way sync: trips and vehicle assignments in real time,
  driver-availability webhooks.
* RSSB / RRA e-declaration file formats generated per payroll period.
* Single sign-on (Microsoft Entra ID / Google Workspace) via OIDC.
* Outlook / Google Calendar sync for leave and training events.

## Phase 4 — Workforce intelligence & mobile

* Mobile PWA for clock in/out (offline queue + GPS), leave and payslips.
* Biometric device integration service (ZKTeco et al.) posting to
  `/api/v1/attendance`.
* Driver fatigue analytics from trip/attendance patterns; safety scorecards.
* Predictive turnover and absence dashboards; KPI benchmarking per department.
* SMS gateway (e.g. local telco API) behind the existing `sms` channel.
* Document e-signatures for contracts and warning letters.

## Technical debt watchlist

* Move inline enum vocabularies into a shared PHP source to guarantee UI/schema
  parity when they evolve.
* Add automated test suite (PHPUnit for core services, Playwright smoke flows).
* CSP headers and per-role rate limiting.
