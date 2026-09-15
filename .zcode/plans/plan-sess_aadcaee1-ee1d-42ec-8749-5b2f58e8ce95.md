### Refactoring & Enhancement Plan: System Administration & Import Service

#### 1. Extract `ImportService` (`app/Services/ImportService.php`)
- Move CSV parsing, delimiter detection, preview mapping, and model generation (`importAssets`, `importUsers`, `importConsumables`, `importComponents`, `importLicenses`, `importMasterData`) into `App\Services\ImportService`.
- Refactor `ImportController` to delegate preview and execution tasks to `ImportService`.

#### 2. Audit Trail & Activity Logging Verification
- Ensure `LogsActivity` trait is consistently attached to core models (`Asset`, `Component`, `Consumable`, `License`, `Ticket`, `Project`).

#### 3. Verification & Build
- Run `vendor/bin/pint` to format code according to project standards.
- Run `php artisan test` to verify no regressions.
- Run `npm run build`.

#### Files to Create / Modify
- `app/Services/ImportService.php` (New)
- `app/Http/Controllers/ImportController.php` (Refactored)