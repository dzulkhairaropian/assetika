### Form Streamlining: Simplify Component CRUD by Leveraging Master Product Catalog

#### 1. Remove Redundant Fields from Form
- Eliminate `Name`, `Category`, `Manufacturer/Brand`, and `Model Number` manual inputs from `create.blade.php` and `edit.blade.php`.
- Make **Product (`product_id`)** the primary required field using `x-form.searchable-select`.

#### 2. Clean Form Layout Fields
- **Master Product** (`product_id`, Required) - Select from IT Component Products
- **Serial / Batch Number** (`serial`, Optional)
- **Total Qty** (`qty`, Required)
- **Min Qty Alert** (`min_qty`, Optional)
- **Purchase Cost** (`purchase_cost`, Optional)
- **Purchase Date** (`purchase_date`, Optional)
- **Warranty Months** (`warranty_months`, Optional)
- **Vendor / Supplier** (`vendor_id`, Optional)
- **Location** (`location_id`, Optional)
- **Notes** (`notes`, Optional)

#### 3. Controller Logic (`ComponentController`)
- Require `product_id` in validation.
- Auto-set `name = $product->name`, `category_id = $product->category_id`, and `manufacturer = $product->brand?->name`.

#### Files to Modify
- `app/Http/Controllers/ComponentController.php`
- `resources/views/pages/components/create.blade.php`
- `resources/views/pages/components/edit.blade.php`