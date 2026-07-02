# Installation Guide

## Requirements

* PHP **8.1+** with `pdo_mysql`, `json`, `fileinfo` (standard on 8.x)
* MySQL **8.0+** or MariaDB **10.6+**
* Apache 2.4 with `mod_rewrite` (or Nginx, or the built-in PHP server for dev)
* Optional: Composer (only needed for PHPMailer SMTP support)

## 1. Get the code

```bash
git clone <repo-url>
cd hr-dashboard/hrms
```

## 2. Create the database

```bash
mysql -uroot -p --default-character-set=utf8mb4 < database/schema.sql
mysql -uroot -p --default-character-set=utf8mb4 < database/seed.sql
```

> Always pass `--default-character-set=utf8mb4` so accented/di­acritic text loads
> correctly.

Create a dedicated DB account:

```sql
CREATE USER 'hrms'@'localhost' IDENTIFIED BY '<strong-password>';
GRANT SELECT, INSERT, UPDATE, DELETE ON pts_hrms.* TO 'hrms'@'localhost';
FLUSH PRIVILEGES;
```

## 3. Configure

All settings are environment variables read by `app/Config/config.php`:

| Variable | Default | Purpose |
|---|---|---|
| `DB_HOST` / `DB_PORT` | 127.0.0.1 / 3306 | Database server |
| `DB_NAME` / `DB_USER` / `DB_PASS` | pts_hrms / root / _empty_ | Credentials |
| `APP_URL` | http://localhost:8080 | Used in email links |
| `APP_DEBUG` | true | Set **false** in production |
| `APP_TIMEZONE` | Africa/Kigali | |
| `SESSION_TIMEOUT` | 30 | Idle minutes before re-login |
| `PASSWORD_EXPIRY_DAYS` | 90 | Password policy |
| `MAIL_ENABLED` | false | Turn on SMTP notifications |
| `MAIL_HOST/PORT/USERNAME/PASSWORD/ENCRYPTION` | — | SMTP (Outlook / Google Workspace) |
| `MAIL_FROM` / `MAIL_FROM_NAME` | hr@pts.rw | Sender identity |

## 4a. Development server

```bash
DB_USER=hrms DB_PASS=... php -S 0.0.0.0:8080 -t public public/router.php
```

## 4b. Apache (production)

```apache
<VirtualHost *:443>
    ServerName hrms.pts.rw
    DocumentRoot /var/www/hrms/public
    <Directory /var/www/hrms/public>
        AllowOverride All
        Require all granted
    </Directory>
    SetEnv DB_HOST 127.0.0.1
    SetEnv DB_NAME pts_hrms
    SetEnv DB_USER hrms
    SetEnv DB_PASS "********"
    SetEnv APP_DEBUG false
    SetEnv MAIL_ENABLED true
    # ... SSL directives
</VirtualHost>
```

* `public/` is the **only** directory that must be web-accessible; `app/`,
  `database/` and `storage/` stay outside the document root by design.
* Ensure the web user can write to `storage/uploads/`:
  `chown -R www-data:www-data storage && chmod -R 750 storage`.

## 5. Email (optional, recommended)

```bash
composer require phpmailer/phpmailer   # run inside hrms/
```

Set the `MAIL_*` variables. `App\Core\Mailer` auto-detects PHPMailer; without it,
PHP `mail()` is used.

## 6. First login & hardening checklist

1. Sign in as **admin / Admin@2026** → User Accounts → change the password.
2. Create personal accounts for HR staff with the right roles; disable sharing.
3. Enable **Two-Factor Authentication** on privileged accounts (users module).
4. Set `APP_DEBUG=false`.
5. Verify HTTPS and that `/storage/...` URLs require login (open one logged out).
6. Schedule backups (below).

## 7. Scheduled notifier (required for automatic alerts)

`bin/notify.php` sends the expiry/birthday/probation/training/RSSB alerts and
performs housekeeping (marks unexplained no-shows absent, expires overdue
contracts). Run it daily:

```cron
# /etc/cron.d/hrms-notify — every morning 06:00
0 6 * * * www-data DB_USER=hrms DB_PASS=******** php /var/www/hrms/bin/notify.php >> /var/log/hrms-notify.log 2>&1
```

Alerts are deduplicated (same alert repeats at most every 20 days). The warning
window is the `alert_days_before_expiry` setting (System Settings, default 30).

## 8. Backups

```cron
# /etc/cron.d/hrms-backup — daily 02:00
0 2 * * * root mysqldump --single-transaction pts_hrms | gzip > /backup/hrms-$(date +\%F).sql.gz
15 2 * * * root tar czf /backup/hrms-uploads-$(date +\%F).tgz -C /var/www/hrms storage/uploads
```

Keep at least 30 days, replicate off-site.

## 9. Upgrades

1. Back up (step 7).
2. `git pull`.
3. Apply any new SQL in `database/migrations/` (created per release).
4. Hard-refresh browsers (assets are versionless; consider cache-busting on release).

## Troubleshooting

| Symptom | Fix |
|---|---|
| 404 on every page (Apache) | Enable `mod_rewrite`, set `AllowOverride All` |
| "Access denied for user" | DB grants / `DB_USER`/`DB_PASS` env not visible to PHP (use `SetEnv`, not shell export) |
| Uploads fail | `storage/uploads` writable? file ≤ 5 MB and an allowed type? |
| Emails not sent | `MAIL_ENABLED=true`? PHPMailer installed? check `error_log` for "Mailer error" |
| Garbled accents from seed | Reload seed with `--default-character-set=utf8mb4` |
