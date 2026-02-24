<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Unit;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            ['name' => 'Drum',   'abbreviation' => 'DR'],
            ['name' => 'Pail',   'abbreviation' => 'PL'],
            ['name' => 'Gallon', 'abbreviation' => 'GAL'],
            ['name' => 'Liter',  'abbreviation' => 'L'],
            ['name' => 'Bottle', 'abbreviation' => 'BTL'],
            ['name' => 'Kilo',   'abbreviation' => 'KG'],
            ['name' => 'Piece',  'abbreviation' => 'PC'],
            ['name' => 'Pair',   'abbreviation' => 'PR'],
            ['name' => 'Roll',   'abbreviation' => 'RL'],
            ['name' => 'Pad',    'abbreviation' => 'PAD'],
            ['name' => 'Sack',   'abbreviation' => 'SCK'],
        ];

        foreach ($units as $unit) {
            Unit::firstOrCreate($unit);
        }
    }
}
