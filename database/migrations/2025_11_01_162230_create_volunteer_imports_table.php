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
        Schema::create('volunteer_imports', function (Blueprint $table) {
            $table->id('import_id');
            $table->string('filename');
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->integer('total_records')->default(0);
            $table->integer('valid_records')->default(0);
            $table->integer('invalid_records')->default(0);
            $table->enum('status', ['Pending', 'Validated', 'Submitted'])->default('Pending');
            $table->timestamps();

            // Relationships
            $table->foreign('admin_id')
                ->references('admin_id')
                ->on('admin_accounts')
                ->onDelete('set null');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('volunteer_imports');
    }
};
