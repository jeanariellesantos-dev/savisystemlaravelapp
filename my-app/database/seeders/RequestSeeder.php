<?php

namespace Database\Seeders;

use App\Models\Request;
use Illuminate\Database\Seeder;

class RequestSeeder extends Seeder
{
    public function run(): void
    {
        Request::insert([
            [
                'request_id' => 'REQ2026022111011043',
                'requestor_id' => "7",
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'request_id' => 'REQ2026022111101322',
                'requestor_id' => "7",
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}

