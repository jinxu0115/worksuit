<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskReviewComment extends Model
{
    use HasFactory;

    protected $table = 'task_review_comment';

    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
