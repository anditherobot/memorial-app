<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('media_attachables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_id')->constrained('media')->cascadeOnDelete();
            $table->unsignedBigInteger('attachable_id');
            $table->string('attachable_type');
            $table->string('role', 64)->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['attachable_type', 'attachable_id']);
            $table->unique(['media_id', 'attachable_type', 'attachable_id', 'role'], 'uniq_media_attachables');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_attachables');
    }
};

