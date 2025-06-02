<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'umkm_description')) {
                $table->text('umkm_description')->nullable()->after('is_investable');
            }
            if (!Schema::hasColumn('users', 'umkm_profile_image_path')) {
                $table->string('umkm_profile_image_path')->nullable()->after('umkm_description');
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'umkm_description')) {
                $table->dropColumn('umkm_description');
            }
            if (Schema::hasColumn('users', 'umkm_profile_image_path')) {
                $table->dropColumn('umkm_profile_image_path');
            }
        });
    }
};
