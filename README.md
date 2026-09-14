# Home Expense App

This project is a Laravel backend + React frontend app for managing home expenses.

## Project structure

- Backend: `~/Herd/Home-Expense`
- Frontend: `~/Herd/expense-web`
- Database: MySQL (`home_expense`)

## Requirements

- Herd
- DBngin
- MySQL
- Node.js and npm
- Composer

## Local setup

### 1. Create the database

Create a MySQL database named:

```sql
home_expense
```

Use:

- username: `root`
- password: empty

This is for local development only.

### 2. Configure backend environment

Open `~/Herd/Home-Expense/.env` and set:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=home_expense
DB_USERNAME=root
DB_PASSWORD=

APP_URL=http://<HOME_IP>:8000
SESSION_DRIVER=cookie
SESSION_LIFETIME=43200
SESSION_DOMAIN=null
SANCTUM_STATEFUL_DOMAINS=<HOME_IP>:5173
FRONTEND_URL=http://<HOME_IP>:5173
```

Find your local IP:

```bash
ipconfig getifaddr en0
```

### 3. Configure CORS

In `config/cors.php`:

```php
'allowed_origins' => ['http://<HOME_IP>:5173'],
'supports_credentials' => true,
```

### 4. Configure frontend environment

In `~/Herd/expense-web/.env`:

```env
VITE_API_URL=http://<HOME_IP>:8000
```

### 5. Run database migrations

From the backend project:

```bash
cd ~/Herd/Home-Expense
php artisan migrate
```

### 6. Start the app

Backend:

```bash
cd ~/Herd/Home-Expense
php artisan serve --host=0.0.0.0 --port=8000
```

Frontend:

```bash
cd ~/Herd/expense-web
npm run dev
```

Open the app in the browser:

```text
http://<HOME_IP>:5173
```

---

## Email setup (optional but recommended)

If you want signup verification and password reset emails, configure Gmail SMTP in the backend `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=<your real Gmail address>
MAIL_PASSWORD=<your 16-character Gmail app password>
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=<your real Gmail address>
MAIL_FROM_NAME="Home Expense"
```

Then clear config:

```bash
php artisan config:clear
```

---

## Useful commands

### Clear Laravel config

```bash
cd ~/Herd/Home-Expense
php artisan config:clear
```

### Run migrations

```bash
cd ~/Herd/Home-Expense
php artisan migrate
```

### Seed roles/permissions

```bash
cd ~/Herd/Home-Expense
php artisan db:seed --class=RolePermissionSeeder
```

### Build frontend for production

```bash
cd ~/Herd/expense-web
npm run build
npm run preview -- --host 0.0.0.0
```

---

## Notes

- Use this setup for local development on the same home network.
- Do not use this exact local database setup in a public production environment.
- For the app to work properly, the backend and frontend must use the same local IP address.

## Notes

This project is configured for local development on a home network and intentionally uses simple local credentials for MySQL and local IP-based frontend/backend communication. Do not use this pattern in a public-facing production environment.
