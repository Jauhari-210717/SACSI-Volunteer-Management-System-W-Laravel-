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
        Schema::create('import_logs', function (Blueprint $table) {
            $table->id('import_id');
            $table->string('filename');
            $table->dateTime('import_date')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->integer('total_records')->default(0);
            $table->string('status', 50)->default('Pending');
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('admin_id')
                  ->references('admin_id')
                  ->on('admin_accounts')
                  ->nullOnDelete(); // same as ON DELETE SET NULL
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('import_logs');
    }
};
