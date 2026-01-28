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
                'request_id' => 'REQ-001',
                'requestor_id' => "2",
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'request_id' => 'REQ-002',
                'requestor_id' => "2",
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}

