### Enhancement: Add Component Tag, Installation Release Date, and Asset Linking

#### 1. Schema Updates
- **`components` table**: Add `component_tag` column (string, unique, nullable, e.g. `CMP/BATT/260912/001` or `CMP-00001`).
- **`component_asset` table**: Ensure `checked_out_at` (Release / Installation Date) is tracked properly and accept custom `release_date` input during checkout.

#### 2. Auto Component Tag Generator & Model Updates
- **`Component` Model**:
  - Add `component_tag` to `$fillable`.
  - Add boot method `creating` to auto-generate `component_tag` if not provided (e.g. `CMP/{CategoryCode}/{DateCode}/{Seq}`).

#### 3. Controller Updates (`ComponentController`)
- **`checkout`**:
  - Accept `release_date` (default: today / now).
  - Save `checked_out_at = $request->release_date`.
  - Pass `release_date` into the auto-generated Ticket description (`[Auto-Generated] Component Installed on {release_date}`).
- **`index` & `show`**:
  - Display `component_tag` beside the component name in monospace font.
  - Display **Assigned Assets** with direct clickable links to the target Asset detail pages (`/assets/{id}`).
  - Display **Installation / Release Date** in the checkout assets table.

#### 4. UI View Updates
- **`index.blade.php`**: Show `component_tag` and clickable badges/links for assigned assets.
- **`create.blade.php` & `edit.blade.php`**: Add `component_tag` field (optional with auto-generation hint).
- **`show.blade.php`**: Add `Release Date` datepicker input to the Checkout form, and display `checked_out_at` date in the Installed Assets list.

#### Files to Create / Modify
- `database/migrations/YYYY_MM_DD_HHMMSS_add_component_tag_to_components_table.php` (New)
- `app/Models/Component.php`
- `app/Http/Controllers/ComponentController.php`
- `resources/views/pages/components/index.blade.php`
- `resources/views/pages/components/create.blade.php`
- `resources/views/pages/components/edit.blade.php`
- `resources/views/pages/components/show.blade.php`