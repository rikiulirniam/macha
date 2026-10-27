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
        Schema::create('reading_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reading_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('last_slide')->default(1);
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('quiz_answered_at')->nullable();
            $table->boolean('quiz_correct')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'reading_id']);
            $table->index(['user_id', 'completed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reading_progress');
    }
};
