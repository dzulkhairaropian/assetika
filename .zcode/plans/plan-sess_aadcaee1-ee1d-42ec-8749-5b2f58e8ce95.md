### Master Data UI Harmonization Plan: Convert Raw `<select>` to `<x-form.searchable-select>`

#### 1. Controller Option Preparation
- Update Master Controllers (`ProductController`, `DepartmentController`, `LocationController`, `OperatingSystemController`, `CompanyController`):
  - Prepare pre-formatted array options (`$brandOpts`, `$categoryOpts`, `$companyOpts`, `$groupOpts`) in `index`, `create`, and `edit` methods.

#### 2. View Upgrades to `<x-form.searchable-select>`
- **`products/create.blade.php` & `edit.blade.php`**: Upgrade Brand and Category dropdowns.
- **`departments/create.blade.php` & `edit.blade.php`**: Upgrade Company dropdown.
- **`locations/create.blade.php` & `edit.blade.php`**: Upgrade Company dropdown.
- **`operating-systems/create.blade.php` & `edit.blade.php`**: Upgrade Brand dropdown.
- **`companies/create.blade.php` & `edit.blade.php`**: Upgrade Group dropdown.
- **`index.blade.php` filters** across Master views: Convert raw filter select elements to `<x-form.searchable-select>`.

#### 3. Layout Harmonization
- Apply TailAdmin 2-column card layout + Actions Card across all Master Data create & edit pages.

#### 4. Verification & Build
- Run `vendor/bin/pint`, `php artisan test`, and `npm run build`.

#### Files to Modify
- `app/Http/Controllers/Master/ProductController.php`
- `app/Http/Controllers/Master/DepartmentController.php`
- `app/Http/Controllers/Master/LocationController.php`
- `app/Http/Controllers/Master/OperatingSystemController.php`
- `app/Http/Controllers/Master/CompanyController.php`
- Master Data views in `resources/views/pages/master/`