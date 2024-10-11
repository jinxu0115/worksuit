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
        Schema::create('task_columns', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->default(0);
            $table->boolean('code')->default(true);
            $table->boolean('timer')->default(true);
            $table->boolean('task')->default(true);
            $table->boolean('completed_on')->default(true);
            $table->boolean('start_date')->default(true);
            $table->boolean('due_date')->default(true);
            $table->boolean('estimated_date')->default(true);
            $table->boolean('hours_logged')->default(true);
            $table->boolean('assigned_to')->default(true);
            $table->boolean('status')->default(true);
            $table->boolean('action')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_columns');
    }
};
