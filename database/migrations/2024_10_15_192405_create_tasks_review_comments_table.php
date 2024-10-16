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
        Schema::create('task_review_comment', function (Blueprint $table) {
            $table->id();
            $table->integer('review_file_id');
            $table->text('comment_text');
            $table->integer('media_width');
            $table->integer('media_height');
            $table->integer('position_top');
            $table->integer('position_left');
            $table->float('time_frame')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks_review_comments');
    }
};
