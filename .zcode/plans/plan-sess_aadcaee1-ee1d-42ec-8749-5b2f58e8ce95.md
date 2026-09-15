### Comprehensive Modernization & Refactoring Plan: Service Desk / Ticketing System

#### 1. Auto-Category Mapping based on Selected Asset
- **Logic**: In `TicketController@store` and `TicketController@update`, if `asset_id` is provided but `ticket_category_id` is empty:
  - Auto-map `ticket_category_id` and `ticket_subcategory_id` using `AssetTicketService::resolveTicketCategoryForAsset($asset)`.

#### 2. Controller Option Preparation (`TicketController.php`)
- In `create` and `edit` methods, prepare `$userOpts`, `$staffOpts`, `$typeOpts`, `$categoryOpts`, `$priorityOpts`, `$assetOpts`, `$statusOpts`, `$labelOpts`, `$sourceOpts` as pre-formatted arrays `[['id' => ..., 'text' => ...]]`.
- Pass clean pre-formatted option arrays to `_form.blade.php`.

#### 3. View Modernization & Blade Parser Protection (`pages/tickets/`)
- **`tickets/_form.blade.php`**:
  - Isolate all `:selected` option expressions into top `@php` block.
  - Apply clean 2-column card layout matching Asset & Component specs.
- **`tickets/index.blade.php`**:
  - Ensure KPI Metric Cards and Quick Filter Tabs are consistent with TailAdmin theme tokens.
  - Standardize action dropdowns to fixed viewport positioning.

#### 4. Verification & Build
- Run `vendor/bin/pint`, `php artisan test`, and `npm run build`.

#### Files to Modify
- `app/Http/Controllers/TicketController.php`
- `resources/views/pages/tickets/_form.blade.php`
- `resources/views/pages/tickets/create.blade.php`
- `resources/views/pages/tickets/edit.blade.php`
- `resources/views/pages/tickets/index.blade.php`