<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop table first to avoid conflicts (safe for dev)
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('import_logs');
        Schema::enableForeignKeyConstraints();

        // Create table
        Schema::create('import_logs', function (Blueprint $table) {
            $table->id('import_id'); // Primary key
            $table->string('file_name'); // CSV file name

            // Admin who performed the import
            $table->foreignId('admin_id')
                  ->nullable()
                  ->constrained('admin_accounts', 'admin_id')
                  ->nullOnDelete(); // set null if admin deleted

            $table->integer('total_records')->default(0);
            $table->integer('valid_count')->default(0);
            $table->integer('invalid_count')->default(0);
            $table->integer('duplicate_count')->default(0);

            $table->string('status', 20)->default('Pending'); // string status instead of ENUM
            $table->text('remarks')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps(); // created_at + updated_at
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints(); // disable FK checks
        Schema::dropIfExists('import_logs');    // drop table safely
        Schema::enableForeignKeyConstraints();
    }
};
