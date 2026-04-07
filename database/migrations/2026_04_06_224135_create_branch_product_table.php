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
        Schema::create('branch_product', function (Blueprint $table) {
            $table->id();

            // 🔥 FOREIGN KEYS
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();

            // 📦 STOCK PER BRANCH
            $table->integer('stock')->default(0);

            $table->timestamps();

            // ✅ OPTIONAL (recommended - no duplicate entries)
            $table->unique(['product_id', 'branch_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branch_product');
    }
};