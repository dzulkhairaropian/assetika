### Fix: Status Badge Rendering & Clean Text Link for Assigned Asset

#### 1. Backfill Existing Data
- Run database update to assign `status_id` and `component_tag` to legacy records (linking to `Ready` or `Installed` status for `IT Component`).

#### 2. Robust Badge Rendering in `index.blade.php` & `show.blade.php`
- In `index.blade.php` and `show.blade.php`, safely resolve `$statusObj` with fallback to `IT Component` master statuses.
- Use `<x-ui.badge :color="$statusColor" variant="light" size="sm" :featherIcon="$statusIcon">{{ $statusName }}</x-ui.badge>`.
- This ensures icons (`user-check`, `circle-check`, `wrench`, `circle-x`, `archive`) render crisply with correct TailAdmin colors (`primary`, `success`, `warning`, `danger`, `secondary`).

#### 3. Clean Assigned Asset Display in `index.blade.php`
- Render the asset cleanly as pure clickable text links with device name and user metadata, without any surrounding badge/box.

#### 4. Verification & Build
- Run `vendor/bin/pint`, `php artisan test`, and `npm run build`.

#### Files to Modify
- `resources/views/pages/components/index.blade.php`
- `resources/views/pages/components/show.blade.php`