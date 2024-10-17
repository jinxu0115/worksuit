<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Events\TaskReviewFileEvent;
use Illuminate\Support\Facades\Notification;
use App\Notifications\TaskReviewFileApproved;

class TaskReviewFileListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(TaskReviewFileEvent $event)
    {
        if (!$event->task->is_private) {
            Notification::send($event->notifyUser, new TaskReviewFileApproved($event->task));
        }
    }
}
