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
        Schema::table('transactions', function (Blueprint $table) {
            // Add the 'notes' column. It should be nullable.
            // You can place it after a specific column if you prefer, e.g., ->after('status')
            if (!Schema::hasColumn('transactions', 'notes')) { // Check if column doesn't exist
                $table->text('notes')->nullable()->after('status'); // Or after another relevant column
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (Schema::hasColumn('transactions', 'notes')) { // Check if column exists before dropping
                $table->dropColumn('notes');
            }
        });
    }
};
