### Fix: Replace All Remaining Invalid Icon Names `'tool'` with `'wrench'` across Inventory Tabs & Dashboards

#### Root Cause
In `resources/views/pages/assets/index.blade.php` (Service & Repair quick tab), `components/index.blade.php`, `dashboard/it-assets.blade.php`, and `ga-assets/index.blade.php`, the tab icon was set to `'icon' => 'tool'`. Because Lucide uses `wrench` as the valid icon name, the icon failed to render on the Service & Repair metric tab.

#### Solution Plan
1. Update icon definition in `resources/views/pages/assets/index.blade.php` line 129:
   - `'icon' => 'tool'` ➔ `'icon' => 'wrench'`
2. Update icon definition in `resources/views/pages/components/index.blade.php`:
   - `'icon' => 'tool'` ➔ `'icon' => 'wrench'`
3. Update `dashboard/it-assets.blade.php` and `ga-assets/index.blade.php`:
   - `icon="tool"` ➔ `icon="wrench"`
4. Run `vendor/bin/pint`, `php artisan test`, and `npm run build`.

#### Files to Modify
- `resources/views/pages/assets/index.blade.php`
- `resources/views/pages/components/index.blade.php`
- `resources/views/pages/dashboard/it-assets.blade.php`
- `resources/views/pages/ga-assets/index.blade.php`