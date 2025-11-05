<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('volunteers', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->string('volunteer_code')->unique();
            $table->string('full_name');
            $table->string('email')->nullable()->unique();
            $table->string('contact_number', 20)->nullable();
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->dateTime('registration_date')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('volunteers');
    }
};
