<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Unit;

class ProductUnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = Unit::all()->keyBy('name');

        $productUnits = [

            // ---------------- RUSTPROOFING I ----------------
            'UBC' => ['Drum', 'Pail', 'Gallon'],

            // ---------------- COMMON MATERIALS / DETAILING ----------------
            'Luster Plus (Pure)'     => ['Gallon', 'Kilo', 'Bottle'],
            'Luster Plus (Diluted)'  => ['Gallon', 'Kilo', 'Bottle'],
            'All Purpose (Pure)'     => ['Gallon', 'Kilo', 'Bottle'],
            'All Purpose (Diluted)'  => ['Gallon', 'Kilo', 'Bottle'],
            'Kerosene'               => ['Gallon', 'Kilo', 'Bottle'],
            'Rubbing Compound'       => ['Gallon', 'Kilo', 'Bottle'],
            'SAVI Pro'               => ['Gallon', 'Kilo', 'Bottle'],

            // ---------------- LIMITED DISINFECTION ----------------
            'Disinfectant (Pure)'    => ['Gallon', 'Liter', 'Piece'],
            'Disinfectant (Diluted)' => ['Gallon', 'Liter', 'Piece'],
            'Disinfection Card'      => ['Gallon', 'Liter', 'Piece'],

            // ---------------- WASHING ----------------
            'Foam Wash Shampoo'      => ['Gallon', 'Liter'],
            'Cool Rev (Pure)'        => ['Gallon', 'Liter'],
            'Cool Rev (Diluted)'     => ['Gallon', 'Liter'],

            // ---------------- RUSTPROOFING II ----------------
            'Cavity Wax'                 => ['Piece', 'Roll', 'Pail'],
            'Packing Tape - Clear'       => ['Piece', 'Roll', 'Pail'],
            'Plastic Cover'              => ['Piece', 'Roll', 'Pail'],
            'Warranty Card'              => ['Piece', 'Roll', 'Pail'],
            'Warranty Insert Booklet'    => ['Piece', 'Roll', 'Pail'],
            'Warranty Sticker'           => ['Piece', 'Roll', 'Pail'],

            // ---------------- DETAILING ----------------
            'Deodorizer'            => ['Liter'],
            'Polishing Glaze'       => ['Liter'],
            'Liquid Wax'            => ['Liter'],
            'Quick Detailer'        => ['Liter'],

            // ---------------- SUPPLIES - DETAILING ----------------
            'Applicator Pad'        => ['Piece', 'Kilo'],
            'Backer Pad'            => ['Piece', 'Kilo'],
            'Buffing Cloth'         => ['Piece', 'Kilo'],
            'Foam Cutting Pad'      => ['Piece', 'Kilo'],
            'Foam Polishing Pad'    => ['Piece', 'Kilo'],
            'Hi-Performance Cloth'  => ['Piece', 'Kilo'],
            'Kanebo'                => ['Piece', 'Kilo'],
            'Masking Tape'          => ['Piece', 'Kilo'],
            'Odor Neutralizer'      => ['Piece', 'Kilo'],
            'Over Spray Clay'       => ['Piece', 'Kilo'],
            'Plastic Dispencer'     => ['Piece', 'Kilo'],
            'Rags'                  => ['Piece', 'Kilo'],
            'Spray Bottle'          => ['Piece', 'Kilo'],
            'Toothbrush'            => ['Piece', 'Kilo'],
            'Washmitt'              => ['Piece', 'Kilo'],
            'Paint Brush #2'        => ['Piece', 'Kilo'],

            // ---------------- OFFICE SUPPLIES ----------------
            'Ball Pen'                   => ['Piece', 'Pad'],
            'Whiteboard Marker'          => ['Piece', 'Pad'],
            'Stabilo'                    => ['Piece', 'Pad'],
            'Pentel Pen'                 => ['Piece', 'Pad'],
            'AR - Double'                => ['Piece', 'Pad'],
            'AR - Triple'                => ['Piece', 'Pad'],
            'Daily Production Report'    => ['Piece', 'Pad'],
            'Weekly Production Report'   => ['Piece', 'Pad'],
            'Service Invoice'            => ['Piece', 'Pad'],
            'Individual Activity Report' => ['Piece', 'Pad'],

            // ---------------- VACUUM ----------------
            'Carbon Brush RV18/CE 1020'  => ['Piece', 'Pair'],
            'Crevise Nozzle'             => ['Piece', 'Pair'],
            'Head Main Body Base'        => ['Piece', 'Pair'],
            'Hose for Vaccum'            => ['Piece', 'Pair'],
            'Impeller'                   => ['Piece', 'Pair'],

            // ---------------- UNIROM SUPPLIES ----------------
            'Arm Sleeves'   => ['Pair', 'Piece'],
            'Gas Mask'      => ['Pair', 'Piece'],
            'Bonnet'        => ['Pair', 'Piece'],
            'Cotton Gloves' => ['Pair', 'Piece'],
            'Googles'       => ['Pair', 'Piece'],

            // ---------------- POWER WASH ----------------
            'Fan Belt - 38'           => ['Piece', 'Pair'],
            'Fan Belt - 40'           => ['Piece', 'Pair'],
            'Hydraulic Hose - 40FT'   => ['Piece', 'Pair'],
            'Valve Assembly'          => ['Piece', 'Pair'],
            'Valve Flat'              => ['Piece', 'Pair'],
            'Valve Seal - Big'        => ['Piece', 'Pair'],
            'Valve Seal - Small'      => ['Piece', 'Pair'],
            'V-Packing'               => ['Piece', 'Pair'],

            // ---------------- FOAM WASH MACHINE ----------------
            'Foam Wash Hose - 40FT'   => ['Piece', 'Pair'],
            'Ball Valve 1/2'          => ['Piece', 'Pair'],

            // ---------------- BUFFING MACHINE ----------------
            'Carbon Brush 100/101'    => ['Piece', 'Pair'],

            // ---------------- LOW PRESSURE GUN ----------------
            'Hose For Low Pressure Gun' => ['Piece', 'Pair'],
            'Repair Kit (Set)'          => ['Piece', 'Pair'],

            // ---------------- GRACO ----------------
            'Rubber Washer Coupler'   => ['Piece', 'Pair'],
            'Rubber Washer Z-Swivel'  => ['Piece', 'Pair'],
            'Metal Filter'            => ['Piece', 'Pair'],
            'V-Packing (Graco)'       => ['Piece', 'Pair'],
            'Steam Valve'             => ['Piece', 'Pair'],
            'Poppet Valve'            => ['Piece', 'Pair'],
        ];

        foreach ($productUnits as $productName => $unitNames) {
            $product = Product::where('product_name', $productName)->first();

            if (!$product) {
                continue;
            }

            $sync = collect($unitNames)
                ->mapWithKeys(function ($unitName, $index) use ($units) {
                    if (!isset($units[$unitName])) {
                        return [];
                    }

                    return [
                        $units[$unitName]->id => [
                            'is_default' => $index === 0,
                        ],
                    ];
                })
                ->toArray();

            $product->units()->sync($sync);
        }
    }
}
