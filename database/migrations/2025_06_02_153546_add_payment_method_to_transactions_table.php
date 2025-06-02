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
            // Add the new payment_method column
            // It can be nullable if not all transactions will have a specific payment method,
            // or you can set a default.
            $table->string('payment_method')->nullable()->after('type');

            // Optionally, you might want to modify the existing 'type' column
            // if you plan to restrict its values more strictly (e.g., only 'Income', 'Expense').
            // This example assumes 'type' will now store 'Income', 'Expense', 'DebtIncurred'.
            // If you were previously storing 'Cash' or 'Credit' in 'type' to signify payment method,
            // you might want to run a data migration script here to move those values
            // to the new 'payment_method' column and update 'type' accordingly.
            // For now, we'll just add the new column.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (Schema::hasColumn('transactions', 'payment_method')) {
                $table->dropColumn('payment_method');
            }
        });
    }
};
