<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\MasterCategory;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $itAsset = MasterCategory::where('name', 'IT Asset')->first();
        $consumable = MasterCategory::where('name', 'Consumable')->first();

        // Brands
        $apple = Brand::where('code', 'APL')->first();
        $dell = Brand::where('code', 'DEL')->first();
        $hp = Brand::where('code', 'HP')->first();
        $lenovo = Brand::where('code', 'LEN')->first();
        $logitech = Brand::where('code', 'LOG')->first();

        // Categories
        $notebook = Category::where('code', 'NB')->first();
        $pc = Category::where('code', 'PC')->first();
        $monitor = Category::where('code', 'MN')->first();
        $printer = Category::where('code', 'PR')->first();
        $mouse = Category::where('code', 'MSE')->first();
        $keyboard = Category::where('code', 'KBD')->first();
        $toner = Category::where('code', 'TON')->first();

        $products = [
            // IT Assets
            [
                'name' => 'MacBook Pro 14"',
                'product_type' => 'A2941',
                'brand_id' => $apple?->id,
                'category_id' => $notebook?->id,
                'master_category_id' => $itAsset?->id,
                'description' => 'Apple MacBook Pro 14-inch with M3 chip, 16GB RAM, 512GB SSD',
            ],
            [
                'name' => 'Dell Latitude 5420',
                'product_type' => 'L5420',
                'brand_id' => $dell?->id,
                'category_id' => $notebook?->id,
                'master_category_id' => $itAsset?->id,
                'description' => 'Dell Latitude 5420 Laptop, Intel Core i5, 8GB RAM, 256GB SSD',
            ],
            [
                'name' => 'HP ProDesk 400 G7',
                'product_type' => 'PD400G7',
                'brand_id' => $hp?->id,
                'category_id' => $pc?->id,
                'master_category_id' => $itAsset?->id,
                'description' => 'HP ProDesk 400 G7 Microtower PC, Intel Core i7, 16GB RAM, 512GB SSD',
            ],
            [
                'name' => 'Lenovo ThinkVision T24i-20',
                'product_type' => 'T24I20',
                'brand_id' => $lenovo?->id,
                'category_id' => $monitor?->id,
                'master_category_id' => $itAsset?->id,
                'description' => 'Lenovo ThinkVision T24i-20 23.8-inch FHD IPS Monitor',
            ],
            [
                'name' => 'HP LaserJet Pro M404dn',
                'product_type' => 'M404DN',
                'brand_id' => $hp?->id,
                'category_id' => $printer?->id,
                'master_category_id' => $itAsset?->id,
                'description' => 'HP LaserJet Pro Monochrome Laser Printer',
            ],
            // Consumables
            [
                'name' => 'Logitech Pebble M350',
                'product_type' => 'M350',
                'brand_id' => $logitech?->id,
                'category_id' => $mouse?->id,
                'master_category_id' => $consumable?->id,
                'description' => 'Logitech Pebble Wireless Mouse',
            ],
            [
                'name' => 'Logitech K380 Bluetooth Keyboard',
                'product_type' => 'K380',
                'brand_id' => $logitech?->id,
                'category_id' => $keyboard?->id,
                'master_category_id' => $consumable?->id,
                'description' => 'Multi-Device Bluetooth Keyboard',
            ],
            [
                'name' => 'HP 85A Black LaserJet Toner Cartridge',
                'product_type' => 'CE285A',
                'brand_id' => $hp?->id,
                'category_id' => $toner?->id,
                'master_category_id' => $consumable?->id,
                'description' => 'HP 85A LaserJet Toner',
            ],
        ];

        foreach ($products as $prod) {
            Product::updateOrCreate(
                ['name' => $prod['name'], 'product_type' => $prod['product_type']],
                [
                    'brand_id' => $prod['brand_id'],
                    'category_id' => $prod['category_id'],
                    'master_category_id' => $prod['master_category_id'],
                    'description' => $prod['description'],
                ]
            );
        }
    }
}
