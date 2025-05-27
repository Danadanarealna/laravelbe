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
            if (!Schema::hasColumn('users', 'is_investable')) {
                $table->boolean('is_investable')->default(false)->after('password'); // Or after another relevant column
            }

            // Standardize umkm_contact to contact if it exists, or add contact if it doesn't
            if (Schema::hasColumn('users', 'umkm_contact') && !Schema::hasColumn('users', 'contact')) {
                $table->renameColumn('umkm_contact', 'contact');
            } elseif (!Schema::hasColumn('users', 'contact')) {
                $table->string('contact')->nullable()->after('umkm_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'is_investable')) {
                $table->dropColumn('is_investable');
            }
            // If you renamed umkm_contact to contact, you might want to rename it back
            // or simply ensure the 'contact' column is dropped if it was newly added.
            // This part depends on your previous schema. For simplicity, if 'contact' was the result of rename:
            if (Schema::hasColumn('users', 'contact') && !Schema::hasColumn('users', 'umkm_contact')) {
                 // $table->renameColumn('contact', 'umkm_contact'); // Or just drop if it was always meant to be 'contact'
            }
            // If you simply added 'contact', then drop it. Given the forward migration, this is safer:
            // else if (Schema::hasColumn('users', 'contact')) {
            //     $table->dropColumn('contact');
            // }
            // A simpler down for now, assuming 'contact' is the desired final state and 'is_investable' is new.
            // Adjust if you need to specifically revert a rename.
        });
    }
};
