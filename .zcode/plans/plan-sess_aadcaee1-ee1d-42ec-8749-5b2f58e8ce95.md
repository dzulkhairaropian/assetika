### Comprehensive Refinement Plan: Consumables Inventory Module

#### 1. Controller & Form Data Preparation (`ConsumableController.php`)
- Prepare `$productOpts`, `$userOpts`, `$deptOpts`, `$locationOpts` as clean array format `[['id' => ..., 'text' => ...]]` in `create`, `edit`, `checkout`, `bulkCheckoutForm`, and `restock` methods.

#### 2. View Modernization & Blade Parser Fixes
- **`create.blade.php` & `edit.blade.php`**:
  - Replace `<select>` with `<x-form.searchable-select>` for Product selection.
  - Apply clean 2-column card layout matching Asset/Component standards.
- **`checkout.blade.php` & `bulk-checkout.blade.php`**:
  - Replace `<select>` with `<x-form.searchable-select>` for User, Department, and Location selection.
- **`index.blade.php`**:
  - Ensure KPI Metric Cards (Total, In Stock, Low Stock, Out of Stock) and Filter Tabs match TailAdmin specs.
  - Standardize actions dropdown to fixed viewport positioning.

#### 3. Verification & Build
- Run `vendor/bin/pint`, `php artisan test`, and `npm run build`.

#### Files to Modify
- `app/Http/Controllers/ConsumableController.php`
- `resources/views/pages/consumables/index.blade.php`
- `resources/views/pages/consumables/create.blade.php`
- `resources/views/pages/consumables/edit.blade.php`
- `resources/views/pages/consumables/checkout.blade.php`
- `resources/views/pages/consumables/bulk-checkout.blade.php`