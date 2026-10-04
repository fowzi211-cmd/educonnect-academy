# Deployment Checklist

This is the punch list for taking Dr. Nada Center from local development to
a real production deployment. Items are grouped by who actually does them.

## Already done (code side)

- [x] Production `.env` template ready — see `.env.production.example`.
- [x] Test-gateway checkout path disabled outside local/testing (no free-course bug).
- [x] Login rate limiting (5 attempts, then a timed lockout).
- [x] `php artisan app:test-email {address}` — verifies whatever mail driver is configured.
- [x] `php artisan app:backup` — archives the database + all uploaded files into `storage/backups/*.zip`.
- [x] `php artisan app:cleanup-demo-data` — suspends the seeded demo accounts (see below; not run automatically).

## Host setup (you / whoever provisions the server)

1. **Choose a host.** Anything that runs PHP 8.4 + SQLite (or MySQL/Postgres
   if you'd rather switch — see the DB section of `.env.production.example`).
   A small VPS (DigitalOcean, Hetzner, Laravel Forge + any provider) is
   enough for a platform this size.
2. **Point a domain at it** and get an SSL certificate (Let's Encrypt via
   your host's tooling, or Cloudflare in front of it). The app must be
   served over HTTPS — `SESSION_SECURE_COOKIE=true` in the production env
   template assumes this.
3. **Deploy the code** (git pull / CI deploy / whatever your host uses), then:
   ```bash
   composer install --optimize-autoloader --no-dev
   cp .env.production.example .env   # then fill in every blank value
   php artisan key:generate
   php artisan migrate --force
   php artisan db:seed --class=RoleSeeder
   php artisan db:seed --class=SettingSeeder
   php artisan db:seed --class=StaticContentSeeder
   npm install && npm run build
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```
   Do **not** run the demo seeders (`DemoUserSeeder`, `CourseDemoSeeder`,
   etc.) in production — they're already gated to only run when
   `APP_ENV` is `local` or `testing`, so this happens automatically as long
   as `.env` really says `APP_ENV=production`.
4. **Cron**, so subscriptions actually expire on schedule — add this to the
   server's crontab (as the deploy user, from the project directory):
   ```
   * * * * * cd /path/to/educonnect-academy && php artisan schedule:run >> /dev/null 2>&1
   ```
   This drives the daily `expireLapsedSubscriptions()` /
   `expireLapsedGracePeriods()` job registered in `routes/console.php`.
5. **Backups** — wire the backup command into the same crontab, e.g. nightly:
   ```
   0 3 * * * cd /path/to/educonnect-academy && php artisan app:backup >> storage/logs/backup.log 2>&1
   ```
   This writes to `storage/backups/` on the server itself — copy those
   zips off the server regularly (host snapshot, S3, rsync to somewhere
   else). A backup that only lives on the same disk as the database it's
   backing up doesn't protect against losing that disk.
6. **Queue worker** — not required. No notification currently implements
   `ShouldQueue`, so everything (mail, in-app notifications) sends inline
   during the request. Fine at this scale; revisit only if SMTP latency
   starts making admin actions feel slow.
7. **Automatic deploys (optional)** — the server can poll GitHub every 2
   minutes and deploy new commits on `master` by itself:
   ```bash
   cd /var/www/nadacenter && git pull && bash deploy/install-autodeploy.sh
   ```
   Each deploy runs `deploy/update.sh` and then checks `/up`. If the update
   fails or the site is unhealthy afterwards, the server rolls back to the
   previous commit (database migrations are *not* reverted) and will not retry
   that commit until a newer one arrives. A deploy is skipped, and logged, if
   the server's checkout has local edits. Log: `/var/log/nadacenter-deploy.log`.
   Pause with `rm /etc/cron.d/nadacenter-autodeploy`; re-run the installer to
   resume. Anything pushed to `master` goes live, so only push tested code and
   keep the GitHub account protected (2FA).

## Still needs a human decision (not something to automate)

- [ ] **SMTP credentials** — pick a provider (Gmail SMTP, Mailtrap, SendGrid,
  Postmark...), put them in `.env`, then confirm with
  `php artisan app:test-email you@example.com`.
- [ ] **Legal content review** — Terms, Privacy, Refund, and Cookie policy
  pages exist and are wired up; the actual legal text should be reviewed
  (ideally by a lawyer, given real payments are involved) before relying on
  it. See the content audit notes below.
- [ ] **Arabic translation sign-off** — ~1,000 strings have been translated;
  a native-speaker pass is worth doing before a large Arabic-speaking
  audience sees them, particularly the payment/receipt-facing text.
- [ ] **Demo account cleanup timing** — `php artisan app:cleanup-demo-data`
  is ready but not run automatically, since the seeded accounts may still be
  useful for your own testing. Run it when you're done testing and about to
  go live for real. It suspends (doesn't delete) — reversible from
  Admin → Users if needed.
- [ ] **Real bank account(s)** — already added (Bank of Khartoum). Add any
  additional accounts the same way, from Admin → Finance → Bank Accounts.
