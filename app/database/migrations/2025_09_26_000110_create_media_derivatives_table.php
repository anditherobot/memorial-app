<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('media_derivatives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_id')->constrained('media')->cascadeOnDelete();
            $table->string('type', 64); // thumbnail, poster, optimized, transcoded
            $table->string('storage_path');
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->timestamps();

            $table->index(['type']);
            $table->unique(['media_id', 'type', 'storage_path']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_derivatives');
    }
};

