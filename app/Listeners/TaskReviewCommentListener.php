<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Events\TaskReviewCommentEvent;
use Illuminate\Support\Facades\Notification;
use App\Notifications\TaskReviewCommentCreated;

class TaskReviewCommentListener
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
    public function handle(TaskReviewCommentEvent $event)
    {
        if (!$event->task->is_private) {
            Notification::send($event->notifyUser, new TaskReviewCommentCreated($event->task));
        }
    }
}
