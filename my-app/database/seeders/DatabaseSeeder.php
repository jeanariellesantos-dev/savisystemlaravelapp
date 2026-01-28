<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

    $users = [
[
            'firstname' => 'Jean Arielle',
            'lastname' => 'Santos',
            'role' => 'OPERATION',
            'mobile' => '+639165097848',
            'email' => 'operation@example.com',
        ],
        [
            'firstname' => 'Juan ',
            'lastname' => 'dela Cruz',
            'role' => 'ACCOUNTING',
            'mobile' => '+639165097848',
            'email' => 'accounting@example.com',
        ],
        [
            'firstname' => 'Ychy',
            'lastname' => 'Katigbak',
            'role' => 'SUPERVISOR',
            'mobile' => '+639165097848',
            'email' => 'supervisor@example.com',
        ],
        [
            'firstname' => 'Kessiah',
            'lastname' => 'Katigbak',
            'role' => 'INVENTORY',
            'mobile' => '+639165097848',
            'email' => 'inventory@example.com',
        ]

    ];

        // User::factory()->create([
        //     'firstname' => 'Jean Arielle',
        //     'lastname' => 'Santos',
        //     'role' => 'OPERATION',
        //     'mobile' => '+639165097848',
        //     'email' => 'operation@example.com',
        // ]);

        foreach ($users as $user) {
            User::create($user);
        }
    }
}
