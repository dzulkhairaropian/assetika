### Format Update: `{CompanyCode}/{CategoryCode}/{DateCode}/{Seq}` for `component_tag`

#### 1. Logic Update in `Component.php`
- Update `component_tag` boot generator in `Component.php`:
  - `CompanyCode`: Get from `auth()->user()->company?->code` or default company code (e.g. `DB`).
  - `CategoryCode`: Get from category code/name uppercase (e.g. `BATT`, `RAM`, `SSD`).
  - `DateCode`: Format from `release_date` / `purchase_date` / `now()` as `YYMMDD` (e.g. `260912`).
  - `Seq`: 3-digit sequence number (`001`, `002`, ...).
  - Final Tag Format: `DB/BATT/260912/001`.

#### 2. Tests & Build
- Update `ComponentCheckoutTicketTest.php` assertion to match `DB/BATT/260915/001`.
- Run `vendor/bin/pint`, `php artisan test`, and `npm run build`.

#### Files to Modify
- `app/Models/Component.php`
- `tests/Feature/ComponentCheckoutTicketTest.php`