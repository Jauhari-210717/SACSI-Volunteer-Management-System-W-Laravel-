<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fact_type', function (Blueprint $table) {
            $table->id('fact_type_id');       // Primary key
            $table->string('type_name', 100)->unique();
            $table->text('description')->nullable();
            $table->timestamps();             // created_at & updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fact_type');
    }
};
 