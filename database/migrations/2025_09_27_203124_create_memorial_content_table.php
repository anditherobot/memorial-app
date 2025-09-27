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
        Schema::create('memorial_content', function (Blueprint $table) {
            $table->id();
            $table->string('content_type', 100);
            $table->string('title')->nullable();
            $table->text('content')->nullable();
            $table->timestamp('updated_at');

            $table->unique('content_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('memorial_content');
    }
};
