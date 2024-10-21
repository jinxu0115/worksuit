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
            $table->dropColumn('media_width');
            $table->dropColumn('media_height');
            $table->dropColumn('position_top');
            $table->dropColumn('position_left');

            $table->float('left_percentage')->nullable();
            $table->float('top_percentage')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
