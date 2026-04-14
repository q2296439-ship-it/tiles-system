<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // PostgreSQL safe fix
        DB::statement("
            ALTER TABLE stock_movements
            ALTER COLUMN type TYPE VARCHAR(255)
        ");

        DB::statement("
            ALTER TABLE stock_movements
            ALTER COLUMN type DROP DEFAULT
        ");
    }

    public function down(): void
    {
        // optional rollback
        DB::statement("
            ALTER TABLE stock_movements
            ALTER COLUMN type TYPE VARCHAR(255)
        ");
    }
};