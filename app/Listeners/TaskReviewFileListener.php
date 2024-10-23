<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Events\TaskReviewFileEvent;
use Illuminate\Support\Facades\Notification;
use App\Notifications\TaskReviewFileApproved;
use App\Notifications\TaskReviewFileApprovedByCreator;
use App\Notifications\TaskReviewFileUnapprovedByCreator;
use App\Notifications\TaskReviewFileApprovedByManager;
use App\Notifications\TaskReviewFileUnapprovedByManager;
use App\Notifications\TaskReviewFileUnrejected;
use App\Notifications\TaskReviewFileRejected;

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
            if ($event->notificationName === 'ApprovedReviewByCreator') {
                Notification::send($event->notifyUser, new TaskReviewFileApprovedByCreator($event->task));
            } elseif ($event->notificationName === 'UnapproveReviewByCreator') {
                Notification::send($event->notifyUser, new TaskReviewFileUnapprovedByCreator($event->task));
            } elseif ($event->notificationName === 'ApprovedReviewByManager') {
                Notification::send($event->notifyUser, new TaskReviewFileApprovedByManager($event->task));
            } elseif ($event->notificationName === 'UnapproveReviewByManager') {
                Notification::send($event->notifyUser, new TaskReviewFileUnapprovedByManager($event->task));
            } elseif ($event->notificationName === 'ApprovedReview') {
                Notification::send($event->notifyUser, new TaskReviewFileApproved($event->task));
            } elseif ($event->notificationName === 'Rejected') {
                Notification::send($event->notifyUser, new TaskReviewFileRejected($event->task));
            } elseif ($event->notificationName === 'Unrejected') {
                Notification::send($event->notifyUser, new TaskReviewFileUnrejected($event->task));
            }
        }
    }
}
