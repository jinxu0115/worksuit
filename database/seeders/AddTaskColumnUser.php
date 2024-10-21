<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\TaskColumns;

class AddTaskColumnUser extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        foreach ($users as $user) {
            $row = TaskColumns::where('user_id', $user->id)->first();
            if(!empty($row)) continue;
            TaskColumns::insert([
                'code' => true,
                'timer' => true,
                'task' => true,
                'completed_on' => true,
                'start_date' => true,
                'due_date' => true,
                'estimated_date' => true,
                'hours_logged' => true,
                'user_id' => $user->id,
                'assigned_to' => true,
                'status' => true,
                'action' => true,
                'creator_name' => true,
                'project_name' => true,
                'priority' => true,
                'review_status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
