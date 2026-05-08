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

## Skills

Load with `skill(name: "skill-name")`:
- `compra-boletos` → Ticket purchase/reservation logic (concurrency, rate limiting, Payphone)
- `quickluck-landing` → Landing page system (`app/Views/home/`) — **critical rules**: no dates, no ticket counts, only `$soldPercent` (0–100) is public
- `ci4-admin-ui-designer` → Admin views using `app/Views/layout/main.php`
- `queue-code` → Async job queues

## Ticket Concurrency (Critical)

**Never** do `SELECT` then `UPDATE` in PHP. Use atomic SQL:
```php
UPDATE tickets SET status = 'procesando' WHERE id IN (...) AND status = 'disponible';
// Must verify $this->db->affectedRows() === count(requested tickets)
```
If affected rows < requested, someone else won the race — rollback and retry.

## Admin Panel

All `/admin/*` routes use `adminauth` filter. Default admin: `admin@admin.com` / `password` (from `.env` seeder vars).

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

No GitHub Actions workflows present.
