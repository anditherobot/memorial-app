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
        Schema::table('photos', function (Blueprint $table) {
            $table->foreignId('media_id')->nullable()->constrained()->after('user_id');
            $table->dropColumn(['original_path', 'display_path', 'mime_type', 'size', 'width', 'height', 'variants']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('photos', function (Blueprint $table) {
            $table->dropForeign(['media_id']);
            $table->dropColumn('media_id');
            $table->string('original_path')->nullable();
            $table->string('display_path')->nullable();
            $table->string('mime_type')->nullable();
            $table->integer('size')->nullable();
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->json('variants')->nullable();
        });
    }
};
