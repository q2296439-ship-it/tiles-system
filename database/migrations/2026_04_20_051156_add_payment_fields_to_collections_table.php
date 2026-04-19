<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('collections', function (Blueprint $table) {

            $table->string('business_style')->nullable()->after('customer_name');

            $table->string('sales_type')
                ->default('cash')
                ->after('terms');

            $table->decimal('paid_amount', 12, 2)
                ->default(0)
                ->after('total_amount');

            $table->decimal('balance', 12, 2)
                ->default(0)
                ->after('paid_amount');

        });
    }

    public function down(): void
    {
        Schema::table('collections', function (Blueprint $table) {

            $table->dropColumn([
                'business_style',
                'sales_type',
                'paid_amount',
                'balance'
            ]);

        });
    }
};