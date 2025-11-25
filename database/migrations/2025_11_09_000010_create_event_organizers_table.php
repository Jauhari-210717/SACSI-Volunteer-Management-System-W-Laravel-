<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('event_organizers')) {
            Schema::create('event_organizers', function (Blueprint $table) {
                $table->increments('organizer_id');
                $table->unsignedInteger('event_id');

                $table->string('name');           // organizer name
                $table->string('email')->nullable();   // organizer email
                $table->string('contact')->nullable(); // organizer contact #

                $table->foreign('event_id')
                    ->references('event_id')
                    ->on('events')
                    ->onDelete('cascade');

                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('event_organizers');
    }
};
