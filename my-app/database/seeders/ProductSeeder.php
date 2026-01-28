<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        Product::insert([
            [
                'product_name' => 'Laptop',
                'quantity' => 100,
                'unit_of_measure' => 'pcs',
                'is_active' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'product_name' => 'Mouse',
                'quantity' => 200,
                'unit_of_measure' => 'pcs',
                'is_active' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'product_name' => 'Keyboard',
                'quantity' => 150,
                'is_active' => '1',
                'unit_of_measure' => 'pcs',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}

