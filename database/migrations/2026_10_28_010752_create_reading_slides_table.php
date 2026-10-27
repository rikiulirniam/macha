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
        Schema::create('reading_slides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reading_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('position');
            $table->string('type'); // text, quote, image, audio
            $table->text('body')->nullable();
            $table->string('source')->nullable();
            $table->string('media_path')->nullable();
            $table->string('bg_color')->default('paper'); // teal, kilat, amber, paper
            $table->timestamps();

            $table->index(['reading_id', 'position']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reading_slides');
    }
};
