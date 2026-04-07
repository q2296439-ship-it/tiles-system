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
        Schema::table('products', function (Blueprint $table) {

            // ✅ SKU
            if (!Schema::hasColumn('products', 'sku')) {
                $table->string('sku')->nullable();
            }

            // ✅ CATEGORY
            if (!Schema::hasColumn('products', 'category')) {
                $table->string('category')->nullable();
            }

            // ✅ LOW STOCK THRESHOLD
            if (!Schema::hasColumn('products', 'low_stock_threshold')) {
                $table->integer('low_stock_threshold')->default(10);
            }

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {

            if (Schema::hasColumn('products', 'sku')) {
                $table->dropColumn('sku');
            }

            if (Schema::hasColumn('products', 'category')) {
                $table->dropColumn('category');
            }

            if (Schema::hasColumn('products', 'low_stock_threshold')) {
                $table->dropColumn('low_stock_threshold');
            }

        });
    }
};