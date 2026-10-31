<?php

namespace App\Services;

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
use App\Models\Product;
use App\Models\Status;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ImportService
{
    public function parseCsvFile(UploadedFile $file): array
    {
        $path = $file->getRealPath();
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if (empty($lines)) {
            return ['header' => [], 'rows' => []];
        }

        $firstLine = $lines[0];
        $delimiter = ',';
        if (substr_count($firstLine, ';') > substr_count($firstLine, ',')) {
            $delimiter = ';';
        } elseif (substr_count($firstLine, "\t") > substr_count($firstLine, ',')) {
            $delimiter = "\t";
        }

        $rows = array_map(fn ($line) => str_getcsv($line, $delimiter), $lines);
        $header = array_shift($rows) ?? [];

        return [
            'header' => $header,
            'rows' => $rows,
        ];
    }

    public function processImport(string $type, array $data): int
    {
        return DB::transaction(function () use ($type, $data) {
            $importedCount = 0;

            foreach ($data as $item) {
                switch ($type) {
                    case 'assets':
                        if ($this->importSingleAsset($item)) {
                            $importedCount++;
                        }
                        break;
                    case 'users':
                        if ($this->importSingleUser($item)) {
                            $importedCount++;
                        }
                        break;
                    case 'consumables':
                        if ($this->importSingleConsumable($item)) {
                            $importedCount++;
                        }
                        break;
                    case 'components':
                        if ($this->importSingleComponent($item)) {
                            $importedCount++;
                        }
                        break;
                    case 'licenses':
                        if ($this->importSingleLicense($item)) {
                            $importedCount++;
                        }
                        break;
                    case 'companies':
                        if (! empty($item['company_name']) && ! empty($item['company_code'])) {
                            Company::firstOrCreate(
                                ['code' => trim($item['company_code'])],
                                ['name' => trim($item['company_name']), 'email' => $item['email'] ?? null]
                            );
                            $importedCount++;
                        }
                        break;
                    case 'departments':
                        if (! empty($item['department_name']) && ! empty($item['department_code'])) {
                            $company = Company::first();
                            Department::firstOrCreate(
                                ['code' => trim($item['department_code'])],
                                ['name' => trim($item['department_name']), 'company_id' => $company?->id ?? 1]
                            );
                            $importedCount++;
                        }
                        break;
                    case 'locations':
                        if (! empty($item['location_name'])) {
                            $company = Company::first();
                            Location::firstOrCreate(
                                ['name' => trim($item['location_name'])],
                                ['company_id' => $company?->id ?? 1]
                            );
                            $importedCount++;
                        }
                        break;
                    case 'brands':
                        if (! empty($item['brand_name'])) {
                            Brand::firstOrCreate(['name' => trim($item['brand_name'])]);
                            $importedCount++;
                        }
                        break;
                    case 'categories':
                        if (! empty($item['category_name'])) {
                            Category::firstOrCreate(['name' => trim($item['category_name'])]);
                            $importedCount++;
                        }
                        break;
                    case 'vendors':
                        if (! empty($item['vendor_name'])) {
                            Vendor::firstOrCreate(['name' => trim($item['vendor_name'])]);
                            $importedCount++;
                        }
                        break;
                }
            }

            return $importedCount;
        });
    }

    private function importSingleAsset(array $data): bool
    {
        if (empty($data['serial_number']) && empty($data['asset_tag'])) {
            return false;
        }

        $company = ! empty($data['company_code']) ? Company::where('code', trim($data['company_code']))->first() : Company::first();

        $category = null;
        if (! empty($data['category_name'])) {
            $category = Category::where('name', trim($data['category_name']))->first();
            if (! $category) {
                $masterCat = MasterCategory::where('name', 'IT Asset')->first();
                $category = Category::create([
                    'name' => trim($data['category_name']),
                    'code' => strtoupper(substr(trim($data['category_name']), 0, 3)),
                    'master_category_id' => $masterCat?->id,
                ]);
            }
        }

        $brand = ! empty($data['brand_name']) ? Brand::firstOrCreate(['name' => trim($data['brand_name'])]) : null;

        $product = null;
        if (! empty($data['product_name'])) {
            $product = Product::where('name', trim($data['product_name']))->first();
            if (! $product) {
                $masterCat = MasterCategory::where('name', 'IT Asset')->first();
                $product = Product::create([
                    'name' => trim($data['product_name']),
                    'product_type' => $data['product_type'] ?? null,
                    'brand_id' => $brand?->id,
                    'category_id' => $category?->id,
                    'master_category_id' => $masterCat?->id,
                ]);
            }
        }

        $department = null;
        if (! empty($data['department_code'])) {
            $department = Department::where('code', trim($data['department_code']))->first();
        } elseif (! empty($data['department_name'])) {
            $department = Department::where('name', trim($data['department_name']))->first();
        }

        $location = ! empty($data['location_name']) ? Location::where('name', trim($data['location_name']))->first() : null;
        $vendor = ! empty($data['vendor_name']) ? Vendor::where('name', trim($data['vendor_name']))->first() : null;

        $status = null;
        if (! empty($data['status_name'])) {
            $status = Status::where('name', trim($data['status_name']))->first();
        }
        if (! $status) {
            $status = Status::where('name', 'Ready to Deploy')->first() ?? Status::first();
        }

        Asset::create([
            'company_id' => $company?->id,
            'product_id' => $product?->id,
            'status_id' => $status?->id,
            'department_id' => $department?->id,
            'location_id' => $location?->id,
            'vendor_id' => $vendor?->id,
            'asset_tag' => ! empty($data['asset_tag']) ? trim($data['asset_tag']) : null,
            'serial_number' => $data['serial_number'] ?? null,
            'device_name' => $data['device_name'] ?? null,
            'user_name' => $data['user_name'] ?? null,
            'purchase_date' => ! empty($data['purchase_date']) ? $data['purchase_date'] : null,
            'release_date' => ! empty($data['release_date']) ? $data['release_date'] : null,
            'purchase_cost' => ! empty($data['purchase_cost']) ? (float) $data['purchase_cost'] : null,
            'notes' => $data['notes'] ?? null,
        ]);

        return true;
    }

    private function importSingleUser(array $data): bool
    {
        if (empty($data['email']) || empty($data['name'])) {
            return false;
        }

        $company = ! empty($data['company_code']) ? Company::where('code', trim($data['company_code']))->first() : Company::first();
        $department = ! empty($data['department_code']) ? Department::where('code', trim($data['department_code']))->first() : null;

        User::firstOrCreate(
            ['email' => trim($data['email'])],
            [
                'name' => trim($data['name']),
                'password' => Hash::make($data['password'] ?? 'password'),
                'company_id' => $company?->id,
                'department_id' => $department?->id,
            ]
        );

        return true;
    }

    private function importSingleConsumable(array $data): bool
    {
        if (empty($data['name'])) {
            return false;
        }

        $masterCat = MasterCategory::where('name', 'IT Consumable')->first();
        $product = Product::firstOrCreate(
            ['name' => trim($data['name'])],
            ['master_category_id' => $masterCat?->id]
        );

        Consumable::create([
            'product_id' => $product->id,
            'quantity' => (int) ($data['quantity'] ?? 0),
            'low_threshold' => (int) ($data['low_threshold'] ?? 5),
            'purchase_cost' => ! empty($data['purchase_cost']) ? (float) $data['purchase_cost'] : null,
            'notes' => $data['notes'] ?? null,
        ]);

        return true;
    }

    private function importSingleComponent(array $data): bool
    {
        if (empty($data['name'])) {
            return false;
        }

        $masterCat = MasterCategory::where('name', 'IT Component')->first();
        $product = Product::firstOrCreate(
            ['name' => trim($data['name'])],
            ['master_category_id' => $masterCat?->id]
        );

        $totalQty = (int) ($data['quantity'] ?? 1);
        $singleCost = ! empty($data['purchase_cost']) ? ((float) $data['purchase_cost'] / max(1, $totalQty)) : null;

        for ($i = 0; $i < $totalQty; $i++) {
            Component::create([
                'product_id' => $product->id,
                'name' => $product->name,
                'qty' => 1,
                'purchase_cost' => $singleCost,
                'status' => 'Ready',
                'notes' => $data['notes'] ?? null,
            ]);
        }

        return true;
    }

    private function importSingleLicense(array $data): bool
    {
        if (empty($data['name'])) {
            return false;
        }

        License::create([
            'name' => trim($data['name']),
            'product_key' => $data['product_key'] ?? null,
            'seats' => (int) ($data['seats'] ?? 1),
            'license_email' => $data['license_email'] ?? null,
            'manufacturer' => $data['manufacturer'] ?? null,
            'purchase_cost' => ! empty($data['purchase_cost']) ? (float) $data['purchase_cost'] : null,
            'notes' => $data['notes'] ?? null,
        ]);

        return true;
    }
}
