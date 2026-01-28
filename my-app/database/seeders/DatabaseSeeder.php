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
    //Users
    $users = [
        [       
            'firstname' => 'Super',
            'lastname' => 'Admin',
            'password' => 'pass123',
            'role' => 'ADMINISTRATOR',
            'mobile' => '+639165097848',
            'email' => 'admin@example.com',
        ],
        [       
            'firstname' => 'Jean Arielle',
            'lastname' => 'Santos',
            'password' => 'pass123',
            'role' => 'OPERATION',
            'mobile' => '+639165097848',
            'email' => 'operation@example.com',
        ],
        [
            'firstname' => 'Juan ',
            'lastname' => 'dela Cruz',
            'password' => 'pass123',
            'role' => 'ACCOUNTING',
            'mobile' => '+639165097848',
            'email' => 'accounting@example.com',
        ],
        [
            'firstname' => 'Ychy',
            'lastname' => 'Katigbak',
            'password' => 'pass123',
            'role' => 'SUPERVISOR',
            'mobile' => '+639165097848',
            'email' => 'supervisor@example.com',
        ],
        [
            'firstname' => 'Kessiah',
            'lastname' => 'Katigbak',
            'password' => 'pass123',
            'role' => 'INVENTORY',
            'mobile' => '+639165097848',
            'email' => 'inventory@example.com',
        ]

    ];
        foreach ($users as $user) {
            User::create($user);
        }

        $this->call([
            ProductSeeder::class,
            RequestSeeder::class,
            RequestItemSeeder::class,
        ]);

    }
}
