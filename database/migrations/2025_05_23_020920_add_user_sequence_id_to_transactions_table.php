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
            // Check if the column doesn't already exist to make the migration re-runnable
            if (!Schema::hasColumn('transactions', 'user_sequence_id')) {
                $table->unsignedBigInteger('user_sequence_id')->nullable()->after('user_id');
                // You might want an index for performance if you query this often per user
                // $table->index(['user_id', 'user_sequence_id']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Check if the column exists before trying to drop it
            if (Schema::hasColumn('transactions', 'user_sequence_id')) {
                // If you added an index, drop it first:
                // $table->dropIndex(['user_id', 'user_sequence_id']); // Or $table->dropIndex('transactions_user_id_user_sequence_id_index');
                $table->dropColumn('user_sequence_id');
            }
        });
    }
};
