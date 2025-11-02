<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_accounts', function (Blueprint $table) {
            $table->id('admin_id');                   // Primary key
            $table->string('username')->unique();     // Unique username
            $table->string('password');               // Password
            $table->string('email')->unique();        // Unique email
            $table->string('profile_picture')->nullable(); // Profile picture path
            $table->string('full_name')->nullable();  // Full name optional
            $table->string('role')->default('admin'); // Role default
            $table->string('status')->default('active'); // Status default
            $table->timestamps();                     // created_at and updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_accounts');
    }
};
