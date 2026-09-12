### Fix: Blade Tag Compiler Error on `edit.blade.php` & `create.blade.php`

#### Root Cause
Multi-line formatted Blade component tags `<x-form.searchable-select ... />` with inline string function arguments like `old('product_id', ...)` cause the Laravel 13 Blade compiler to misparse internal quotes as prop boundaries (`$product_id`).

#### Solution Plan
1. Convert `<x-form.searchable-select>` component invocations in `create.blade.php`, `edit.blade.php`, and `show.blade.php` to clean single-line format (matching `assets/edit.blade.php` syntax).
2. Test GET `/components/2/edit` and `/components/create`.
3. Re-run `vendor/bin/pint`, `php artisan test`, and `npm run build`.

#### Files to Modify
- `resources/views/pages/components/create.blade.php`
- `resources/views/pages/components/edit.blade.php`
- `resources/views/pages/components/show.blade.php`