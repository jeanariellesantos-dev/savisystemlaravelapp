<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        
        // -------------------------
        // CATEGORIES
        // -------------------------
        $categories = [
            'Rustproofing I',
            'Common Materials / Detailing',
            'Limited Disinfection',
            'Washing',
            'Rustproofing II',
            'Detailing',
            'Supplies - Detailing',
            'Office Supplies',
            'Vacuum',
            'Uniform Supplies',
            'Power Wash',
            'Foam Wash Machine',
            'Buffing Machine',
            'Low Pressure Gun',
            'Graco Pump',
        ];

        foreach ($categories as $name) {
            Category::firstOrCreate([
                'name' => $name,
                'slug' => Str::slug($name),
            ]);
        }
    }
}
