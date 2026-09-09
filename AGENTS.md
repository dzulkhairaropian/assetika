# AGENTS.md

## Workflow & Git Rules

Before starting any task in `assetika`:
1. Always run `git pull` first before starting work.
2. After making changes, list all modified folders and files.
3. If changes affect views, assets, or frontend components, run `npm run build`.
4. Commit and `git push` the changes.

## Repository Overview
Assetika is an Enterprise Asset & Maintenance Management System built on Laravel 13, Livewire 4, and Tailwind CSS v4 (Vite).

## Key Commands

### Development Server
```bash
# Start all dev services (Laravel serve, queue listener, logs, Vite) concurrently
composer dev

# Run Vite dev server only
npm run dev
```

### Testing
```bash
# Run test suite
composer test
# Or using artisan
php artisan test

# Run a single test file
php artisan test tests/Feature/ExampleTest.php

# Run a specific test method
php artisan test --filter=test_example
```

### Code Style & Setup
```bash
# Run Laravel Pint code formatter
vendor/bin/pint

# Setup new environment
composer run setup
```

## Architecture & Conventions

### Frontend & UI
- **Design System**: Refer to `docs/DESIGN.md` for design tokens, TailAdmin patterns, color rules (`#465fff`), and typography (`Outfit`).
- **Styling**: Tailwind CSS v4 via `@tailwindcss/vite` plugin.
- **Interactivity**: Alpine.js and Livewire v4 primitives.
- **Icons**: Lucide icons (`lucide`) and Feather icons (`feather-icons`).

### Backend Architecture
- **Framework**: PHP 8.3+ / Laravel 13.
- **Permissions**: `spatie/laravel-permission` for RBAC.
- **Backups**: `spatie/laravel-backup`.
- **PDF & Exports**: `barryvdh/laravel-dompdf` and `maatwebsite/excel`.

## Verification Flow
Before submitting changes:
1. Run `vendor/bin/pint` to ensure code formatting matches project standards.
2. Run `composer test` or `php artisan test` to verify no regressions in unit/feature tests.
