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

            $request1 = Request::where('request_id', 'REQ-001')->firstOrFail();
            $request2 = Request::where('request_id', 'REQ-002')->firstOrFail();

            // Products
            $rustproofing = Product::where('product_name', 'Rustproofing')->firstOrFail();
            $carSoap      = Product::where('product_name', 'Car Soap')->firstOrFail();

            // Units (via pivot default)
            $rustproofingUnit = $rustproofing->units()
                ->wherePivot('is_default', true)
                ->firstOrFail();

            $carSoapUnit = $carSoap->units()
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
                'product_id' => $carSoap->id,
                'unit_id'    => $carSoapUnit->id,
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
