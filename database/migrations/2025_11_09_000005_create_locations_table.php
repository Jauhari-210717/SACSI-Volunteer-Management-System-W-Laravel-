<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->increments('location_id');
            
            // Only 1 or 2, representing the political district
            $table->unsignedTinyInteger('district_id')->comment('1 = District 1, 2 = District 2');
            
            // East, West, North, South, or Poblacion
            $table->string('zone_name', 50)->comment('Geographical zone name');
            
            // Barangay name
            $table->string('barangay', 100);
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
