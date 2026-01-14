<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {

            // Phone
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone', 15)
                    ->nullable()
                    ->after('email');
            }

            // Avatar
            if (!Schema::hasColumn('users', 'avatar')) {
                $table->string('avatar')
                    ->nullable()
                    ->after('phone');
            }

            // Role
            if (!Schema::hasColumn('users', 'role')) {
                $table->enum('role', ['admin', 'customer'])
                    ->default('customer')
                    ->after('password');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'avatar',
                'role'
            ]);
        });
    }
};