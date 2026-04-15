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
        Schema::table('collections', function (Blueprint $table) {

            $table->decimal('gross_amount', 12, 2)->default(0)->after('terms');
            $table->string('discount_type')->nullable()->after('gross_amount');
            $table->decimal('discount_amount', 12, 2)->default(0)->after('discount_type');
            $table->decimal('net_amount', 12, 2)->default(0)->after('discount_amount');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('collections', function (Blueprint $table) {

            $table->dropColumn([
                'gross_amount',
                'discount_type',
                'discount_amount',
                'net_amount'
            ]);

        });
    }
};