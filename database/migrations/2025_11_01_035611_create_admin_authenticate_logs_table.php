<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_authenticate_logs', function (Blueprint $table) {
            $table->id('log_id');                 // Primary key
            $table->unsignedBigInteger('admin_id')->nullable(); // Nullable foreign key
            $table->timestamp('login_time')->useCurrent();
            $table->string('ip_address');
            $table->enum('status', ['success', 'failed']);
            $table->text('failure_reason')->nullable();

            // Foreign key constraint (optional, cascade on delete)
            $table->foreign('admin_id')
                  ->references('admin_id')
                  ->on('admin_accounts')
                  ->onDelete('set null'); // important for nullable
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_authenticate_logs');
    }
};
