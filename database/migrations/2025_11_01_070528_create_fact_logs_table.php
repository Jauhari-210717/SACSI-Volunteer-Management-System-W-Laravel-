<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fact_logs', function (Blueprint $table) {
            $table->bigIncrements('fact_id');

            // Foreign key to fact_types
            $table->foreignId('fact_type_id')
                ->constrained('fact_types', 'fact_type_id')
                ->cascadeOnDelete();

            // Admin who performed the action
            $table->foreignId('admin_id')
                ->nullable()
                ->constrained('admin_accounts', 'admin_id')
                ->nullOnDelete();

            $table->string('entity_type', 100);  // e.g., 'import_logs'
            $table->unsignedBigInteger('entity_id')->nullable(); // the ID of the entity
            $table->string('action', 100);
            $table->longText('details')->nullable();
            $table->timestamp('logged_at')->useCurrent();
            $table->timestamps();

            $table->index(['entity_type', 'entity_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fact_logs');
    }
};
