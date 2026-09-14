<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Company;
use App\Models\Component;
use App\Models\Consumable;
use App\Models\Department;
use App\Models\License;
use App\Models\Location;
use App\Models\MasterCategory;
use App\Models\OperatingSystem;
use App\Models\Position;
use App\Models\Product;
use App\Models\Status;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ImportController extends Controller
{
    public function index()
    {
        return view('pages.import.index');
    }

    public function preview(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx|max:10240',
            'type' => 'required|in:assets,users,consumables,companies,departments,locations,brands,categories,positions,vendors,products,components,licenses',
        ]);

        $path = $request->file('file')->getRealPath();

        if ($request->file('file')->getClientOriginalExtension() === 'csv' || $request->file('file')->getClientOriginalExtension() === 'txt') {
            $rows = array_map('str_getcsv', file($path));
        } else {
            return back()->with('error', 'XLSX not supported yet. Use CSV.');
        }

        $header = array_shift($rows);
        if (! $header) {
            return back()->with('error', 'Empty file.');
        }

        $errors = [];
        $preview = [];
        $maxPreview = 10;

        foreach ($rows as $i => $row) {
            if (count($row) < count($header)) {
                continue;
            }
            $data = array_combine(array_slice($header, 0, count($row)), $row);
            if ($i < $maxPreview) {
                $preview[] = $data;
            }

            if ($request->type === 'assets') {
                $v = Validator::make($data, [
                    'asset_tag' => 'nullable|string',
                    'serial_number' => 'nullable|string',
                    'device_name' => 'nullable|string',
                ]);
                if ($v->fails()) {
                    $errors[] = 'Row '.($i + 2).': '.implode(', ', $v->errors()->all());
                }
            }
        }

        session()->flash('import_rows', json_encode($rows));
        session()->flash('import_header', json_encode($header));
        session()->flash('import_type', $request->type);

        return view('pages.import.preview', compact('header', 'preview', 'errors', 'maxPreview'));
    }

    public function process(Request $request)
    {
        $header = json_decode(session('import_header'), true);
        $rows = json_decode(session('import_rows'), true);
        $type = session('import_type');

        if (! $rows || ! $header) {
            return redirect()->route('import.index')->with('error', 'No data to import.');
        }

        $validRows = [];
        $skipped = 0;

        foreach ($rows as $row) {
            if (count($row) < count($header)) {
                $skipped++;

                continue;
            }
            $data = array_combine(array_slice($header, 0, count($row)), $row);
            $validRows[] = $data;
        }

        $imported = 0;
        if ($type === 'assets') {
            $itMasterCat = MasterCategory::where('name', 'IT Asset')->first();

            foreach ($validRows as $data) {
                if (empty($data['serial_number']) && empty($data['asset_tag'])) {
                    continue;
                }

                if (! empty($data['serial_number']) && Asset::where('serial_number', $data['serial_number'])->exists()) {
                    $skipped++;

                    continue;
                }

                // Resolve Brand & Category & Product
                $brandId = ! empty($data['brand_name'])
                    ? Brand::firstOrCreate(['name' => trim($data['brand_name'])])->id
                    : null;

                $categoryId = ! empty($data['category_name'])
                    ? Category::firstOrCreate(
                        ['name' => trim($data['category_name'])],
                        ['master_category_id' => $itMasterCat?->id]
                    )->id
                    : null;

                $modelName = ! empty($data['model_name']) ? trim($data['model_name']) : (! empty($data['device_name']) ? trim($data['device_name']) : 'General Model');
                $product = Product::firstOrCreate(
                    ['name' => $modelName],
                    [
                        'brand_id' => $brandId,
                        'category_id' => $categoryId,
                        'master_category_id' => $itMasterCat?->id,
                    ]
                );

                // Resolve Operating System
                $osId = ! empty($data['operating_system'])
                    ? OperatingSystem::firstOrCreate(['name' => trim($data['operating_system'])])->id
                    : null;

                // Resolve Vendor
                $vendorId = ! empty($data['vendor_name'])
                    ? Vendor::firstOrCreate(['name' => trim($data['vendor_name'])])->id
                    : null;

                // Resolve Status
                $statusId = ! empty($data['status_name'])
                    ? Status::where('name', trim($data['status_name']))->first()?->id
                    : null;

                if (! $statusId) {
                    $statusId = Status::where('name', 'Ready to Deploy')->first()?->id ?? Status::first()?->id;
                }

                // Resolve Company, Department, Location
                $companyId = ! empty($data['company_name'])
                    ? Company::firstOrCreate(['name' => trim($data['company_name'])])->id
                    : null;

                $departmentId = ! empty($data['department_name'])
                    ? Department::firstOrCreate(
                        ['name' => trim($data['department_name'])],
                        ['company_id' => $companyId]
                    )->id
                    : null;

                $locationId = ! empty($data['location_name'])
                    ? Location::firstOrCreate(['name' => trim($data['location_name'])])->id
                    : null;

                // Resolve Assigned User (by email or name)
                $assignedUserId = null;
                if (! empty($data['assigned_user_email'])) {
                    $userSearch = trim($data['assigned_user_email']);
                    $user = User::where('email', $userSearch)->orWhere('name', 'like', "%{$userSearch}%")->first();
                    if (! $user && strpos($userSearch, '@') === false) {
                        // Auto-create user if name is provided
                        $user = User::create([
                            'name' => $userSearch,
                            'email' => strtolower(str_replace(' ', '.', $userSearch)).'@diastika.co.id',
                            'password' => Hash::make('password123'),
                            'department_id' => $departmentId,
                            'company_id' => $companyId,
                        ]);
                    }
                    $assignedUserId = $user?->id;
                }

                Asset::create([
                    'product_id' => $product->id,
                    'operating_system_id' => $osId,
                    'vendor_id' => $vendorId,
                    'status_id' => $statusId,
                    'department_id' => $departmentId,
                    'location_id' => $locationId,
                    'assigned_to_user_id' => $assignedUserId,
                    'asset_tag' => ! empty($data['asset_tag']) ? trim($data['asset_tag']) : null,
                    'serial_number' => ! empty($data['serial_number']) ? trim($data['serial_number']) : null,
                    'product_number' => ! empty($data['product_number']) ? trim($data['product_number']) : null,
                    'device_name' => ! empty($data['device_name']) ? trim($data['device_name']) : $modelName,
                    'purchase_date' => ! empty($data['purchase_date']) ? trim($data['purchase_date']) : null,
                    'purchase_cost' => ! empty($data['purchase_cost']) ? (float) $data['purchase_cost'] : 0,
                    'invoice_number' => ! empty($data['invoice_number']) ? trim($data['invoice_number']) : null,
                    'warranty_expiry_date' => ! empty($data['warranty_expiry_date']) ? trim($data['warranty_expiry_date']) : null,
                    'license_windows' => ! empty($data['license_windows']) ? trim($data['license_windows']) : null,
                    'license_office' => ! empty($data['license_office']) ? trim($data['license_office']) : null,
                    'mac_address_wireless' => ! empty($data['mac_address_wireless']) ? trim($data['mac_address_wireless']) : null,
                    'mac_address_ethernet' => ! empty($data['mac_address_ethernet']) ? trim($data['mac_address_ethernet']) : null,
                    'specification' => ! empty($data['specification']) ? trim($data['specification']) : null,
                    'accessories' => ! empty($data['accessories']) ? trim($data['accessories']) : null,
                    'notes' => ! empty($data['notes']) ? trim($data['notes']) : null,
                ]);
                $imported++;
            }
        } elseif ($type === 'users') {
            foreach ($validRows as $data) {
                if (empty($data['email'])) {
                    continue;
                }

                if (User::where('email', trim($data['email']))->exists()) {
                    $skipped++;

                    continue;
                }

                $companyId = ! empty($data['company_name'])
                    ? Company::firstOrCreate(['name' => trim($data['company_name'])])->id
                    : null;

                $departmentId = ! empty($data['department_name'])
                    ? Department::firstOrCreate(
                        ['name' => trim($data['department_name'])],
                        ['company_id' => $companyId]
                    )->id
                    : null;

                $positionId = ! empty($data['position_name'])
                    ? Position::firstOrCreate(['name' => trim($data['position_name'])])->id
                    : null;

                $locationId = ! empty($data['location_name'])
                    ? Location::firstOrCreate(['name' => trim($data['location_name'])])->id
                    : null;

                $isActive = isset($data['is_active'])
                    ? (in_array(strtolower(trim($data['is_active'])), ['1', 'true', 'active', 'yes']))
                    : true;

                User::create([
                    'name' => ! empty($data['name']) ? trim($data['name']) : 'User',
                    'email' => trim($data['email']),
                    'phone' => ! empty($data['phone']) ? trim($data['phone']) : null,
                    'password' => Hash::make('password123'),
                    'department_id' => $departmentId,
                    'company_id' => $companyId,
                    'position_id' => $positionId,
                    'location_id' => $locationId,
                    'is_active' => $isActive,
                ]);
                $imported++;
            }
        } elseif ($type === 'consumables') {
            $consumableMasterCat = MasterCategory::where('name', 'IT Consumable')->first();

            foreach ($validRows as $data) {
                if (empty($data['name'])) {
                    continue;
                }

                $brandId = ! empty($data['brand_name'])
                    ? Brand::firstOrCreate(['name' => trim($data['brand_name'])])->id
                    : null;

                $categoryId = ! empty($data['category_name'])
                    ? Category::firstOrCreate(
                        ['name' => trim($data['category_name'])],
                        ['master_category_id' => $consumableMasterCat?->id]
                    )->id
                    : null;

                $product = Product::firstOrCreate(
                    ['name' => trim($data['name'])],
                    [
                        'brand_id' => $brandId,
                        'category_id' => $categoryId,
                        'master_category_id' => $consumableMasterCat?->id,
                    ]
                );

                $statusId = ! empty($data['status_name'])
                    ? Status::where('name', trim($data['status_name']))->first()?->id
                    : (Status::where('name', 'In Stock')->first()?->id ?? Status::first()?->id);

                Consumable::create([
                    'product_id' => $product->id,
                    'status_id' => $statusId,
                    'quantity' => (int) ($data['quantity'] ?? $data['qty'] ?? 10),
                    'low_threshold' => (int) ($data['min_qty'] ?? 5),
                    'purchase_cost' => (float) ($data['purchase_cost'] ?? 0),
                    'notes' => ! empty($data['notes']) ? trim($data['notes']) : null,
                ]);
                $imported++;
            }
        } elseif ($type === 'companies') {
            foreach ($validRows as $data) {
                if (empty($data['name'])) {
                    continue;
                }

                if (Company::where('name', trim($data['name']))->exists()) {
                    $skipped++;

                    continue;
                }

                Company::create([
                    'name' => trim($data['name']),
                    'code' => ! empty($data['code']) ? trim($data['code']) : null,
                    'email' => ! empty($data['email']) ? trim($data['email']) : null,
                    'phone' => ! empty($data['phone']) ? trim($data['phone']) : null,
                    'website' => ! empty($data['website']) ? trim($data['website']) : null,
                    'address' => ! empty($data['address']) ? trim($data['address']) : null,
                    'notes' => ! empty($data['notes']) ? trim($data['notes']) : null,
                ]);
                $imported++;
            }
        } elseif ($type === 'departments') {
            foreach ($validRows as $data) {
                if (empty($data['name'])) {
                    continue;
                }

                if (Department::where('name', trim($data['name']))->exists()) {
                    $skipped++;

                    continue;
                }

                $companyId = ! empty($data['company_name'])
                    ? Company::firstOrCreate(['name' => trim($data['company_name'])])->id
                    : null;

                Department::create([
                    'name' => trim($data['name']),
                    'code' => ! empty($data['code']) ? trim($data['code']) : null,
                    'company_id' => $companyId,
                    'notes' => ! empty($data['notes']) ? trim($data['notes']) : null,
                ]);
                $imported++;
            }
        } elseif ($type === 'locations') {
            foreach ($validRows as $data) {
                if (empty($data['name'])) {
                    continue;
                }

                if (Location::where('name', trim($data['name']))->exists()) {
                    $skipped++;

                    continue;
                }

                $companyId = ! empty($data['company_name'])
                    ? Company::firstOrCreate(['name' => trim($data['company_name'])])->id
                    : null;

                Location::create([
                    'name' => trim($data['name']),
                    'code' => ! empty($data['code']) ? trim($data['code']) : null,
                    'company_id' => $companyId,
                    'address' => ! empty($data['address']) ? trim($data['address']) : null,
                    'city' => ! empty($data['city']) ? trim($data['city']) : null,
                    'state' => ! empty($data['state']) ? trim($data['state']) : null,
                    'zip' => ! empty($data['zip']) ? trim($data['zip']) : null,
                    'country' => ! empty($data['country']) ? trim($data['country']) : null,
                    'notes' => ! empty($data['notes']) ? trim($data['notes']) : null,
                ]);
                $imported++;
            }
        } elseif ($type === 'brands') {
            foreach ($validRows as $data) {
                if (empty($data['name'])) {
                    continue;
                }

                if (Brand::where('name', trim($data['name']))->exists()) {
                    $skipped++;

                    continue;
                }

                Brand::create([
                    'name' => trim($data['name']),
                    'website' => ! empty($data['website']) ? trim($data['website']) : null,
                    'support_phone' => ! empty($data['support_phone']) ? trim($data['support_phone']) : null,
                    'support_email' => ! empty($data['support_email']) ? trim($data['support_email']) : null,
                    'notes' => ! empty($data['notes']) ? trim($data['notes']) : null,
                ]);
                $imported++;
            }
        } elseif ($type === 'categories') {
            foreach ($validRows as $data) {
                if (empty($data['name'])) {
                    continue;
                }

                if (Category::where('name', trim($data['name']))->exists()) {
                    $skipped++;

                    continue;
                }

                $masterCategory = ! empty($data['master_category_name'])
                    ? MasterCategory::where('name', trim($data['master_category_name']))->first()
                    : MasterCategory::first();

                Category::create([
                    'name' => trim($data['name']),
                    'code' => ! empty($data['code']) ? trim($data['code']) : null,
                    'master_category_id' => $masterCategory?->id,
                    'notes' => ! empty($data['notes']) ? trim($data['notes']) : null,
                ]);
                $imported++;
            }
        } elseif ($type === 'positions') {
            foreach ($validRows as $data) {
                if (empty($data['name'])) {
                    continue;
                }

                if (Position::where('name', trim($data['name']))->exists()) {
                    $skipped++;

                    continue;
                }

                $departmentId = ! empty($data['department_name'])
                    ? Department::firstOrCreate(['name' => trim($data['department_name'])])->id
                    : null;

                Position::create([
                    'name' => trim($data['name']),
                    'code' => ! empty($data['code']) ? trim($data['code']) : null,
                    'department_id' => $departmentId,
                    'notes' => ! empty($data['notes']) ? trim($data['notes']) : null,
                ]);
                $imported++;
            }
        } elseif ($type === 'vendors') {
            foreach ($validRows as $data) {
                if (empty($data['name'])) {
                    continue;
                }

                if (Vendor::where('name', trim($data['name']))->exists()) {
                    $skipped++;

                    continue;
                }

                Vendor::create([
                    'name' => trim($data['name']),
                    'contact_person' => ! empty($data['contact_person']) ? trim($data['contact_person']) : null,
                    'email' => ! empty($data['email']) ? trim($data['email']) : null,
                    'phone' => ! empty($data['phone']) ? trim($data['phone']) : null,
                    'website' => ! empty($data['website']) ? trim($data['website']) : null,
                    'address' => ! empty($data['address']) ? trim($data['address']) : null,
                    'npwp_number' => ! empty($data['npwp_number']) ? trim($data['npwp_number']) : null,
                    'npwp_name' => ! empty($data['npwp_name']) ? trim($data['npwp_name']) : null,
                    'nib' => ! empty($data['nib']) ? trim($data['nib']) : null,
                    'bank_name' => ! empty($data['bank_name']) ? trim($data['bank_name']) : null,
                    'bank_account_number' => ! empty($data['bank_account_number']) ? trim($data['bank_account_number']) : null,
                    'bank_account_name' => ! empty($data['bank_account_name']) ? trim($data['bank_account_name']) : null,
                    'notes' => ! empty($data['notes']) ? trim($data['notes']) : null,
                ]);
                $imported++;
            }
        } elseif ($type === 'products') {
            foreach ($validRows as $data) {
                if (empty($data['name'])) {
                    continue;
                }

                if (Product::where('name', trim($data['name']))->exists()) {
                    $skipped++;

                    continue;
                }

                $brandId = ! empty($data['brand_name'])
                    ? Brand::firstOrCreate(['name' => trim($data['brand_name'])])->id
                    : null;

                $masterCat = ! empty($data['master_category_name'])
                    ? MasterCategory::where('name', trim($data['master_category_name']))->first()
                    : MasterCategory::first();

                $categoryId = ! empty($data['category_name'])
                    ? Category::firstOrCreate(
                        ['name' => trim($data['category_name'])],
                        ['master_category_id' => $masterCat?->id]
                    )->id
                    : null;

                Product::create([
                    'name' => trim($data['name']),
                    'brand_id' => $brandId,
                    'category_id' => $categoryId,
                    'master_category_id' => $masterCat?->id,
                    'product_type' => ! empty($data['product_type']) ? trim($data['product_type']) : 'Hardware',
                    'description' => ! empty($data['description']) ? trim($data['description']) : null,
                ]);
                $imported++;
            }
        } elseif ($type === 'components') {
            foreach ($validRows as $data) {
                if (empty($data['name'])) {
                    continue;
                }

                $categoryId = ! empty($data['category_name'])
                    ? Category::firstOrCreate(['name' => trim($data['category_name'])])->id
                    : null;

                $locationId = ! empty($data['location_name'])
                    ? Location::firstOrCreate(['name' => trim($data['location_name'])])->id
                    : null;

                Component::create([
                    'name' => trim($data['name']),
                    'category_id' => $categoryId,
                    'manufacturer' => ! empty($data['brand_name']) ? trim($data['brand_name']) : null,
                    'serial' => ! empty($data['serial_number']) ? trim($data['serial_number']) : null,
                    'qty' => (int) ($data['quantity'] ?? 1),
                    'min_qty' => (int) ($data['min_qty'] ?? 1),
                    'purchase_cost' => (float) ($data['purchase_cost'] ?? 0),
                    'location_id' => $locationId,
                    'notes' => ! empty($data['notes']) ? trim($data['notes']) : null,
                ]);
                $imported++;
            }
        } elseif ($type === 'licenses') {
            foreach ($validRows as $data) {
                if (empty($data['name'])) {
                    continue;
                }

                License::create([
                    'name' => trim($data['name']),
                    'product_key' => ! empty($data['license_key']) ? trim($data['license_key']) : null,
                    'seats' => (int) ($data['seats'] ?? 1),
                    'manufacturer' => ! empty($data['manufacturer_name']) ? trim($data['manufacturer_name']) : null,
                    'purchase_cost' => (float) ($data['purchase_cost'] ?? 0),
                    'purchase_date' => ! empty($data['purchase_date']) ? trim($data['purchase_date']) : null,
                    'expiration_date' => ! empty($data['expiration_date']) ? trim($data['expiration_date']) : null,
                    'notes' => ! empty($data['notes']) ? trim($data['notes']) : null,
                ]);
                $imported++;
            }
        }

        session()->forget(['import_header', 'import_rows', 'import_type']);

        return redirect()->route('import.index')->with('success', "Imported {$imported} records. Skipped {$skipped}.");
    }

    public function downloadTemplate($type)
    {
        switch ($type) {
            case 'assets':
                $headers = [
                    'asset_tag',
                    'device_name',
                    'serial_number',
                    'product_number',
                    'brand_name',
                    'category_name',
                    'model_name',
                    'operating_system',
                    'status_name',
                    'company_name',
                    'department_name',
                    'location_name',
                    'assigned_user_email',
                    'vendor_name',
                    'purchase_date',
                    'purchase_cost',
                    'invoice_number',
                    'warranty_expiry_date',
                    'license_windows',
                    'license_office',
                    'mac_address_wireless',
                    'mac_address_ethernet',
                    'specification',
                    'accessories',
                    'notes',
                ];
                $rows = [
                    [
                        'AST-IT-2026-001',
                        'MacBook Pro M3 Max',
                        'SN-MAC2026-001',
                        'PN-MBP16-01',
                        'Apple',
                        'Laptops',
                        'MacBook Pro 16 Inch M3 Max',
                        'macOS Sequoia',
                        'Ready to Deploy',
                        'PT Diastika Biotekindo',
                        'Information Technology',
                        'Head Office Jakarta',
                        'dzul.khair@diastika.co.id',
                        'Bhinneka Mentari Dimensi',
                        '2026-01-15',
                        '45000000',
                        'INV-2026-001',
                        '2028-01-15',
                        '',
                        '',
                        'A1:B2:C3:D4:E5:F6',
                        'A1:B2:C3:D4:E5:F7',
                        'Apple M3 Max 36GB RAM 1TB SSD',
                        '140W USB-C Power Adapter, MagSafe 3 Cable',
                        'Primary Developer Laptop',
                    ],
                    [
                        'AST-IT-2026-002',
                        'Dell XPS 15 Workstation',
                        'SN-DELL2026-002',
                        'PN-XPS15-9530',
                        'Dell',
                        'Laptops',
                        'Dell XPS 15 9530',
                        'Windows 11 Pro',
                        'Deployed',
                        'PT Diastika Biotekindo',
                        'Finance & Accounting',
                        'Head Office Jakarta',
                        'admin.finance2@diastika.co.id',
                        'Global IT Solution',
                        '2026-02-01',
                        '32000000',
                        'INV-2026-088',
                        '2029-02-01',
                        'WIN11-PRO-KEY-XYZ',
                        'OFFICE-2021-KEY-ABC',
                        'B1:C2:D3:E4:F5:A6',
                        'B1:C2:D3:E4:F5:A7',
                        'Intel Core i7 32GB RAM 1TB SSD RTX 4060',
                        'Charger 130W USB-C, Dell Backpack',
                        'Finance Lead Laptop',
                    ],
                ];
                break;

            case 'consumables':
                $headers = [
                    'name',
                    'category_name',
                    'brand_name',
                    'company_name',
                    'location_name',
                    'quantity',
                    'min_qty',
                    'purchase_cost',
                    'status_name',
                    'notes',
                ];
                $rows = [
                    [
                        'Logitech MX Master 3S Mouse',
                        'Peripherals',
                        'Logitech',
                        'PT Diastika Biotekindo',
                        'Head Office Jakarta',
                        '50',
                        '5',
                        '1490000',
                        'In Stock',
                        'Wireless Ergonomic Mouse for Staff',
                    ],
                    [
                        'HDMI Cable 2m 4K',
                        'Cables & Adapters',
                        'Vention',
                        'PT Diastika Biotekindo',
                        'Head Office Jakarta',
                        '100',
                        '10',
                        '85000',
                        'In Stock',
                        'High Speed HDMI Cable for Meeting Rooms',
                    ],
                ];
                break;

            case 'users':
                $headers = [
                    'name',
                    'email',
                    'phone',
                    'company_name',
                    'department_name',
                    'position_name',
                    'location_name',
                    'is_active',
                ];
                $rows = [
                    [
                        'Budi Pratama',
                        'budi.pratama@diastika.co.id',
                        '081234567890',
                        'PT Diastika Biotekindo',
                        'Information Technology',
                        'Senior Developer',
                        'Head Office Jakarta',
                        'Active',
                    ],
                    [
                        'Siti Aminah',
                        'siti.aminah@diastika.co.id',
                        '081234567891',
                        'PT Diastika Biotekindo',
                        'Finance & Accounting',
                        'Financial Analyst',
                        'Head Office Jakarta',
                        'Active',
                    ],
                ];
                break;

            case 'companies':
                $headers = ['name', 'code', 'email', 'phone', 'website', 'address', 'notes'];
                $rows = [
                    ['PT Diastika Biotekindo', 'DB', 'info@diastika.co.id', '021-1234567', 'https://diastika.co.id', 'Kabayoran Lama, Jakarta', 'Head Company'],
                    ['PT UBC Indonesia', 'UBC', 'contact@ubcindonesia.com', '021-7654321', 'https://ubcindonesia.com', 'Jakarta', 'Subsidiary Company'],
                ];
                break;

            case 'departments':
                $headers = ['name', 'code', 'company_name', 'notes'];
                $rows = [
                    ['Information Technology', 'IT', 'PT Diastika Biotekindo', 'IT Department'],
                    ['Finance & Accounting', 'FIN', 'PT Diastika Biotekindo', 'Finance & Tax Department'],
                    ['Human Resources', 'HR', 'PT Diastika Biotekindo', 'HR & GA Department'],
                ];
                break;

            case 'locations':
                $headers = ['name', 'code', 'company_name', 'address', 'city', 'state', 'zip', 'country', 'notes'];
                $rows = [
                    ['Head Office Jakarta', 'HO-JKT', 'PT Diastika Biotekindo', 'Jl. Kebayoran Lama No. 12', 'Jakarta Selatan', 'DKI Jakarta', '12210', 'Indonesia', 'Main Headquarter'],
                    ['Branch Surabaya', 'BR-SBY', 'PT Diastika Biotekindo', 'Jl. Raya Darmo', 'Surabaya', 'Jawa Timur', '60241', 'Indonesia', 'Surabaya Office'],
                ];
                break;

            case 'brands':
                $headers = ['name', 'website', 'support_phone', 'support_email', 'notes'];
                $rows = [
                    ['Apple', 'https://apple.com', '1-800-MY-APPLE', 'support@apple.com', 'Apple Hardware & Devices'],
                    ['Dell', 'https://dell.com', '1-800-DELL-CARE', 'support@dell.com', 'Dell Workstations & Servers'],
                    ['Logitech', 'https://logitech.com', '1-800-LOGITECH', 'support@logitech.com', 'Computer Peripherals'],
                ];
                break;

            case 'categories':
                $headers = ['name', 'master_category_name', 'code', 'eula_name', 'notes'];
                $rows = [
                    ['Laptops', 'IT Asset', 'LAP', 'Standard Hardware EULA', 'Laptop Computers'],
                    ['Desktops', 'IT Asset', 'DESK', 'Standard Hardware EULA', 'Desktop PC & All-in-One'],
                    ['Peripherals', 'IT Consumable', 'PERIPH', '', 'Mice, Keyboards, Audio'],
                ];
                break;

            case 'positions':
                $headers = ['name', 'code', 'department_name', 'notes'];
                $rows = [
                    ['Senior Developer', 'SR-DEV', 'Information Technology', 'Lead Developer Position'],
                    ['IT Support Specialist', 'IT-SUPP', 'Information Technology', 'Helpdesk & Support'],
                    ['Financial Analyst', 'FIN-ANL', 'Finance & Accounting', 'Finance Analyst'],
                ];
                break;

            case 'vendors':
                $headers = ['name', 'contact_person', 'email', 'phone', 'website', 'address', 'npwp_number', 'npwp_name', 'nib', 'bank_name', 'bank_account_number', 'bank_account_name', 'notes'];
                $rows = [
                    ['PT Jasanet Mitra Network', 'Erna', 'admin@jasanet.co.id', '085894711377', 'https://jasanet.co.id', 'Jakarta', '12.345.678.9-012.000', 'PT Jasanet', '1234567890', 'BCA', '1234567890', 'PT Jasanet Mitra Network', 'Network Vendor'],
                    ['Bhinneka Mentari Dimensi', 'Sales Team', 'sales@bhinneka.com', '021-29292828', 'https://bhinneka.com', 'Jakarta', '', '', '', 'BCA', '0987654321', 'Bhinneka Mentari Dimensi', 'IT Hardware Vendor'],
                ];
                break;

            case 'products':
                $headers = ['name', 'brand_name', 'category_name', 'master_category_name', 'product_type', 'description'];
                $rows = [
                    ['MacBook Pro 16 M3 Max', 'Apple', 'Laptops', 'IT Asset', 'Hardware', '16-inch Apple Laptop'],
                    ['Dell XPS 15 9530', 'Dell', 'Laptops', 'IT Asset', 'Hardware', '15-inch Dell Laptop'],
                ];
                break;

            case 'components':
                $headers = ['name', 'category_name', 'brand_name', 'serial_number', 'quantity', 'min_qty', 'purchase_cost', 'location_name', 'notes'];
                $rows = [
                    ['DDR5 32GB RAM SODIMM', 'Memory RAM', 'Corsair', 'SN-RAM-001', '20', '5', '1850000', 'Head Office Jakarta', 'DDR5 Laptop Memory'],
                    ['Samsung 990 PRO 2TB SSD', 'Storage SSD', 'Samsung', 'SN-SSD-002', '15', '3', '2450000', 'Head Office Jakarta', 'NVMe M.2 SSD'],
                ];
                break;

            case 'licenses':
                $headers = ['name', 'license_key', 'category_name', 'manufacturer_name', 'seats', 'purchase_cost', 'purchase_date', 'expiration_date', 'notes'];
                $rows = [
                    ['Microsoft 365 Business Premium', 'MS365-KEY-2026-XYZ', 'Software License', 'Microsoft', '100', '25000000', '2026-01-01', '2027-01-01', 'Annual License'],
                    ['Adobe Creative Cloud All Apps', 'ADOBE-CC-2026-ABC', 'Software License', 'Adobe', '15', '18000000', '2026-02-01', '2027-02-01', 'Design Team Licenses'],
                ];
                break;

            default:
                $headers = ['name', 'email'];
                $rows = [];
                break;
        }

        $fp = fopen('php://temp', 'r+');
        fputcsv($fp, $headers);
        foreach ($rows as $row) {
            fputcsv($fp, $row);
        }
        rewind($fp);
        $csv = stream_get_contents($fp);
        fclose($fp);

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename={$type}_template.csv",
        ]);
    }
}
