# User Manual

## Signing in

Open the HRMS URL, enter your username (or email) and password. If your account
has two-factor authentication enabled, a 6-digit code is emailed to you; it
expires after 10 minutes. Five wrong passwords lock the account for 15 minutes.

The sidebar only shows the modules **your role** can access. The moon/sun button
(top right) toggles dark mode; press **/** anywhere to jump to search, which
filters the table on the current page.

## Roles at a glance

| Role | Typical user | Can |
|---|---|---|
| Administrator / IT Administrator | IT | Everything, user accounts, settings |
| HR Manager | Head of HR | All HR modules incl. final leave approval, payroll |
| HR Officer | HR staff | Day-to-day records, recruitment, no payroll |
| Managing Director | MD | View everything, approve requests & leave (HR stage) |
| Department Manager / Supervisor | Line managers | Own team, supervisor-stage leave approval, performance |
| Finance Officer | Finance | Payroll preparation & exports |
| Operations / Fleet Manager | Ops | Drivers, vehicles, trips, incidents, supervisor approvals |
| Employee | Everyone | Self-service: leave, clock in/out, requests, own profile |
| Auditor / Compliance Officer | Oversight | Read-only + audit trail; compliance also manages disciplinary & H&S |

Lost your password? Use **Forgot your password?** on the sign-in page — a reset
link valid for one hour is emailed to your account address.

## Employee self-service

* **My Account** — avatar menu → My Account: change your password, update your
  own contact details (phone, email, address, emergency contact — changes are
  audit-logged), and switch email two-factor authentication on or off.
* **My Payslips** — avatar menu → My Payslips: once Finance locks a payroll
  period, your payslip appears here with a print-friendly view (use the
  browser's Print → Save as PDF).
* **My profile** — avatar menu → My Profile. Shows your details, documents,
  leave balances/history, assets, performance, training and (for drivers) your
  license and trip data. Ask HR to correct anything.
* **Request leave** — Leave → *Request Leave*. Pick the type and dates; working
  days are computed automatically (weekends and public holidays excluded) and
  checked against your balance. Attach a document where required (e.g. sick
  note). Track status on the Leave page; you can cancel while it's pending.
  You are notified in-app and by email at each approval step.
* **Clock in / out** — Attendance → big green/blue button. Your browser may ask
  for location; GPS is stored with the record. Arriving after your shift's grace
  period marks you late.
* **Internal requests** — Requests → New: travel, training, salary advance,
  equipment, IT support, vehicle assignment or document requests. Approvers are
  notified; watch the status column.

## HR operations

* **Employees** — full register with search/sort/export. *New Employee* captures
  personal, contact, employment, salary/statutory and insurance data; the
  employee number (PTS-####) is generated automatically and leave balances for
  the current year are opened. The profile page has tabs for every history.
* **Document vault** — upload on the employee profile (Documents tab) or in the
  Document Vault module. Re-uploading the same document type creates **version
  n+1** and keeps the old file. Set an expiry date to feed the dashboard alerts.
* **Leave approvals** — requests flow *employee → supervisor → HR*. Approvers see
  ✓ / ✗ buttons on the Leave page; on final approval the balance is deducted and
  the employee notified.
* **Attendance register** — daily view with lateness minutes and GPS link; pick
  any date. Late/absence reports live under Reports.
* **Recruitment** — create a Vacancy (publish it), register Applicants, schedule
  Interviews with scores, issue Offers. Move applicants along the funnel via the
  stage field; park good candidates in *Talent Pool*. To hire, click the
  **convert** button on the applicant row: the employee record is created
  automatically (number assigned, vacancy's department/position applied, accepted
  offer's salary and start date used, CV moved into the document vault, leave
  balances opened) and you land on the employee form to complete the details.
* **Org chart** — People → Org Chart: the live reporting tree built from each
  employee's manager, plus department cards with managers and headcount.
* **HR calendar** — Time & Pay → HR Calendar: month view of public holidays,
  approved leave and training sessions; browse months with the arrows.
* **Payroll preparation** — Payroll → pick a month → *Generate*: entries are
  created for all active staff with RSSB (6 % employee / 8 % employer) and PAYE
  (0/10/20/30 % monthly brackets) precomputed. Edit a row to add allowances,
  overtime, bonuses or deductions — totals recalculate. *Lock* the period, then
  *Export CSV* for Finance / QuickBooks.
* **Drivers** — keep license/permit/medical expiry current; the dashboard warns
  60–90 days ahead. Record trips (fuel, distance, client rating) and incidents
  (accidents, fines, warnings, suspensions). The *Driver Performance Ranking*
  report consolidates it.
* **Performance & training** — create reviews per period with KPI scores and
  recommendations; set goals with progress %. Training: course catalogue →
  sessions → participants with attendance, scores and certificate expiry.
* **Disciplinary & H&S** — cases with category, action taken (verbal → dismissal),
  evidence upload and appeal outcome; a timeline shows on the employee profile.
  Medical checkups and incident reports (incl. insurance claim status) live under
  Workplace.
* **Assets** — register assets, assign to employees with due-back dates, record
  condition out/in on return.
* **Reports** — 14 canned reports (statistics, gender/age, leave, attendance,
  late, turnover, contract expiry, probation, birthdays, recruitment, training,
  drivers, performance). Every report exports to CSV (opens in Excel).
* **Audit trail** — every action with who/when/old/new/IP/browser; exportable.

## Notifications

The bell icon shows unread items: leave decisions, approval requests, and (when
email is configured) the same messages by email. Expiry warnings for contracts,
licenses, medical certificates and insurance appear on the executive dashboard.

With the daily notifier scheduled (see the installation guide), HR and the
affected employee are automatically alerted about: expiring contracts, driving
licenses, permits, driver medical exams, medical insurance, vault documents and
training certificates; probation periods ending; today's birthdays; upcoming
training; and (on the 5th of each month, to HR/Finance) the RSSB declaration
deadline. The same job marks unexplained no-shows as absent and expires overdue
contracts.

## System settings (administrators)

Insight & Admin → Settings: company identity, password expiry, session timeout,
the expiry-alert window, and RSSB contribution rates. Changes apply immediately
and are audit-logged.
