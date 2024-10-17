<?php

namespace App\Observers;

use App\Models\TaskReviewFile;
use App\Models\Task;
use App\Models\TaskUser;
use App\Models\User;
use Illuminate\Support\Facades\Http;

class TaskReviewFileObserver
{

    private function webhookData(TaskReviewFile $taskReviewFile, Task $task, $action){

        $taskUsers = TaskUser::where('task_id', $task->id)->get();
        $assignedUsers = array();

        foreach ($taskUsers as $taskUser) {
            $user = User::where('id', $taskUser->user_id)->first();
            $userDetail = [
                'name' => $user->name,
                'email' => $user->email,
                'mobile' => $user->mobile
            ];
            array_push($assignedUsers, $userDetail);
        }
        
        $customFieldData = $task->getCustomFieldsDataWithLabel();

        foreach ($customFieldData as $key => $value) {
            if ($key == '') {
                unset($customFieldData[$key]); // Remove the entry with an empty key
            }
        }

        $user = User::where('company_id', $task->company_id)->first();

        $webhookData = [
            'heading' => $task->heading,
            'project_id' => $task->project_id,
            'project_name' => $task->project?->project_name,
            'task_category_id' => $task->task_category_id,
            'priority' => $task->priority,
            'approval_send' => $task->approval_send,
            // 'approved' => $approved,
            // 'approved_by' => $approved_by,
            'description' => $task->description,
            'status' => $task->boardColumn->column_name,
            'custom_fields' => $customFieldData,
            'company_id' => $task->company_id,
            'assigned_to' => $assignedUsers,
            'assigned_by' => [
                'name' => $user->name,
                'phone_number' => $user->mobile,
                'email' => $user->email,
            ],
            'action' => $action,
            'review_file_name' => $taskReviewFile->filename,
            'type' => pathinfo($taskReviewFile->filename, PATHINFO_EXTENSION),
            'approved' => $taskReviewFile->approved ? true : false
        ];
        return $webhookData;
    }
    /**
     * Handle the TaskReviewFile "created" event.
     */
    public function created(TaskReviewFile $taskReviewFile): void
    {        
        $webhookUrl = config('app.webhook_url');
        $task = Task::where('id', $taskReviewFile->task_id)->first();
        Http::post($webhookUrl, array_merge($this->webhookData($taskReviewFile, $task, 'task review file created')));
    }

    /**
     * Handle the TaskReviewFile "updated" event.
     */
    public function updated(TaskReviewFile $taskReviewFile): void
    {
        //
    }

    /**
     * Handle the TaskReviewFile "deleted" event.
     */
    public function deleted(TaskReviewFile $taskReviewFile): void
    {        
        $webhookUrl = config('app.webhook_url');
        $task = Task::where('id', $taskReviewFile->task_id)->first();
        Http::post($webhookUrl, array_merge($this->webhookData($taskReviewFile, $task, 'task review file deleted')));
    }

    /**
     * Handle the TaskReviewFile "restored" event.
     */
    public function restored(TaskReviewFile $taskReviewFile): void
    {
        //
    }

    /**
     * Handle the TaskReviewFile "force deleted" event.
     */
    public function forceDeleted(TaskReviewFile $taskReviewFile): void
    {
        //
    }
}
