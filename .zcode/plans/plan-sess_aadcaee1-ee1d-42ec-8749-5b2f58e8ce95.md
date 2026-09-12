### Redesign Component Module into Individual Trackable Units (Asset-Style Model)

#### 1. Architecture Transition: Bulk Quantity → Unique Individual Units
- **Component Record**: 1 Component record = 1 physical hardware item with a unique `component_tag`, `serial_number`, and current status.
- **Status Model**:
  - `Ready` (Available in inventory stock)
  - `Installed` (Currently attached to a specific Asset)
  - `Maintenance` (Under repair)
  - `Retired` (Scrapped / Disposed)

#### 2. Batch Creation (When Buying Multiple Units, e.g. 6 Batteries)
- **Create Form (`create.blade.php`)**:
  - User selects Product (e.g. `Battery Latitude 3410`) and inputs `Quantity` purchased (e.g. `6`).
  - User fills purchase date, release date, cost per unit, vendor, location.
- **Controller Logic (`ComponentController@store`)**:
  - Automatically generates **N individual component records** (6 items), each receiving a unique `component_tag` (e.g. `DB/BATT/260912/001`, `DB/BATT/260912/002`, ..., `DB/BATT/260912/006`).

#### 3. 1-to-1 Component Assignment to Asset (Checkout & Checkin)
- **Assign to Asset**: Select a specific component unit (e.g. `DB/BATT/260912/001`) and attach it to an Asset (e.g. `AST-001`).
  - Updates component `status` to `Installed` and sets `assigned_asset_id` + `release_date`.
  - Auto-generates completed Maintenance Ticket for that Asset.
- **Detach from Asset (Checkin)**:
  - Updates component `status` back to `Ready` and clears `assigned_asset_id`.
  - Auto-generates completed Removal Maintenance Ticket.

#### 4. Schema Updates
- **`components` table**: Add `assigned_asset_id` (foreign key `assets.id`, nullable) and `status` column (`Ready`, `Installed`, `Maintenance`, `Retired`).

#### Files to Modify / Create
- `database/migrations/YYYY_MM_DD_HHMMSS_add_assigned_asset_and_status_to_components_table.php` (New)
- `app/Models/Component.php`
- `app/Http/Controllers/ComponentController.php`
- `resources/views/pages/components/index.blade.php`
- `resources/views/pages/components/create.blade.php`
- `resources/views/pages/components/edit.blade.php`
- `resources/views/pages/components/show.blade.php`