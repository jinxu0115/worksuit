<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\IconTrait;

class TaskReviewFile extends Model
{
    use HasFactory;
    use IconTrait;

    protected $table = 'task_review_file';

    const FILE_PATH = 'task-review-files';

    protected $appends = ['file_url', 'icon', 'file'];

    public function getFileUrlAttribute()
    {
        return asset_url_local_s3(TaskReviewFile::FILE_PATH . '/' . $this->task_id . '/' . $this->hashname);
    }

    public function getFileAttribute()
    {
        return $this->external_link ?: (TaskReviewFile::FILE_PATH . '/' . $this->task_id . '/' . $this->hashname);
    }

    public function canApprove(){
        $userId = user()->id;
        $task = Task::where('id', $this->task_id)->first();
        return $task->created_by == $userId;
    }
}
