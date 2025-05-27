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
            Schema::create('investments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('investor_id')->constrained('investors')->onDelete('cascade');
                $table->foreignId('umkm_id')->constrained('users')->onDelete('cascade'); // umkm_id refers to the id in the 'users' table
                $table->decimal('amount', 15, 2);
                $table->string('status')->default('pending'); // e.g., pending, confirmed, completed, cancelled
                $table->timestamps();
            });
        }
    
        /**
         * Reverse the migrations.
         */
        public function down(): void
        {
            Schema::dropIfExists('investments');
        }
    };
    