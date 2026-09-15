### Enterprise Component Module Upgrade: KPI Widgets, Quick Tabs, Asset-Detail Integration & Quick Status Actions

#### 1. KPI Summary Widget Cards (`index.blade.php`)
- Add 4 TailAdmin small-box KPI metric cards above the table:
  1. **Total Component Units**: Total physical component records.
  2. **Ready (In Stock)**: Units available for deployment.
  3. **Installed Units**: Units currently attached to active laptops/assets.
  4. **Maintenance / Retired**: Units undergoing service or scrapped.

#### 2. Quick Filter Tabs (`index.blade.php`)
- Add interactive filter tabs on top of the Component table:
  - `All` (default)
  - `Ready (In Stock)`
  - `Installed`
  - `Maintenance`
  - `Retired`

#### 3. Component Integration inside Asset Details (`pages/assets/show.blade.php`)
- Add an **"Installed Components"** section / tab inside the Asset detail page:
  - Lists all component units attached to that asset (`assigned_asset_id` = `$asset->id`).
  - Shows `component_tag`, model, serial, installation release date, and a 1-click **"Detach Component"** action.

#### 4. Extended Quick Actions in Component Index
- Allow changing component status directly from index dropdown (`Set to Maintenance`, `Retire Component`).

#### 5. Verification & Build
- Run `vendor/bin/pint`, `php artisan test`, and `npm run build`.

#### Files to Modify
- `app/Http/Controllers/ComponentController.php`
- `app/Http/Controllers/AssetController.php`
- `resources/views/pages/components/index.blade.php`
- `resources/views/pages/assets/show.blade.php`