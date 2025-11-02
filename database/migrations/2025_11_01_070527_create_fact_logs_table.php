<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fact_logs', function (Blueprint $table) {
            $table->id('fact_id');

            // Fact type foreign key
            $table->unsignedBigInteger('fact_type_id');
            $table->foreign('fact_type_id')
                  ->references('fact_type_id')
                  ->on('fact_type')
                  ->onDelete('cascade');

            // Admin foreign key (explicit column)
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->foreign('admin_id')
                  ->references('admin_id')
                  ->on('admin_accounts')
                  ->onDelete('set null');

            $table->string('entity_type', 100);
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->string('action', 100);
            $table->text('details')->nullable();
            $table->timestamp('timestamp')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fact_logs');
    }
};
