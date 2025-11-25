<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('volunteer_profiles')) {
            Schema::create('volunteer_profiles', function (Blueprint $table) {
                $table->increments('volunteer_id');

                // Foreign Keys
                $table->unsignedInteger('import_id')->nullable();
                $table->unsignedInteger('location_id')->nullable();
                $table->unsignedInteger('course_id')->nullable();

                // Main Required Field
                $table->string('full_name');

                // Basic Info
                $table->string('id_number')->nullable()->unique();
                $table->string('year_level')->nullable();
                $table->string('email')->nullable();
                $table->string('contact_number')->nullable();
                $table->string('emergency_contact')->nullable();

                // Social
                $table->string('fb_messenger')->default('No FB messenger');

                // Location Info
                $table->string('barangay')->nullable();
                $table->string('district')->nullable();

                // Profile Picture
                $table->string('profile_picture_url')->nullable();
                $table->string('profile_picture_path')->nullable();

                // These should have defaults → MUST be string instead of text
                $table->string('certificates')->default('No certificates');
                $table->string('class_schedule')->default('No class schedule');
                $table->string('notes')->default('No notes');

                // Status
                $table->enum('status', ['active', 'inactive'])->default('active');

                $table->timestamps();

                // Foreign keys
                $table->foreign('import_id')->references('import_id')->on('import_logs')->onDelete('set null');
                $table->foreign('location_id')->references('location_id')->on('locations')->onDelete('set null');
                $table->foreign('course_id')->references('course_id')->on('courses')->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('volunteer_profiles')) {
            Schema::table('volunteer_profiles', function (Blueprint $table) {
                $table->dropForeign(['import_id']);
                $table->dropForeign(['location_id']);
                $table->dropForeign(['course_id']);
            });

            Schema::dropIfExists('volunteer_profiles');
        }
    }
};
