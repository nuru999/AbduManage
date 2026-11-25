# AbduManage

A school management system built with **Laravel 13**, **Tailwind CSS 4** and **M-Pesa (Daraja) fee collection**.

## Features

- **Role-based access** – admins manage everything, teachers see their classes and take attendance
- **Students** – enroll, search, filter by class/status, full profile page
- **Teachers** – create staff with their own login
- **Classes** – assign a class teacher, see the class list
- **Attendance** – one-screen daily register (present / late / absent / excused) with remarks
- **Fees & M-Pesa** – bill fees per student, send an **STK Push** to the parent's phone, live status page, automatic receipt capture, balance tracking
- **Dashboard** – headcounts, 7-day attendance chart, fees collected / outstanding, recent payments

## Quick start (local)

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
npm install
npm run dev        # in one terminal
php artisan serve  # in another
```

Sign in at <http://localhost:8000> with:

| Role    | Email                  | Password   |
|---------|------------------------|------------|
| Admin   | `admin@abdumanage.test` | `password` |
| Teacher | `grace@abdumanage.test` | `password` |

(Set `ADMIN_EMAIL` / `ADMIN_PASSWORD` in `.env` before seeding to choose your own admin login.)

Run the tests with `php artisan test`.

## M-Pesa setup

1. **Demo mode (default)** – `MPESA_SIMULATE=true`. No Safaricom account is needed; a payment auto-completes ~5 seconds after you send it. Perfect for presenting the project.
2. **Sandbox** – create an app at <https://developer.safaricom.co.ke>, then set in `.env`:
   ```
   MPESA_SIMULATE=false
   MPESA_ENV=sandbox
   MPESA_CONSUMER_KEY=...
   MPESA_CONSUMER_SECRET=...
   MPESA_SHORTCODE=174379
   MPESA_PASSKEY=...            # from the Daraja "Lipa na M-Pesa" test credentials
   MPESA_CALLBACK_TOKEN=some-long-random-string
   MPESA_CALLBACK_BASE_URL=https://your-public-url   # ngrok locally, your domain when deployed
   ```
3. **Production** – set `MPESA_ENV=production` and use your own Paybill/Till, passkey and credentials from Safaricom's Go-Live process.

Safaricom posts payment results to `POST /mpesa/callback/{MPESA_CALLBACK_TOKEN}`. The URL must be public **https**; the secret token in the path stops anyone else from faking a payment.

## Deploying

- Set `APP_ENV=production`, `APP_DEBUG=false`, a real `APP_URL`, and run `php artisan key:generate --show` to get an `APP_KEY`.
- Run `php artisan migrate --force` (and `php artisan db:seed --force` once to create the admin).
- Build assets with `npm ci && npm run build`.
- Use a database that persists (MySQL/Postgres, or a mounted volume for SQLite).
- Change the admin password and set `SEED_DEMO=false` for real data.
- Cache config for speed: `php artisan config:cache route:cache view:cache`.
