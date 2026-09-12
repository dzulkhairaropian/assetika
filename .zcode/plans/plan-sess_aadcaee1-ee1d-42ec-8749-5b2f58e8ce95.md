### Enhancement: Add Release Date & Model Number Input to Component Forms + Build Production Assets

#### 1. Database & Model Updates
- **Migration**: Add `release_date` (date, nullable) to `components` table.
- **Component Model**: Add `release_date` to `$fillable` and `$casts` (`'release_date' => 'date'`).

#### 2. Controller Updates (`ComponentController.php`)
- `store` & `update`:
  - Validate `model_number` (optional string) and `release_date` (optional date).
  - Save `model_number` from request input (fallback to `product->product_type` if empty).

#### 3. View Updates
- **`create.blade.php` & `edit.blade.php`**:
  - Add **Model Number** input field (`model_number`, e.g. `Latitude 3410`).
  - Add **Release Date** datepicker input field (`release_date`).
- **`show.blade.php` & `index.blade.php`**:
  - Display `release_date` in Component details.

#### 4. Asset Build
- Run `npm run build` to compile production assets.

#### Files to Modify / Create
- `database/migrations/YYYY_MM_DD_HHMMSS_add_release_date_to_components_table.php` (New)
- `app/Models/Component.php`
- `app/Http/Controllers/ComponentController.php`
- `resources/views/pages/components/create.blade.php`
- `resources/views/pages/components/edit.blade.php`
- `resources/views/pages/components/show.blade.php`