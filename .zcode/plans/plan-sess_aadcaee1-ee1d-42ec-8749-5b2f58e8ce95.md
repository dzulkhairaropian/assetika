### Emergency Fix & Alignment Plan for Component CRUD

#### 1. Immediate Bug Fix (Fix 500 Error on `components/{id}`)
- Fix `ComponentController@show`:
  - Replace `Asset::with(...)->orderBy('name')->get()` with `Asset::with(...)->orderBy('asset_tag')->get()`.
  - Fix any instance where `name` was called directly on `Asset` model without `device_name` or `product->name` fallback.

#### 2. Component System Alignment with Asset
- Ensure Component views (`index`, `show`, `create`, `edit`) display component records cleanly matching the Asset module standard:
  - Display `component_tag`, `product.name`, `serial`, `location`, `status`, and `assignedToAsset`.
  - Ensure all select dropdowns use `x-form.searchable-select`.

#### 3. Verification & Build
- Run `vendor/bin/pint` and `php artisan test`.
- Run `npm run build`.

#### Files to Modify
- `app/Http/Controllers/ComponentController.php`
- `resources/views/pages/components/show.blade.php`