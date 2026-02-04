<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Unit;

class ProductUnitSeeder extends Seeder
{
    public function run(): void
    {
        $rustproofing = Product::where('product_name', 'Rustproofing')->firstOrFail();
        $carSoap      = Product::where('product_name', 'Car Soap')->firstOrFail();

        $units = Unit::all()->keyBy('name');

        // Rustproofing units
        $rustproofing->units()->sync([
            $units['Drum']->id => ['is_default' => true],
            $units['Pail']->id => ['is_default' => false],
            $units['Gallon']->id => ['is_default' => false],
        ]);

        // Car Soap units
        $carSoap->units()->sync([
            $units['Bottle']->id => ['is_default' => true],
        ]);
    }
}
