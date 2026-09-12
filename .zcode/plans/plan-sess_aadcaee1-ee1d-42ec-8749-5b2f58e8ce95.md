### Visual & UX Harmonization: Align Component Forms & Detail Page with Asset Specs

#### 1. Form Layout (`create.blade.php` & `edit.blade.php`)
- Match the exact card layout of `resources/views/pages/assets/create.blade.php`:
  - `rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6 space-y-8`.
  - Section headers: `<h3 class="text-base font-medium text-gray-800 dark:text-white/90 mb-5 flex items-center gap-2 border-b border-gray-100 pb-3 dark:border-gray-800">`.
  - Structured sections: **General Information** & **Purchase & Warranty Information**.
  - Right sidebar Actions Card matching Asset style.

#### 2. Detail Page Layout (`show.blade.php`)
- Match `resources/views/pages/assets/show.blade.php`:
  - **Header Hero Card**: Big `component_tag` title, status badge (`Ready`, `Installed`, `Maintenance`, `Retired`), model/brand subtitle, action bar top-right (Edit, Back, Install / Detach).
  - **Tab Navigation Bar**:
    - **Overview / Details Tab**: Grid definition list of Product, Category, Brand, Serial, Vendor, Location, Purchase Cost, Purchase Date, Release Date, Warranty.
    - **Installed Asset Tab**: Display active asset attachment details (if installed) or attachment form (if ready).
    - **Maintenance & Tickets Log Tab**: Display history of automatically generated maintenance tickets for this component.

#### 3. Verification & Build
- Run `vendor/bin/pint`, `php artisan test`, and `npm run build`.

#### Files to Modify
- `resources/views/pages/components/create.blade.php`
- `resources/views/pages/components/edit.blade.php`
- `resources/views/pages/components/show.blade.php`