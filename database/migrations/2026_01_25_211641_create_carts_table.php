<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
            ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('variant_id')
            ->constrained('product_variants')
            ->cascadeOnDelete();

            $table->integer('quantity');

            $table->timestamps();

            // 🔒 mỗi user chỉ có 1 dòng cho 1 biến thể
            $table->unique(['user_id', 'variant_id']);
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};