<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_accounts', function (Blueprint $table) {
            $table->id('admin_id'); // Primary key
            $table->string('username')->unique();
            $table->string('password');
            $table->string('email')->unique();
            $table->string('profile_picture')->nullable();
            $table->string('role')->default('admin');
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints(); // Disable FK checks
        Schema::dropIfExists('admin_accounts'); // Drop table safely
        Schema::enableForeignKeyConstraints();
    }
};
