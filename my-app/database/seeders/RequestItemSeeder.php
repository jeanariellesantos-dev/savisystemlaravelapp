<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Request;
use App\Models\RequestItem;
use App\Models\Unit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RequestItemSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            $request1 = Request::where('request_id', 'REQ2026022111011043')->firstOrFail();
            $request2 = Request::where('request_id', 'REQ2026022111101322')->firstOrFail();

            // Products
            $rustproofing = Product::where('product_name', 'UBC')->firstOrFail();
            $kerosene      = Product::where('product_name', 'Kerosene')->firstOrFail();

            // Units (via pivot default)
            $rustproofingUnit = $rustproofing->units()
                ->wherePivot('is_default', true)
                ->firstOrFail();

            $keroseneUnit = $kerosene->units()
                ->wherePivot('is_default', true)
                ->firstOrFail();

            // Request 1 - Rustproofing
            RequestItem::create([
                'request_id' => $request1->id,
                'product_id' => $rustproofing->id,
                'unit_id'    => $rustproofingUnit->id,
                'quantity'   => 2,
                'starting_balance' => 5,
                'ending_balance' => 1,
            ]);

            // Request 1 - Car Soap
            RequestItem::create([
                'request_id' => $request1->id,
                'product_id' => $kerosene->id,
                'unit_id'    => $keroseneUnit->id,
                'quantity'   => 5,
                'starting_balance' => 4,
                'ending_balance' => 2,
            ]);

            // Request 2 - Rustproofing
            RequestItem::create([
                'request_id' => $request2->id,
                'product_id' => $rustproofing->id,
                'unit_id'    => $rustproofingUnit->id,
                'quantity'   => 1,
                'starting_balance' => 4,
                 'ending_balance' => 5,
            ]);
        });
    }
}
