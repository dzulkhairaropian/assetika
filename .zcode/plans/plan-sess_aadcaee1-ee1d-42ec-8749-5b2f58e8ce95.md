### Comprehensive Enhancement Plan: Product Catalog Integration & Auto-Ticket Generation for Component CRUD

#### 1. Schema Migration & Model Updates
- **Migration**: Add columns to `components` table:
  - `product_id` (foreign key `products.id`, nullable)
  - `vendor_id` (foreign key `vendors.id`, nullable)
  - `purchase_date` (date, nullable)
  - `warranty_months` (integer, nullable)
- **Component Model**: Add `product()`, `vendor()` relations, `$fillable` fields, and `$casts`.
- **Product Model**: Add `components()` relation.

#### 2. Auto Ticket Generation on Component Checkout & Checkin
- **Checkout Action (`ComponentController@checkout`)**:
  - Validates stock availability.
  - Attaches component to asset via `component_asset` pivot table with quantity and timestamp.
  - **Auto-generates Ticket**:
    - Creates a completed Maintenance/Hardware Ticket linked to the target Asset (`asset_id`).
    - Problem description: `[Auto-Generated] Component Installed: {qty}x {component_name} installed on Asset {asset_tag} ({asset_name})`.
    - Resolution notes: `Component successfully checked out and attached to asset by {user_name}.`
- **Checkin Action (`ComponentController@checkin`)**:
  - Updates or detaches component from asset.
  - **Auto-generates Ticket**:
    - Creates a completed Maintenance/Hardware Ticket linked to the target Asset (`asset_id`).
    - Problem description: `[Auto-Generated] Component Removed: {qty}x {component_name} removed from Asset {asset_tag} ({asset_name})`.
    - Resolution notes: `Component returned to inventory stock by {user_name}.`

#### 3. Controller & CRUD Enhancement (`ComponentController`)
- **`index`**: Include product, category, location, and vendor relationships. Add filters for Product and Category.
- **`create` / `edit`**:
  - Load products under Master Category `IT Component` (with ability to pick or create product).
  - Load Vendors, Locations, Brands, Categories.
- **`show`**:
  - Display Product details, Stock status (Total, In Use, Available), Purchase & Warranty details.
  - Display Asset Checkout list with direct "Checkin" modal/action.

#### 4. UI Modernization (TailAdmin & Hallmark Standard)
- Migrate select dropdowns to `searchable-select` component in `create.blade.php`, `edit.blade.php`, `show.blade.php` (checkout modal).
- Standardize table action dropdowns to fixed-position popups.
- Update checkout modal with quick quantity selector and asset search.

#### Files to Modify / Create
- `database/migrations/YYYY_MM_DD_HHMMSS_add_product_and_purchase_fields_to_components_table.php` (New)
- `app/Models/Component.php`
- `app/Models/Product.php`
- `app/Http/Controllers/ComponentController.php`
- `resources/views/pages/components/index.blade.php`
- `resources/views/pages/components/create.blade.php`
- `resources/views/pages/components/edit.blade.php`
- `resources/views/pages/components/show.blade.php`