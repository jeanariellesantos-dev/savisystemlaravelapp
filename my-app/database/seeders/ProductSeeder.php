<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Str;
class ProductSeeder extends Seeder
{
    public function run(): void
    {

        // -------------------------
        // PRODUCTS PER CATEGORY
        // -------------------------
        $map = [
            'Rustproofing I' => [
                'UBC',
            ],

            'Common Materials / Detailing' => [
                'Luster Plus (Pure)',
                'Luster Plus (Diluted)',
                'All Purpose (Pure)',
                'All Purpose (Diluted)',
                'Kerosene',
                'Rubbing Compound',
                'SAVI Pro',
            ],

            'Limited Disinfection' => [
                'Disinfectant (Pure)',
                'Disinfectant (Diluted)',
                'Disinfection Card',
            ],

            'Washing' => [
                'Foam Wash Shampoo',
                'Cool Rev (Pure)',
                'Cool Rev (Diluted)',
            ],

            'Rustproofing II' => [
                'Cavity Wax',
                'Packing Tape - Clear',
                'Plastic Cover',
                'Warranty Card',
                'Warranty Insert Booklet',
                'Warranty Sticker',
            ],


            'Detailing' => [
                'Deodorizer',
                'Polishing Glaze',
                'Liquid Wax',
                'Quick Detailer',
            ],

            'Supplies - Detailing' => [
                'Applicator Pad',
                'Backer Pad',
                'Buffing Cloth',
                'Foam Cutting Pad',
                'Foam Polishing Pad',
                'Hi-Performance Cloth',
                'Kanebo',
                'Masking Tape',
                'Odor Neutralizer',
                'Over Spray Clay',
                'Plastic Dispencer',
                'Rags',
                'Spray Bottle',
                'Toothbrush',
                'Washmitt',
                'Paint Brush #2',
            ],


            'Office Supplies' => [
                'Ball Pen',
                'Whiteboard Marker',
                'Stabilo',
                'Pentel Pen',
                'AR - Double',
                'A4 - Triple',
                'Daily Production Report',
                'Weekly Production Report',
                'Service Invoice',
                'Individual Activity Report',
            ],

            'Vacuum' => [
                'Carbon Brush RV18/CE 1020',
                'Crevise Nozzle',
                'Head Main Body Base',
                'Hose for Vaccum',
                'Impeller',
            ],

            'Uniform Supplies' => [
                'Arm Sleeves',
                'Gas Mask',
                'Bonnet',
                'Cotton Gloves',
                'Googles',
            ],

            'Power Wash' => [
                'Fan Belt - 38',
                'Fan Belt - 40',
                'Hydraulic Hose - 40FT',
                'Valve Assembly',
                'Valve Flat',
                'Valve Seal - Big',
                'Valve Seal - Small',
                'V-Packing',
            ],

            'Foam Wash Machine' => [
                'Foam Wash Hose - 40FT',
                'Ball Valve 1/2',
            ],

            'Buffing Machine' => [
                'Carbon Brush 100/101',
            ],

            'Low Pressure Gun' => [
                'Hose For Low Pressure Gum',
                'Repair Kit (Set)',
            ],

            'Graco Pump' => [
                'Rubber Washer Coupler',
                'Rubber Washer Z-Swivel',
                'Metal Filter',
                'V-Packing (Graco)',
                'Steam Valve',
                'Poppet Valve',
            ],
        ];

        foreach ($map as $categoryName => $products) {
            $category = Category::where('slug', Str::slug($categoryName))->firstOrFail();

            foreach ($products as $productName) {
                Product::firstOrCreate(
                    [
                        'product_name' => $productName,
                        'category_id' => $category->id,
                    ],
                    [
                        'description' => $productName,
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
