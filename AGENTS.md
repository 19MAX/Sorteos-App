# AGENTS.md

## Project Overview
CodeIgniter 4 raffle/sorteos system. PHP 8.2+, MySQL, Tailwind CSS for landing pages.

## Key Commands

```bash
php spark migrate --all       # Run all migrations
php spark serve              # Dev server (localhost:8080)
composer test                # Run PHPUnit tests
```

## Architecture

- **Public entry**: `public/index.php`
- **Controllers**: `app/Controllers/` (Admin, Api, Auth, Home subdirectories)
- **Models**: `app/Models/` (TicketModel, TransactionModel, ParticipantModel, etc.)
- **Migrations**: `app/Database/Migrations/`
- **Landing pages**: `app/Views/home/` (Quickluck system)
- **Admin views**: `app/Views/admin/`
- **Layouts**: `app/Views/layout/main.php`

## Skills (Auto-loaded)

- `ci4-admin-ui-designer` → Admin views using existing template (`app/Views/layout/main.php`)
- `compra-boletos` → Ticket purchase/reservation logic (concurrency, rate limiting, Payphone)
- `quickluck-landing` → Landing page system (`app/Views/home/`)

Load with: `skill(name: "skill-name")`

## Admin Routes

All `/admin/*` routes use `adminauth` filter. Key routes:
- `GET /admin` → Dashboard
- `GET /admin/tickets/data` → DataTables endpoint
- `POST /admin/tickets/generate-process` → Generate tickets
- `POST /admin/transactions/mark-as-paid` → Approve transfer payments

## API Routes

- `POST /api/cedula` → Validate cedula
- `POST /api/orden/crear` → Create order
- `GET /api/orden/verificar` → Check order status
- `GET /api/tickets/disponibles` → Available tickets

## Ticket States

`disponible` → `reservado` → `procesando` → `vendido`/`pagado`/`asignado` | `expirado`

## Database Conventions

- Migrations use timestamp prefix (`2026-04-08-224xxx`)
- Tables: `participants`, `tickets`, `transactions`, `bancos`, `payphone_transactions`, `admins`, `settings`, `system_logs`
- Transaction `metodo_pago`: `fisico`, `transferencia`, `tarjeta`

## Queue System

Uses CodeIgniter Queue (`codeigniter4/queue`):
```bash
php spark queue:work --nombre cola
```

## Testing

- PHPUnit config: `phpunit.xml.dist` (bootstrap uses `vendor/codeigniter4/framework/system/Test/bootstrap.php`)
- Database tests require a running MySQL instance; configure in `.env` or `app/Config/Database.php`
- Run: `composer test` or `./phpunit`

## CI/CD

No GitHub Actions workflows present. Check `.github/workflows/` if added later.
