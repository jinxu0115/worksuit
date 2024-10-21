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
        Schema::table('task_review_comment', function (Blueprint $table) {
            $table->integer('media_width')->nullable()->change();
            $table->integer('media_height')->nullable()->change();
            $table->integer('position_top')->nullable()->change();
            $table->integer('position_left')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('task_review_comment', function (Blueprint $table) {
            //
        });
    }
};
