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
        $users = [
            [       
                'employee_number' => 'EMP-001',
                'firstname' => 'Super',
                'lastname' => 'Admin',
                'password' => 'pass1234',
                'role_id' => '1',
                'dealership_id'=> '1',
                'mobile' => '+639165097848',
                'email' => 'admin@example.com',
            ],
            [       
                'employee_number' => 'EMP-002',
                'firstname' => 'OPERATIONS',
                'lastname' => 'OPERATIONS',
                'password' => 'pass1234',
                'role_id' => '2', // OPERATIONS
                'dealership_id'=> '1',
                'mobile' => '+639165097848',
                'email' => 'operation@example.com',
            ],
            [
                'employee_number' => 'EMP-003',
                'firstname' => 'ACCOUNTING',
                'lastname' => 'ACCOUNTING',
                'password' => 'pass1234',
                'role_id' => '3', // ACCOUNTING
                'dealership_id'=> '1',
                'mobile' => '+639165097848',
                'email' => 'accounting@example.com',
            ],
            [
                'employee_number' => 'EMP-004',
                'firstname' => 'SUPERVISOR',
                'lastname' => 'SUPERVISOR',
                'password' => 'pass1234',
                'role_id' => '4', // SUPERVISOR
                'dealership_id'=> '1',
                'mobile' => '+639165097848',
                'email' => 'supervisor@example.com',
            ],
            [
                'employee_number' => 'EMP-005', 
                'firstname' => 'CLUSTER_HEAD',
                'lastname' => 'CLUSTER_HEAD',
                'password' => 'pass1234',
                'role_id' => '5', // CLUSTER_HEAD
                'dealership_id'=> '1',
                'mobile' => '+639165097848',
                'email' => 'clusterhead@example.com',
            ],
            [
                'employee_number' => 'EMP-006',
                'firstname' => 'OPERATIONS',
                'lastname' => 'OPERATIONS',
                'password' => 'pass1234',
                'role_id' => '6', // INVENTORY
                'dealership_id'=> '1',
                'mobile' => '+639165097848',
                'email' => 'inventory@example.com',
            ],
            [
                'employee_number' => 'EMP-OPS',
                'firstname' => 'SAMPLE',
                'lastname' => 'OPERATIONS',
                'password' => 'pass1234',
                'role_id' => '2', // OPERATIONS
                'dealership_id'=> '1',
                'mobile' => '+639165097848',
                'email' => 'sampleops@example.com',
            ]

        ];
            foreach ($users as $user) {
                User::create($user);
            }
    }
}
