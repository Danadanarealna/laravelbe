<?php

// Run this command: php artisan make:migration add_profile_image_to_investors_table

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
        Schema::table('investors', function (Blueprint $table) {
            $table->string('profile_image_path')->nullable()->after('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('investors', function (Blueprint $table) {
            $table->dropColumn('profile_image_path');
        });
    }
};