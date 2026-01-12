<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@elara.com'], // điều kiện tìm
            [
                'name' => 'Admin ELARA',
                'password' => Hash::make('123456'),
                'phone' => '0900000000',
                'role' => 1,
                'email_verified_at' => now(),
            ]
        );
    }
}