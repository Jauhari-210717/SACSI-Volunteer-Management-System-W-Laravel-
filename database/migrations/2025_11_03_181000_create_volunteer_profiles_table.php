<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('volunteer_profiles', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->foreignId('volunteer_id')
                  ->constrained('volunteers')   // Link to volunteers
                  ->cascadeOnDelete();          // Delete profile if volunteer is deleted

            $table->foreignId('import_id')
                  ->nullable()
                  ->constrained('import_logs', 'import_id') // Link to import logs
                  ->nullOnDelete();            // Set to null if import log deleted

            $table->string('full_name');
            $table->string('id_number')->nullable();
            $table->string('school_id')->nullable();
            $table->string('course')->nullable();
            $table->string('year_level')->nullable();
            $table->string('contact_number', 20)->nullable();
            $table->string('emergency_contact', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('fb_messenger')->nullable();
            $table->string('barangay')->nullable();
            $table->string('district')->nullable();
            $table->string('certificates')->nullable();
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->string('class_schedule')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('volunteer_profiles');
    }
};
