<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fact_types', function (Blueprint $table) {
            $table->id('fact_type_id'); // Primary key
            $table->string('type_name', 100)->unique(); // Name of the fact type
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Seed default fact types
        \DB::table('fact_types')->insertOrIgnore([
            ['type_name' => 'Import Verified', 'description' => 'Log for successfully imported volunteers', 'created_at' => now(), 'updated_at' => now()],
            ['type_name' => 'Failed Import', 'description' => 'Log for failed volunteer imports', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('fact_types');
    }
};
