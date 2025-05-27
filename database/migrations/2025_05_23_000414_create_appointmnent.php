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
            Schema::create('appointments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('investor_id')->constrained('investors')->onDelete('cascade');
                $table->foreignId('umkm_id')->constrained('users')->onDelete('cascade'); // umkm_id refers to the id in the 'users' table
                $table->foreignId('investment_id')->nullable()->constrained('investments')->onDelete('set null');
                $table->text('appointment_details')->nullable(); // e.g., "Discussion about investment of $AMOUNT"
                $table->dateTime('appointment_time')->nullable(); // Proposed or confirmed time by UMKM
                $table->string('status')->default('requested'); // e.g., requested, confirmed, completed, cancelled
                $table->string('contact_method')->default('whatsapp'); // e.g., whatsapp, email, call
                $table->text('contact_payload')->nullable(); // Store the initial message sent or relevant contact details
                $table->timestamps();
            });
        }
    
        /**
         * Reverse the migrations.
         */
        public function down(): void
        {
            Schema::dropIfExists('appointments');
        }
    };
    