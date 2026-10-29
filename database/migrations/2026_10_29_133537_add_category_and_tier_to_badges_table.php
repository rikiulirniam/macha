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
        Schema::table('badges', function (Blueprint $table) {
            $table->string('category')->nullable()->after('icon')->index();
            $table->unsignedInteger('tier')->default(1)->after('category');
            $table->unsignedInteger('required_count')->default(1)->after('tier');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('badges', function (Blueprint $table) {
            $table->dropIndex(['category']);
            $table->dropColumn(['category', 'tier', 'required_count']);
        });
    }
};
