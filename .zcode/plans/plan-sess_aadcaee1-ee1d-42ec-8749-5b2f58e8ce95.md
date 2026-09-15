### Polish Plan: Software Licenses & Asset Kits Forms

#### 1. Controller Option Preparation
- In `LicenseController` (`create`, `edit`, `show`), prepare `$brandOpts`, `$assetOpts`, and `$userOpts` as clean array format `[['id' => ..., 'text' => ...]]`.
- In `KitController` (`create`, `edit`), prepare `$assetOpts`, `$licenseOpts`, `$componentOpts`, and `$consumableOpts`.

#### 2. View Modernization
- **`licenses/create.blade.php` & `edit.blade.php`**:
  - Replace `<select>` with `<x-form.searchable-select>` for Brand and Asset assignment.
- **`licenses/show.blade.php`**:
  - Replace `<select>` with `<x-form.searchable-select>` for Seat checkout assignment.
- **`kits/create.blade.php` & `edit.blade.php`**:
  - Replace `<select>` with `<x-form.searchable-select>` for Kit item selections.

#### 3. Verification & Build
- Run `vendor/bin/pint`, `php artisan test`, and `npm run build`.

#### Files to Modify
- `app/Http/Controllers/LicenseController.php`
- `app/Http/Controllers/KitController.php`
- `resources/views/pages/licenses/create.blade.php`
- `resources/views/pages/licenses/edit.blade.php`
- `resources/views/pages/licenses/show.blade.php`
- `resources/views/pages/kits/create.blade.php`
- `resources/views/pages/kits/edit.blade.php`