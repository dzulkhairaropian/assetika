### Refine Status Badge Sizing, Instant SVG Rendering, and Minimalist Clickable Asset Link

#### 1. Fix Badge Component Sizing & Instant Icon Rendering (`badge.blade.php`)
- Remove hardcoded `px-2.5 py-0.5` in `$baseStyles` so `size="sm"` creates a compact, sleek badge (`px-2 py-0.5 text-[10px]`).
- Add clean inline SVG fallbacks in `badge.blade.php` for status icons (`circle-check`, `user-check`, `wrench`, `circle-x`, `archive`, `clock`, `alert-circle`) so icons render instantly on first page load without waiting for async JS replacements.

#### 2. Clean Clickable Assigned Asset Text Column (`index.blade.php`)
- Remove any box/badge icon styling from the Asset column.
- Render pure clean clickable text:
  - Blue font-mono bold link: `DB/NB/250217/008` (clicks to asset detail).
  - Subtle secondary line: Device / Product name (`Victus Gaming 15`).
  - Small meta line: `User: Dzul Khair Aropian`.

#### 3. Verification & Build
- Run `vendor/bin/pint`, `php artisan test`, and `npm run build`.

#### Files to Modify
- `resources/views/components/ui/badge.blade.php`
- `resources/views/pages/components/index.blade.php`