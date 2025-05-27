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
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'umkm_name')) {
                    $table->string('umkm_name')->nullable()->after('name');
                }
                if (!Schema::hasColumn('users', 'umkm_contact')) {
                    // Store phone number for WhatsApp, ensure it can hold international format
                    $table->string('umkm_contact', 30)->nullable()->after('umkm_name');
                }
            });
        }
    
        /**
         * Reverse the migrations.
         */
        public function down(): void
        {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'umkm_name')) {
                    $table->dropColumn('umkm_name');
                }
                if (Schema::hasColumn('users', 'umkm_contact')) {
                    $table->dropColumn('umkm_contact');
                }
            });
        }
    };
    