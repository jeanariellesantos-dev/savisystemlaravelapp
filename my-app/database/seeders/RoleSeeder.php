<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::insert([
            [
                'role_name' => 'ADMINISTRATOR',
                'role_description' => 'Administrator / Super Admin',
            ],
            [
                'role_name' => 'OPERATION',
                'role_description' => 'Lead Man Operations',
            ],
            [
                'role_name' => 'ACCOUNTING',
                'role_description' => 'Head of Accounting',
            ],
            [
                'role_name' => 'SUPERVISOR',
                'role_description' => 'Supervisor',
            ],
            [
                'role_name' => 'INVENTORY',
                'role_description' => 'Inventory Staff',
            ],
        ]);
    }
}
