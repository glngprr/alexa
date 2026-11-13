# Alexa — Premium Insulated Bottle E-Commerce

Alexa is a full-featured e-commerce web application built with Laravel for selling premium stainless-steel insulated bottles and tumblers. The application covers the full shopping journey — from product browsing to payment — and includes a dedicated admin dashboard for order and shipment management.

---

## Features

- **Product Catalog** — 3 collections, 6 products, 18 variants (sizes and colors)
- **Shopping Cart** — session-persisted cart with quantity management
- **User Authentication** — registration, login, email verification (Laravel Breeze / Fortify)
- **Checkout & Orders** — address management, order placement, order history
- **Payments** — Midtrans Sandbox integration (credit card, bank transfer, e-wallet)
- **Shipment Management** — admin-managed manual dispatch; waybill and courier tracking fields
- **Admin Dashboard** — product, collection, order, and shipment management
- **Image Assets** — product images served from `storage/app/public`

---

## Technology Stack

| Layer | Technology |
|---|---|
| Language | PHP 8.3 |
| Framework | Laravel 13.32 |
| Database | MySQL 8.4 |
| Dependency Manager | Composer 2 |
| Frontend Runtime | Node.js ≥ 18 / npm 11 |
| Build Tool | Vite 8 |
| CSS Framework | Tailwind CSS 4 |
| Templating | Blade |

---

## Requirements

- **PHP** ≥ 8.3 with extensions: `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `json`, `mbstring`, `openssl`, `pdo`, `pdo_mysql`, `tokenizer`, `xml`
- **MySQL** ≥ 8.0
- **Composer** ≥ 2
- **Node.js** ≥ 18 and **npm** ≥ 9

---

## Installation & Setup

### 1. Clone the repository

```bash
git clone https://github.com/glngprr/alexa.git
cd alexa
```

### 2. Install PHP dependencies

```bash
composer install
```

### 3. Copy and configure the environment file

```bash
cp .env.example .env
```

Open `.env` and set your local database credentials and application key (see Step 4).

### 4. Generate the application key

```bash
php artisan key:generate
```

### 5. Configure the database

Edit `.env` and set:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=alexa
DB_USERNAME=root
DB_PASSWORD=
```

Create the database in MySQL before running migrations:

```sql
CREATE DATABASE alexa CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 6. Run database migrations

```bash
php artisan migrate
```

### 7. Seed the database

```bash
php artisan db:seed
```

This runs `CatalogSeeder` (3 collections, 6 products, 18 variants) and `UserSeeder` (dev test accounts — local/testing environments only).

### 8. Install frontend dependencies

```bash
npm ci
```

### 9. Build frontend assets

```bash
npm run build
```

### 10. Create the storage symlink

```bash
php artisan storage:link
```

Then start the development server:

```bash
php artisan serve
```

The application will be available at [http://localhost:8000](http://localhost:8000).

---

## Admin Access & Development Test Accounts

> [!IMPORTANT]
> These accounts are created by `UserSeeder` **only when `APP_ENV` is `local` or `testing`**. They are never created in production.

| Role | Email | Password | Notes |
|---|---|---|---|
| Admin | `admin@alexa.test` | `password` | Full access to `/admin` dashboard |
| Customer | `customer@alexa.test` | `password` | Standard shopping account |

Admin panel is available at: **`/admin`**

---

## Midtrans Sandbox Configuration

Alexa uses [Midtrans](https://midtrans.com) for payment processing. The integration is configured for the **Sandbox** environment.

### Required environment variables

```dotenv
MIDTRANS_SERVER_KEY=SB-Mid-server-xxxxxxxxxxxxxxxx
MIDTRANS_CLIENT_KEY=SB-Mid-client-xxxxxxxxxxxxxxxx
MIDTRANS_IS_PRODUCTION=false
```

Obtain sandbox credentials from the [Midtrans Dashboard](https://dashboard.sandbox.midtrans.com) under **Settings → Access Keys**.

### Webhook (Payment Notification)

Midtrans sends payment status updates to:

```
POST /payments/midtrans/notification
```

This endpoint is CSRF-exempt and handles all Midtrans server-to-client notifications.

For local webhook testing, expose your local server using [ngrok](https://ngrok.com):

```bash
ngrok http 8000
```

Then set the notification URL in the Midtrans Dashboard to:

```
https://<your-ngrok-subdomain>.ngrok-free.app/payments/midtrans/notification
```

---

## Shipping Status

> [!NOTE]
> Biteship integration has been **removed**. Shipments are currently managed manually by administrators through the admin dashboard. The shipping provider fields (`courier_code`, `courier_service`, `waybill_id`) are stored in the `shipments` table for use with a future shipping API integration.

**RajaOngkir** has **not** been implemented yet. Integration points are available in `ShipmentController` and the `shipments` table schema.

---

## Database Structure

| Table | Description |
|---|---|
| `users` | Customer and admin accounts |
| `collections` | Product collections / categories |
| `products` | Products belonging to a collection |
| `product_variants` | SKU-level variants (size, color, price, stock) |
| `carts` | One cart per authenticated user |
| `cart_items` | Line items within a cart |
| `addresses` | Saved shipping addresses per user |
| `orders` | Placed orders |
| `order_items` | Line items within an order |
| `payments` | Midtrans payment records per order |
| `shipments` | Shipment dispatch records per order |

---

## Running Tests

```bash
php artisan test
```

Expected output: **88 tests, 519 assertions, 0 failures**.

The test suite covers authentication, admin authorization, product catalog, cart, checkout, payment webhook handling, shipment management, and end-to-end integration.

---

## Git Collaboration Workflow

- **`main`** — stable branch; direct pushes are discouraged
- Create a feature branch for each piece of work:
  ```bash
  git checkout -b feature/your-feature-name
  ```
- Open a pull request against `main` for review before merging
- **Never commit** `.env`, API credentials, private keys, or any secrets
- `.env` is listed in `.gitignore` and must never be tracked

---

## Troubleshooting

### MySQL connection refused

Ensure MySQL is running (`mysqld` or Laragon) and that `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` in `.env` are correct.

### Missing `APP_KEY`

Run `php artisan key:generate`. Without a key, all sessions and encrypted data will fail.

### Composer errors

```bash
composer install --no-scripts
```

If that fails, check your PHP version (`php -v`) meets the ≥ 8.3 requirement.

### npm / Node.js errors

```bash
node -v   # must be ≥ 18
npm -v
npm ci    # clean install from package-lock.json
```

### Missing build assets (blank page or missing styles)

```bash
npm run build
php artisan storage:link
```

Then hard-refresh your browser.

### Midtrans webhook not received locally

Use `ngrok http 8000` and update the notification URL in the Midtrans Sandbox Dashboard. Ensure `MIDTRANS_IS_PRODUCTION=false` is set.

### Storage images not loading

```bash
php artisan storage:link
```

Confirm that `storage/app/public/products/` contains the product image files.
