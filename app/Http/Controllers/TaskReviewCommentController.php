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
        $comment = $request->comment;

        $taskReviewComment = new TaskReviewComment();
        $taskReviewComment->review_file_id = $request['fileId'];
        $taskReviewComment->comment_text = $comment['text'];
        if (isset($comment['time'])) {
            $taskReviewComment->time_frame = $comment['time'];
        }
        $taskReviewComment->user_id = user()->id;
        if (isset($comment['rectangle'])) {
            $taskReviewComment->rect_data = json_encode($comment['rectangle']);
        }
        $taskReviewComment->save();
        return TaskReviewComment::where('review_file_id', $request->fileId)->with('user')->get();
        
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
        $taskReviewComment->comment_text = $request->updatedText;
        $taskReviewComment->update();
        return TaskReviewComment::where('review_file_id', $taskReviewComment->review_file_id)->with('user')->get();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TaskReviewComment $taskReviewComment)
    {
        $review_file_id = $taskReviewComment->review_file_id;
        $taskReviewComment->delete();

        return TaskReviewComment::where('review_file_id', $review_file_id)->with('user')->get();
    }

    public function getAllComments(Request $request){
        $comments = TaskReviewComment::where('review_file_id', $request->fileId)
                    ->with('user')
                    ->get();
        
        return response()->json($comments);
    }

    public function storeImageComment(Request $request){
        $taskReviewComment = new TaskReviewComment();
        $taskReviewComment->review_file_id = $request['fileId'];
        $taskReviewComment->comment_text = $request['commentText'];
        $taskReviewComment->user_id = user()->id;
        $taskReviewComment->save();
        return TaskReviewComment::where('review_file_id', $request->fileId)->with('user')->get();
    }
    
    public function updateImageComment(Request $request){
        $taskReviewComment = TaskReviewComment::where('id', $request->commentId)->first();
        $taskReviewComment->comment_text = $request['commentText'];
        $taskReviewComment->updated_by = user()->id;
        $taskReviewComment->save();
        return TaskReviewComment::where('review_file_id', $taskReviewComment->review_file_id)->with('user')->get();
    }
}
