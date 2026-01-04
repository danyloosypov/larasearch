<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        // Get unique brand names from products table
        $brands = Product::query()
            ->whereNotNull('brand')
            ->where('brand', '!=', '')
            ->select('brand')
            ->distinct()
            ->pluck('brand');

        DB::transaction(function () use ($brands) {
            foreach ($brands as $brandName) {
                $brand = Brand::firstOrCreate([
                    'name' => trim($brandName),
                ]);

                // Assign brand_id to products
                Product::where('brand', $brandName)
                    ->update(['brand_id' => $brand->id]);
            }
        });
    }
}
