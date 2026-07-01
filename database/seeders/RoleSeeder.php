<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'admin', 'description' => 'Administrator with full access'],
            ['name' => 'manager', 'description' => 'Manager with product and sales access'],
            ['name' => 'cashier', 'description' => 'Cashier with sales access only'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role['name']], $role);
        }
    }
}
