<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $chemicals = Category::where('slug', 'chemicals')->first();
        $supplies  = Category::where('slug', 'supplies')->first();

        Product::firstOrCreate(
            [
                'product_name' => 'Rustproofing',
                'category_id' => $chemicals->id,
            ],
            [
                'description' => 'Anti-rust chemical treatment',
                'is_active' => true,
            ]
        );

        Product::firstOrCreate(
            [
                'product_name' => 'Car Soap',
                'category_id' => $supplies->id,
            ],
            [
                'description' => 'Car wash cleaning solution',
                'is_active' => true,
            ]
        );
    }
}
