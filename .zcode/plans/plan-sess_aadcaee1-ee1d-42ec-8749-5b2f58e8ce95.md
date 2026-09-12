### Enhancement: Align `component_tag` Generation with `release_date`

#### 1. Logic Alignment in `Component.php`
- Modify the `creating` and `updating` boot hooks in `Component.php`:
  - Determine date code from `release_date` (or `purchase_date` / `now()` as fallback).
  - Format date code as `ymd` (e.g. `260912`).
  - Calculate sequence count for the component's category within that date/year.
  - Generate `component_tag = CMP/{CategoryCode}/{DateCode}/{Seq}` (e.g. `CMP/BATT/260912/001`).

#### 2. Test Verification
- Update `ComponentCheckoutTicketTest.php` to verify `component_tag` generation using `release_date`.
- Run `php artisan test` and `vendor/bin/pint`.
- Re-run `npm run build`.

#### Files to Modify
- `app/Models/Component.php`
- `tests/Feature/ComponentCheckoutTicketTest.php`