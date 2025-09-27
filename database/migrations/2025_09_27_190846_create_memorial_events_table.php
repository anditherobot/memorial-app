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
        Schema::create('memorial_events', function (Blueprint $table) {
            $table->id();
            $table->enum('event_type', ['funeral', 'viewing', 'burial', 'repass']);
            $table->string('title')->nullable();
            $table->date('date')->nullable();
            $table->time('time')->nullable();
            $table->string('venue_name')->nullable();
            $table->text('address')->nullable();
            $table->string('contact_phone', 50)->nullable();
            $table->string('contact_email')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('poster_media_id')->nullable()->constrained('media')->onDelete('set null');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('memorial_events');
    }
};
