<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_fees', function (Blueprint $table) {
            $table->id();

            $table->string('delivery_no')->unique();
            $table->date('delivery_date');

            $table->string('receipt_no')->nullable();
            $table->string('customer_name')->nullable();
            $table->text('address')->nullable();

            $table->string('rider_name')->nullable();

            $table->decimal('amount', 12, 2)->default(0);

            $table->text('notes')->nullable();

            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('user_id');

            $table->string('status')->default('saved');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_fees');
    }
};