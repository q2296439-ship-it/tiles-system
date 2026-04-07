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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();

            // 🔥 FIX: gawin muna simple (no foreign constraint)
            $table->unsignedBigInteger('user_id')->nullable();

            // 🔥 KEEP THIS (ok lang to)
            $table->foreignId('branch_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->decimal('total_amount', 10, 2);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};