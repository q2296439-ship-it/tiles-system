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
        Schema::create('deposits', function (Blueprint $table) {
            $table->id();

            $table->date('deposit_date');

            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('user_id');

            $table->decimal('expected_amount', 12, 2)->default(0);
            $table->decimal('actual_amount', 12, 2)->default(0);
            $table->decimal('variance', 12, 2)->default(0);

            $table->integer('denom_1000')->default(0);
            $table->integer('denom_500')->default(0);
            $table->integer('denom_200')->default(0);
            $table->integer('denom_100')->default(0);
            $table->integer('denom_50')->default(0);
            $table->integer('denom_20')->default(0);

            $table->integer('coin_10')->default(0);
            $table->integer('coin_5')->default(0);
            $table->integer('coin_1')->default(0);

            $table->text('remarks')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deposits');
    }
};