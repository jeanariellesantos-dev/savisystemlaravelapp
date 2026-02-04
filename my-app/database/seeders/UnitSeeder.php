<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Unit;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            ['name' => 'Drum', 'abbreviation' => 'DR'],
            ['name' => 'Pail', 'abbreviation' => 'PL'],
            ['name' => 'Gallon', 'abbreviation' => 'GAL'],
            ['name' => 'Bottle', 'abbreviation' => 'BTL'],
            ['name' => 'Piece', 'abbreviation' => 'PC'],
        ];

        foreach ($units as $unit) {
            Unit::firstOrCreate($unit);
        }
    }
}
