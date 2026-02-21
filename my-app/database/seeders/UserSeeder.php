<?php

namespace Database\Seeders;
use App\Models\User;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
            //Users
    $users = [
        [       
            'firstname' => 'Super',
            'lastname' => 'Admin',
            'password' => 'pass123',
            'role_id' => '1',
            'dealership_id'=> '1',
            'mobile' => '+639165097848',
            'email' => 'admin@example.com',
        ],
        [       
            'firstname' => 'Jean Arielle',
            'lastname' => 'Santos',
            'password' => 'pass123',
            'role_id' => '2', // OPERATIONS
            'dealership_id'=> '1',
            'mobile' => '+639165097848',
            'email' => 'operation@example.com',
        ],
        [
            'firstname' => 'Juan ',
            'lastname' => 'dela Cruz',
            'password' => 'pass123',
            'role_id' => '3', // ACCOUNTING
            'dealership_id'=> '1',
            'mobile' => '+639165097848',
            'email' => 'accounting@example.com',
        ],
        [
            'firstname' => 'Ychy',
            'lastname' => 'Katigbak',
            'password' => 'pass123',
            'role_id' => '4', // SUPERVISOR
            'dealership_id'=> '1',
            'mobile' => '+639165097848',
            'email' => 'supervisor@example.com',
        ],
        [
            'firstname' => 'Kate',
            'lastname' => 'Katigbak',
            'password' => 'pass123',
            'role_id' => '5', // CLUSTER_HEAD
            'dealership_id'=> '1',
            'mobile' => '+639165097848',
            'email' => 'clusterhead@example.com',
        ],
        [
            'firstname' => 'Kessiah',
            'lastname' => 'Katigbak',
            'password' => 'pass123',
            'role_id' => '6', // INVENTORY
            'dealership_id'=> '1',
            'mobile' => '+639165097848',
            'email' => 'inventory@example.com',
        ]

    ];
        foreach ($users as $user) {
            User::create($user);
        }
    }
}
