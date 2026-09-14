<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Company;
use App\Models\Department;
use App\Models\MasterCategory;
use App\Models\Product;
use App\Models\Status;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class Import22LegacyAssetsSeeder extends Seeder
{
    public function run(): void
    {
        $rawAssets = [
            [
                'tag' => 'DB/NB/181200046',
                'user' => 'Danan',
                'device' => 'TS',
                'jenis' => 'NB',
                'year' => '2018',
                'status' => 'Stored',
                'spec' => 'Notebook Lenovo IP320 Core i3 6006u Ram DDR4 4GB SSD 512 GB 14"',
                'sn' => null,
            ],
            [
                'tag' => 'DB/NB/201200017',
                'user' => 'Chris',
                'device' => 'FAT',
                'jenis' => 'NB',
                'year' => '2020',
                'status' => 'Broken',
                'spec' => 'Notebook DELL Inspiron 3481 i3 7020 Ram 4GB SSD 256 GB Display 14"',
                'sn' => null,
            ],
            [
                'tag' => 'DB/NB/201200019',
                'user' => 'Eva Ryski',
                'prev_user' => 'Katharina',
                'device' => 'FAT',
                'jenis' => 'NB',
                'year' => '2020',
                'status' => 'Broken',
                'spec' => 'Notebook DELL Inspiron 3401 i3 7020 Ram 4GB SSD 256 GB Display 14"',
                'sn' => null,
            ],
            [
                'tag' => 'DB/NB/21080114',
                'user' => 'Safirah',
                'prev_user' => 'Regina Lenny',
                'device' => 'TS',
                'jenis' => 'NB',
                'year' => '2021',
                'status' => 'Stored',
                'spec' => 'Notebook DELL Vostro 3410 i3 i3-10110U Ram 4GB SSD 512 Display 14"',
                'sn' => null,
            ],
            [
                'tag' => 'DB/15050029',
                'user' => 'Sofie',
                'device' => null,
                'jenis' => 'NB',
                'year' => '2015',
                'status' => 'Retired',
                'spec' => 'Lenovo G40 Intel Core i5-5200 22 Ghz SSD 256 GB Ram 4GB Display 14"',
                'sn' => null,
            ],
            [
                'tag' => 'DB/19120043',
                'user' => 'Karudin',
                'prev_user' => 'Anwar Saroso',
                'device' => null,
                'jenis' => 'NB',
                'year' => '2019',
                'status' => 'Broken',
                'spec' => 'Notebook LENOVO IP 130 Core i3 7020 Ram 4GB SSD 512 GB Display 14"',
                'sn' => null,
            ],
            [
                'tag' => 'DB/20120006',
                'user' => 'Feny',
                'device' => null,
                'jenis' => 'NB',
                'year' => '2020',
                'status' => 'Retired',
                'spec' => 'Notebook DELL Inspiron 3481 i3 7020 Ram 4GB SSD 256 Display 14"',
                'sn' => null,
            ],
            [
                'tag' => 'DB/20120009',
                'user' => 'Ima',
                'device' => null,
                'jenis' => 'NB',
                'year' => '2020',
                'status' => 'Retired',
                'spec' => 'Notebook DELL Inspiron 3481 i3 7020 Ram 4GB SSD 256 Display 14"',
                'sn' => null,
            ],
            [
                'tag' => 'DB/20120013',
                'user' => 'Admin (eva)',
                'device' => null,
                'jenis' => 'NB',
                'year' => '2020',
                'status' => 'Broken',
                'spec' => 'Notebook DELL Inspiron 3481 i3 7020 Ram 4GB SSD 256 Display 14"',
                'sn' => null,
            ],
            [
                'tag' => 'DB/20120014',
                'user' => 'Meiri',
                'device' => null,
                'jenis' => 'NB',
                'year' => '2020',
                'status' => 'Broken',
                'spec' => 'Notebook DELL Inspiron 3481 i3 7020 Ram 4GB SSD 256 GB Display 14"',
                'sn' => null,
            ],
            [
                'tag' => 'DB/20120018',
                'user' => 'Rianti',
                'prev_user' => 'Dian',
                'device' => null,
                'jenis' => 'NB',
                'year' => '2020',
                'status' => 'Broken',
                'spec' => 'Notebook DELL Inspiron 3481 i3 7020 Ram 4GB SSD 256 Display 14"',
                'sn' => null,
            ],
            [
                'tag' => 'DB/20120031',
                'user' => 'Muliaty',
                'device' => null,
                'jenis' => 'NB',
                'year' => '2020',
                'status' => 'Broken',
                'spec' => 'Notebook DELL Inspiron 3481 i3 7020 Ram 4GB SSD 256 GB Display 14"',
                'sn' => null,
            ],
            [
                'tag' => 'DB/21030057',
                'user' => 'Ade Feri',
                'device' => null,
                'jenis' => 'NB',
                'year' => '2021',
                'status' => 'Broken',
                'spec' => 'Notebook DELL Vostro 3410 i3 i3-10110U Ram 4GB SSD 256 Display 14"',
                'sn' => null,
            ],
            [
                'tag' => 'DB/21100121',
                'user' => 'Dzul Khair',
                'prev_user' => 'Rifki Arifianto',
                'device' => null,
                'jenis' => 'NB',
                'year' => '2021',
                'status' => 'Broken',
                'spec' => 'Notebook DELL Vostro 3410 i3 i3-1115G4 Ram 16GB SSD 512 Display 14"',
                'sn' => null,
            ],
            [
                'tag' => 'DB/22040008',
                'user' => 'Rara Ajeng Tiara',
                'device' => null,
                'jenis' => 'NB',
                'year' => '2022',
                'status' => 'Broken',
                'spec' => 'Notebook DELL Vostro 3400 i3 i3-1115G4 Ram 4GB SSD 512 Display 14"',
                'sn' => null,
            ],
            [
                'tag' => 'DB/NB/180400047',
                'user' => 'Hidayat',
                'device' => null,
                'jenis' => 'NB',
                'year' => '2018',
                'status' => 'Retired',
                'spec' => 'Lenovo V310 – 2RID - Processor Core i3 6006SSD 512 GBRam 4GBLed 14Dvd RwWebcam Wifi .Dos',
                'sn' => null,
            ],
            [
                'tag' => 'DB/NB/190400002',
                'user' => 'Irwansyah',
                'prev_user' => 'F.X. Yoshua',
                'device' => null,
                'jenis' => 'NB',
                'year' => '2019',
                'status' => 'Broken',
                'spec' => 'Asus A407UA Core i3-7020U RAM 16GB SSD 256GB LED 14"',
                'sn' => null,
            ],
            [
                'tag' => 'DB/NB/190400003',
                'user' => 'Meytha',
                'prev_user' => 'Randi',
                'device' => null,
                'jenis' => 'NB',
                'year' => '2019',
                'status' => 'Broken',
                'spec' => 'Asus A407UA Core i3-7020U RAM 16GB SSD 512GB LED 14"',
                'sn' => null,
            ],
            [
                'tag' => 'DB/NB/201201006',
                'user' => 'Nindu',
                'prev_user' => 'Handoyo S.',
                'device' => null,
                'jenis' => 'NB',
                'year' => '2020',
                'status' => 'Broken',
                'spec' => 'Notebook DELL Inspiron 3481 i3 7020 Ram 4GB SSD 512 GB Display 14"',
                'sn' => null,
            ],
            [
                'tag' => 'DB/NB/23030017',
                'user' => 'Mulyadi',
                'device' => null,
                'jenis' => 'NB',
                'year' => '2023',
                'status' => 'Broken',
                'spec' => 'Lenovo K14 i3-1115G4 RAM 8GB SSD 14"',
                'sn' => 'PF-3D5FX8',
            ],
            [
                'tag' => 'DB/PR/220101/099',
                'user' => 'Sales',
                'device' => 'Printer G3010 Sales',
                'jenis' => 'PR',
                'year' => '2022',
                'status' => 'Broken',
                'spec' => 'Canon Pixma G3010 Printer',
                'sn' => 'G3010',
            ],
            [
                'tag' => 'DB/20120033',
                'user' => 'Cristine',
                'prev_user' => 'Rony',
                'device' => null,
                'jenis' => 'NB',
                'year' => '2020',
                'status' => 'Broken',
                'spec' => 'Notebook DELL Inspiron 3481 i3 7020 Ram 4GB SSD 1 TB Display 14"',
                'sn' => null,
            ],
        ];

        $itMasterCat = MasterCategory::where('name', 'IT Asset')->first();
        $company = Company::where('code', 'DB')->first() ?? Company::firstOrCreate(['name' => 'PT Diastika Biotekindo'], ['code' => 'DB']);

        $readyStatus = Status::where('name', 'Ready to Deploy')->first();
        $brokenStatus = Status::where('name', 'Broken')->first();
        $retiredStatus = Status::where('name', 'Retired')->first();

        $nbCategory = Category::firstOrCreate(['name' => 'Notebook'], ['master_category_id' => $itMasterCat?->id, 'code' => 'NB']);
        $prCategory = Category::firstOrCreate(['name' => 'Printer'], ['master_category_id' => $itMasterCat?->id, 'code' => 'PR']);

        $tsDept = Department::firstOrCreate(['name' => 'Technical Support'], ['company_id' => $company->id]);
        $fatDept = Department::firstOrCreate(['name' => 'Finance & Accounting'], ['company_id' => $company->id]);
        $salesDept = Department::firstOrCreate(['name' => 'Sales'], ['company_id' => $company->id]);

        foreach ($rawAssets as $item) {
            $tag = $item['tag'];
            if (Asset::where('asset_tag', $tag)->exists()) {
                continue;
            }

            // Resolve Status
            $statusObj = match ($item['status']) {
                'Stored' => $readyStatus,
                'Broken' => $brokenStatus,
                'Retired' => $retiredStatus,
                default => $readyStatus
            };

            // Resolve Category & Brand from Spec
            $specLower = strtolower($item['spec']);
            $cat = $item['jenis'] === 'PR' ? $prCategory : $nbCategory;

            $brandName = 'Other';
            if (str_contains($specLower, 'dell')) {
                $brandName = 'Dell';
            } elseif (str_contains($specLower, 'lenovo')) {
                $brandName = 'Lenovo';
            } elseif (str_contains($specLower, 'asus')) {
                $brandName = 'ASUS';
            } elseif (str_contains($specLower, 'canon') || $item['jenis'] === 'PR') {
                $brandName = 'Canon';
            }

            $brand = Brand::firstOrCreate(['name' => $brandName]);

            // Model name extraction
            $modelName = trim(explode(' ', $item['spec'])[0] ?? 'IT Hardware');
            if (isset(explode(' ', $item['spec'])[1])) {
                $modelName .= ' '.explode(' ', $item['spec'])[1];
            }
            if (isset(explode(' ', $item['spec'])[2])) {
                $modelName .= ' '.explode(' ', $item['spec'])[2];
            }

            $product = Product::firstOrCreate(
                ['name' => $modelName],
                [
                    'brand_id' => $brand->id,
                    'category_id' => $cat->id,
                    'master_category_id' => $itMasterCat?->id,
                ]
            );

            // Resolve Department
            $dept = null;
            if ($item['device'] === 'TS') {
                $dept = $tsDept;
            } elseif ($item['device'] === 'FAT') {
                $dept = $fatDept;
            } elseif ($item['user'] === 'Sales') {
                $dept = $salesDept;
            }

            // Resolve User
            $assignedUser = null;
            if ($item['user'] && $item['user'] !== 'Sales') {
                $email = strtolower(str_replace([' ', '(', ')', '.'], '', $item['user'])).'@diastika.co.id';
                $assignedUser = User::firstOrCreate(
                    ['email' => $email],
                    [
                        'name' => $item['user'],
                        'password' => Hash::make('password123'),
                        'company_id' => $company->id,
                        'department_id' => $dept?->id,
                    ]
                );
            }

            // Purchase date from year
            $purchaseDate = $item['year'].'-01-01';

            $notes = null;
            if (isset($item['prev_user'])) {
                $notes = 'Previous holder: '.$item['prev_user'];
            }

            Asset::create([
                'asset_tag' => $tag,
                'device_name' => $item['device'] ?? $modelName,
                'product_id' => $product->id,
                'status_id' => $statusObj?->id,
                'department_id' => $dept?->id,
                'assigned_to_user_id' => $assignedUser?->id,
                'purchase_date' => $purchaseDate,
                'specification' => $item['spec'],
                'serial_number' => $item['sn'],
                'notes' => $notes,
            ]);
        }
    }
}
