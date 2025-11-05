<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_authenticate_logs', function (Blueprint $table) {
            $table->id('log_id'); // Primary key

            // ✅ Use foreignId() to ensure it matches unsignedBigInteger type
            $table->foreignId('admin_id')
                  ->nullable()
                  ->constrained('admin_accounts', 'admin_id')
                  ->nullOnDelete();

            $table->timestamp('login_time')->useCurrent();
            $table->string('ip_address')->nullable();
            $table->enum('status', ['success', 'failed']);
            $table->text('failure_reason')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_authenticate_logs');
    }
};
