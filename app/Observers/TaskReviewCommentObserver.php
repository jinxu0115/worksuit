<?php

namespace App\Observers;

use App\Models\TaskReviewComment;
use App\Models\Task;
use App\Models\TaskUser;
use App\Models\User;
use App\Models\TaskReviewFile;
use Illuminate\Support\Facades\Http;
use App\Events\TaskReviewCommentEvent;

class TaskReviewCommentObserver
{
    private function webhookData(TaskReviewComment $taskReviewComment, TaskReviewFile $taskReviewFile,  Task $task, $action){

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
            'review_comment' => $taskReviewComment->comment_text
        ];
        return $webhookData;
    }
    /**
     * Handle the TaskReviewComment "created" event.
     */
    public function created(TaskReviewComment $taskReviewComment): void
    {
        $webhookUrl = config('app.webhook_url');
        $taskReviewFile = TaskReviewFile::where('id', $taskReviewComment->review_file_id)->first();
        $task = Task::where('id', $taskReviewFile->task_id)->first();
        event(new TaskReviewCommentEvent($task, $task->users, 'CommentCreated'));
        Http::post($webhookUrl, array_merge($this->webhookData($taskReviewComment, $taskReviewFile, $task, 'task review comment created')));
    }

    /**
     * Handle the TaskReviewComment "updated" event.
     */
    public function updated(TaskReviewComment $taskReviewComment): void
    {        
        $webhookUrl = config('app.webhook_url');
        $taskReviewFile = TaskReviewFile::where('id', $taskReviewComment->review_file_id)->first();
        $task = Task::where('id', $taskReviewFile->task_id)->first();
        Http::post($webhookUrl, array_merge($this->webhookData($taskReviewComment, $taskReviewFile, $task, 'task review comment updated')));
    }

    /**
     * Handle the TaskReviewComment "deleted" event.
     */
    public function deleted(TaskReviewComment $taskReviewComment): void
    {        
        $webhookUrl = config('app.webhook_url');
        $taskReviewFile = TaskReviewFile::where('id', $taskReviewComment->review_file_id)->first();
        $task = Task::where('id', $taskReviewFile->task_id)->first();
        Http::post($webhookUrl, array_merge($this->webhookData($taskReviewComment, $taskReviewFile, $task, 'task review comment deleted')));
    }

    /**
     * Handle the TaskReviewComment "restored" event.
     */
    public function restored(TaskReviewComment $taskReviewComment): void
    {
        //
    }

    /**
     * Handle the TaskReviewComment "force deleted" event.
     */
    public function forceDeleted(TaskReviewComment $taskReviewComment): void
    {
        //
    }
}
