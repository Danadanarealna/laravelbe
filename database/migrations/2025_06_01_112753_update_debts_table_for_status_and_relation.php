<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Debt;

return new class extends Migration
{
    public function up()
    {
        Schema::table('debts', function (Blueprint $table) {
            if (!Schema::hasColumn('debts', 'status')) {
                $table->string('status')->default(Debt::STATUS_PENDING_VERIFICATION)->after('amount');
            } else {
                 $table->string('status')->default(Debt::STATUS_PENDING_VERIFICATION)->change();
            }

            if (!Schema::hasColumn('debts', 'related_transaction_id')) {
                $table->foreignId('related_transaction_id')->nullable()->constrained('transactions')->onDelete('set null')->after('status');
            }
        });
    }

    public function down()
    {
        Schema::table('debts', function (Blueprint $table) {
            if (Schema::hasColumn('debts', 'related_transaction_id')) {
                $table->dropConstrainedForeignId('related_transaction_id');
            }
        });
    }
};
