<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'admin@easystock.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password123'),
            ]
        );

        $adminRole = Role::where('name', 'admin')->first();

        if ($adminRole instanceof Role) {
            $user->assignRole($adminRole);
        }
    }
}
