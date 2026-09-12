### Fix: Undefined Property Error on `edit.blade.php`

#### Root Cause
Passing inline closure mappings (`$products->map(...)`) inside Blade component attribute tags (`:options="..."`) causes Blade tag-compiler ambiguity in Laravel 13 when parsing component attributes.

#### Solution Plan
1. **Prepare Options in `ComponentController.php`**:
   - In `create` and `edit` methods, prepare `$productOpts`, `$vendorOpts`, `$locationOpts` as clean array format `[['id' => ..., 'text' => ...]]`.
   - Pass pre-formatted option arrays to `create.blade.php`, `edit.blade.php`, and `show.blade.php`.
2. **Clean Component Calls**:
   - Replace inline closure mappings in `create.blade.php`, `edit.blade.php`, and `show.blade.php` with `$productOpts`, `$vendorOpts`, `$locationOpts`.
   - Use `:required="true"` or `required` properly.
3. **Verification**:
   - Run `vendor/bin/pint`, `php artisan test`, and `npm run build`.

#### Files to Modify
- `app/Http/Controllers/ComponentController.php`
- `resources/views/pages/components/create.blade.php`
- `resources/views/pages/components/edit.blade.php`
- `resources/views/pages/components/show.blade.php`