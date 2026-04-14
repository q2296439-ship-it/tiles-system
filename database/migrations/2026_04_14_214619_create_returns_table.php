<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('returns', function (Blueprint $table) {
            $table->id();
            $table->string('return_no')->unique();
            $table->string('receipt_no')->nullable();
            $table->date('return_date');
            $table->string('customer_name')->nullable();
            $table->text('reason');
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('returns');
    }
};