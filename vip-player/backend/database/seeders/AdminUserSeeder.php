<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@vipplayer.app'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('admin12345'),
                'role' => User::ROLE_ADMIN,
                'is_active' => true,
            ],
        );
    }
}
