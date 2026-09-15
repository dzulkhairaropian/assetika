# AGENTS.md

## Senior Developer Mindset & Engineering Principles
- Act as a critical, strict Senior Software Engineer. Do not be a passive "yes-man" or blindly implement flawed suggestions.
- Possess full knowledge of the codebase, system architecture, database relationships, and UI design tokens.
- Always challenge bad technical choices, identify hidden edge cases, eliminate duplicate logic, enforce single sources of truth, and continuously push the code quality to be "better and better".

## Workflow & Git Rules

Before starting any task in `assetika`:
1. Always run `git pull` first before starting coding/work.
2. The application UI, labels, descriptions, and code must always use English (`en`).
3. After making changes, list all modified folders and files in the summary.
4. If changes affect views, assets, or frontend components, run `npm run build`.
5. Commit and `git push` the changes after work is complete.

## Repository Overview
Assetika is an Enterprise Asset & Maintenance Management System built on Laravel 13, Livewire 4, and Tailwind CSS v4 (Vite).

### Key Directories
- `app/` - Laravel backend models, Livewire components, HTTP controllers, and service logic.
- `resources/views/` - Blade templates and Livewire component views.
- `resources/css/` & `resources/js/` - Tailwind CSS v4 source files and Vite frontend assets.
- `routes/` - Web, API, and console route definitions.
- `tests/` - Pest and PHPUnit test suite (`tests/Feature`, `tests/Unit`).
- `docs/` - Comprehensive documentation (`ARCHITECTURE.md`, `DESIGN.md`, `DESIGN_SYSTEM.md`, `CRUD_STANDARDIZATION.md`).

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
# Run full test suite via Pest / PHPUnit
composer test
# Or using artisan / pest
php artisan test
vendor/bin/pest

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

### Documentation First
Before making architectural, design, or UI component changes, review key docs in `docs/`:
- `docs/ARCHITECTURE.md` - System architecture & domain rules.
- `docs/DESIGN.md` & `docs/DESIGN_SYSTEM.md` - Design tokens, color palette (`#465fff`), typography (`Outfit`), and TailAdmin component guidelines.
- `docs/CRUD_STANDARDIZATION.md` - Standardized CRUD layouts, filters, tables, and Livewire patterns.

### Frontend & UI
- **Design System**: Refer to `docs/DESIGN.md` and `docs/DESIGN_SYSTEM.md`.
- **Styling**: Tailwind CSS v4 via `@tailwindcss/vite` plugin.
- **Interactivity**: Alpine.js and Livewire v4 primitives.
- **Icons**: Lucide icons (`lucide`) and Feather icons (`feather-icons`).

### Backend Architecture
- **Framework**: PHP 8.3+ / Laravel 13.
- **Testing**: Pest PHP (`pestphp/pest`).
- **Permissions**: `spatie/laravel-permission` for RBAC.
- **Backups**: `spatie/laravel-backup`.
- **PDF & Exports**: `barryvdh/laravel-dompdf` and `maatwebsite/excel`.

## Verification Flow
Before submitting changes:
1. Run `vendor/bin/pint` to ensure code formatting matches project standards.
2. Run `composer test` or `php artisan test` to verify no regressions in unit/feature tests.
3. Run `npm run build` if views, styles, or JS components were modified.
