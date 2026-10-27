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
        Schema::create('exp_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('amount');
            $table->string('source'); // reading_complete, quiz_attempt, quiz_correct, daily_target
            $table->foreignId('reading_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('week_key', 12)->index(); // ISO o-\WW in Asia/Jakarta
            $table->timestamp('created_at')->useCurrent();

            $table->index(['week_key', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exp_logs');
    }
};
