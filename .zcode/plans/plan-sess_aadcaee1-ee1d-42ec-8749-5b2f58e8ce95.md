### Fix: Undefined Variable `$available` in `index.blade.php`

#### Root Cause
In `resources/views/pages/components/index.blade.php`, lines 207-218 contained leftover code referencing `$available` from the legacy bulk quantity model. Since Component now tracks individual physical units, `$available` is no longer passed or needed in the table iteration.

#### Solution Plan
- Replace the legacy bulk quantity column (`$available`) in `index.blade.php` with individual unit details (e.g. Purchase / Release Date).
- Verify table header columns match table row cells (6 columns):
  1. Checkbox
  2. Tag / Component Name
  3. Category & Model
  4. Status Badge (Ready / Installed / Maintenance / Retired)
  5. Assigned Asset (with link)
  6. Location
  7. Actions Dropdown
- Run `vendor/bin/pint`, `php artisan test`, and `npm run build`.

#### Files to Modify
- `resources/views/pages/components/index.blade.php`