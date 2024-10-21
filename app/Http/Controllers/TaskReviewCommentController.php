<?php

namespace App\Http\Controllers;

use App\Models\TaskReviewComment;
use App\Models\TaskReviewFile;
use Illuminate\Http\Request;
use App\Helper\Reply;

class TaskReviewCommentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $taskReviewComment = new TaskReviewComment();
        $taskReviewComment->review_file_id = $request['mediaId'];
        $taskReviewComment->comment_text = $request['commentText'];
        $taskReviewComment->media_width = $request['mediaWidth'];
        $taskReviewComment->media_height = $request['mediaHeight'];
        $taskReviewComment->position_top = $request['positionTop'];
        $taskReviewComment->position_left = $request['positionLeft'];
        $taskReviewComment->time_frame = $request['timeFrame'];
        $taskReviewComment->user_id = user()->id;
        $taskReviewComment->save();

        $this->taskReviewComments = TaskReviewComment::where('review_file_id', $request['mediaId'])->get();
        $this->review_file = TaskReviewFile::where('id', $request['mediaId'])->first();

        $media_view = view('front.tasks.edit-review.show-media-comments', $this->data)->render();
        if($this->review_file->duration){
            $list_view = view('front.tasks.edit-review.show-video-comments', $this->data)->render();
            $comment_marker_view = view('front.tasks.edit-review.comments-marker-show', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'list_view' => $list_view, 'media_view' => $media_view, 'taskReviewComments' => $this->taskReviewComments, 'comment_marker_view' => $comment_marker_view]);
        } else {
            $list_view = view('front.tasks.edit-review.show-image-comments', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'list_view' => $list_view, 'media_view' => $media_view, 'taskReviewComments' => $this->taskReviewComments]);
        }
        
    }

    /**
     * Display the specified resource.
     */
    public function show(TaskReviewComment $taskReviewComment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TaskReviewComment $taskReviewComment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TaskReviewComment $taskReviewComment)
    {
        $taskReviewComment->comment_text = $request->commentText;
        $taskReviewComment->updated_by = user()->id;
        $taskReviewComment->save();

        $this->taskReviewComments = TaskReviewComment::where('review_file_id', $taskReviewComment->review_file_id)->get();
        $this->review_file = TaskReviewFile::where('id', $taskReviewComment->review_file_id)->first();

        $media_view = view('front.tasks.edit-review.show-media-comments', $this->data)->render();
        
        $list_view = '';
        if($this->review_file->duration){
            $list_view = view('front.tasks.edit-review.show-video-comments', $this->data)->render();
        } else {
            $list_view = view('front.tasks.edit-review.show-image-comments', $this->data)->render();
        }
        return Reply::dataOnly(['status' => 'success', 'list_view' => $list_view, 'media_view' => $media_view, 'taskReviewComments' => $this->taskReviewComments]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TaskReviewComment $taskReviewComment)
    {
        $review_file_id = $taskReviewComment->review_file_id;
        $taskReviewComment->delete();

        $this->taskReviewComments = TaskReviewComment::where('review_file_id', $review_file_id)->get();

        $this->review_file = TaskReviewFile::where('id', $review_file_id)->first();

        $media_view = view('front.tasks.edit-review.show-media-comments', $this->data)->render();
        
        if($this->review_file->duration){
            $list_view = view('front.tasks.edit-review.show-video-comments', $this->data)->render();
            $comment_marker_view = view('front.tasks.edit-review.comments-marker-show', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'list_view' => $list_view, 'media_view' => $media_view, 'taskReviewComments' => $this->taskReviewComments, 'comment_marker_view' => $comment_marker_view]);
        } else {
            $list_view = view('front.tasks.edit-review.show-image-comments', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'list_view' => $list_view, 'media_view' => $media_view, 'taskReviewComments' => $this->taskReviewComments]);
        }
    }
}
