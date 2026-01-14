<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // ===== ADMIN =====
        User::create([
            'name' => 'Admin ELARA',
            'email' => 'elara.shop26@gmail.com',
            'password' => Hash::make('123456'),
            'phone' => '0900000000',
            'avatar' => null,
            'role' => 1, // admin
        ]);
    }
}