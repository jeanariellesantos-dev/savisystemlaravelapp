<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Request;
use App\Models\RequestItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RequestItemSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            $request1 = Request::where('request_id', 'REQ-001')->first();
            $request2 = Request::where('request_id', 'REQ-002')->first();

            $laptop = Product::where('product_name', 'Laptop')->first();
            $mouse = Product::where('product_name', 'Mouse')->first();

            // Request 1 - Laptop
            RequestItem::create([
                'request_id' => $request1->id,
                'product_id' => $laptop->id,
                'quantity' => 5,
                'starting_balance' => $laptop->quantity,
                'ending_balance' => $laptop->quantity - 5,
            ]);

            $laptop->decrement('quantity', 5);

            // Request 1 - Mouse
            RequestItem::create([
                'request_id' => $request1->id,
                'product_id' => $mouse->id,
                'quantity' => 10,
                'starting_balance' => $mouse->quantity,
                'ending_balance' => $mouse->quantity - 10,
            ]);

            $mouse->decrement('quantity', 10);

            // Request 2 - Laptop
            RequestItem::create([
                'request_id' => $request2->id,
                'product_id' => $laptop->id,
                'quantity' => 3,
                'starting_balance' => $laptop->quantity,
                'ending_balance' => $laptop->quantity - 3,
            ]);

            $laptop->decrement('quantity', 3);
        });
    }
}

