# Budgeting Platform Scaffold

This repository provides a PHP 7.2 compatible scaffold for a budgeting and order management platform. The structure includes PSR-4 autoloading, Bootstrap-powered public assets, and a comprehensive MySQL schema migration covering clients, obras, architects, sellers, project designers, products, budgets, orders, and role-based permissions.

## Requirements

- PHP >= 7.2
- Composer
- MySQL 5.7+
- Web server (Apache/Nginx) configured to serve the `public/` directory

## Project Structure

```
public/           # Public web root and Bootstrap layout entry point
src/              # PHP source (controllers, core utilities, views)
config/           # Configuration files
routes/           # HTTP routes
storage/          # Runtime storage (logs, sessions)
database/migrations/  # SQL migrations
```

## Installation

1. Install dependencies:
   ```bash
   composer install
   ```
2. Copy the environment file and configure database credentials:
   ```bash
   cp .env.example .env
   ```
3. Import the base schema into your MySQL server:
   ```bash
   mysql -u root -p budgeting < database/migrations/2024_01_01_000000_create_schema.sql
   ```
4. Configure your web server to point to `public/index.php`.

## Default Roles

The schema includes `roles`, `permissions`, and `role_permission` tables. Suggested default roles:

- **admin** – full access to CRUD, budgeting, orders, and reports.
- **sales** – manage clients, budgets, and orders.
- **finance** – view budgets, orders, and financial reports.
- **viewer** – read-only access to budgets and reports.

These roles can be inserted via SQL after migration.

## Next Steps

This scaffold does not yet implement controllers, views, or authentication logic. It establishes the groundwork so those modules can be added incrementally following the provided structure.
