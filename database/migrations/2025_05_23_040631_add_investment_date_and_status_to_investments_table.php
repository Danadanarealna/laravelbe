<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('investments', function (Blueprint $table) {
            if (!Schema::hasColumn('investments', 'investment_date')) {
                $table->timestamp('investment_date')->useCurrent()->after('amount');
            }
            // The 'status' column was in your original create_investment migration,
            // but if it's missing for some reason, you can add it here too.
            // if (!Schema::hasColumn('investments', 'status')) {
            //     $table->string('status')->default('pending')->after('investment_date');
            // }
        });
    }

    public function down(): void
    {
        Schema::table('investments', function (Blueprint $table) {
            if (Schema::hasColumn('investments', 'investment_date')) {
                $table->dropColumn('investment_date');
            }
            // if (Schema::hasColumn('investments', 'status')) {
            //     $table->dropColumn('status');
            // }
        });
    }
};