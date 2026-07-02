# REST API Reference

Base URL: `https://<host>/api/v1` · All payloads JSON (UTF-8).

## Authentication

Every request needs a bearer token:

```
Authorization: Bearer <api-key>
```

Keys are stored **SHA-256-hashed** in `users.api_key`. To issue one:

```sql
-- generate a long random key, give it to the integrating system, store its hash:
UPDATE users SET api_key = SHA2('<the-key>', 256) WHERE username = 'integration-fleet';
```

Missing/invalid tokens → `401 {"error": "..."}`.
All write operations are recorded in the audit trail as `api_create` /
`api_update` / `api_delete` under the token's user.

## Resources

| Resource | Table | Notes |
|---|---|---|
| `employees` | employees | full register |
| `departments` | departments | |
| `positions` | positions | |
| `leaves` | leave_requests | statuses: pending_supervisor, pending_hr, approved, rejected, cancelled |
| `attendance` | attendance_records | for biometric-device ingestion (`source: "biometric"`) |
| `drivers` | drivers | license/permit/medical expiry for Fleet |
| `vehicles` | vehicles | |
| `trainings` | training_sessions | |
| `assets` | assets | |
| `vacancies` | job_vacancies | |
| `notifications` | notifications | push a message to a user |

### Endpoints (uniform for every resource)

| Method & path | Action | Success |
|---|---|---|
| `GET /api/v1/{resource}` | List. Query params: `page`, `per_page` (≤100), plus equality filters on any writable column, e.g. `?status=active&department_id=2` | `200 {data:[…], meta:{page, per_page, total}}` |
| `GET /api/v1/{resource}/{id}` | Fetch one | `200 {data:{…}}` |
| `POST /api/v1/{resource}` | Create (JSON body; unknown fields ignored — column whitelist) | `201 {data:{…}}` |
| `PUT /api/v1/{resource}/{id}` | Partial update | `200 {data:{…}}` |
| `DELETE /api/v1/{resource}/{id}` | Delete | `204` |

Errors: `404` unknown resource/id · `422` invalid/empty body or DB constraint
(response includes `detail`) · `405` unsupported method.

### Statistics

`GET /api/v1/stats` → headcount, drivers, on-leave today, pending leave,
attendance today, open vacancies, per-department headcount. Ideal for ERP
dashboards.

## Examples

```bash
# Fleet module: drivers whose license is on file
curl -H "Authorization: Bearer $KEY" \
  "https://hrms.pts.rw/api/v1/drivers?status=available&per_page=50"

# Biometric bridge: push a clock-in
curl -X POST -H "Authorization: Bearer $KEY" -H "Content-Type: application/json" \
  -d '{"employee_id":4,"work_date":"2026-07-02","clock_in":"2026-07-02 07:58:00","source":"biometric","status":"present"}' \
  https://hrms.pts.rw/api/v1/attendance

# Finance: monthly headcount check
curl -H "Authorization: Bearer $KEY" https://hrms.pts.rw/api/v1/stats
```

## Conventions

* Dates `YYYY-MM-DD`, datetimes `YYYY-MM-DD HH:MM:SS` (Africa/Kigali).
* Money is decimal RWF.
* IDs are integers; lists are ordered newest-first.
* CORS is open (`Access-Control-Allow-Origin: *`) — restrict at the proxy if
  the API must be intranet-only.
