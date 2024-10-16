<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helper\Files;
use App\Helper\Reply;
use App\Models\Task;
use App\Models\TaskReviewFile;

class TaskReviewFileController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageIcon = 'icon-layers';
        $this->pageTitle = 'app.menu.taskFiles';
    }

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
        $this->addPermission = user()->permission('add_task_files');
        $task = Task::findOrFail($request->task_id);
        $taskUsers = $task->users->pluck('id')->toArray();

        abort_403(!(
            $this->addPermission == 'all'
            || ($this->addPermission == 'added' && $task->added_by == user()->id)
            || ($this->addPermission == 'owned' && in_array(user()->id, $taskUsers))
            || ($this->addPermission == 'added' && (in_array(user()->id, $taskUsers) || $task->added_by == user()->id))
        ));

        if ($request->hasFile('file')) {

            foreach ($request->file as $idx => $fileData) {
                $file = new TaskReviewFile();
                $file->task_id = $request->task_id;

                $filename = Files::uploadLocalOrS3($fileData, TaskReviewFile::FILE_PATH.'/' . $request->task_id);

                $file->user_id = $this->user->id;
                $file->filename = $fileData->getClientOriginalName();
                $file->hashname = $filename;
                $file->size = $fileData->getSize();
                if (str_starts_with($fileData->getMimeType(), 'video/')) {
                    $sanitizedFileName = str_replace([' ', '.'], '_', $fileData->getClientOriginalName());
                    $file->duration = $request->input($sanitizedFileName . '_duration'); 
                }
                $file->save();

                $this->logTaskActivity($task->id, $this->user->id, 'uploadReviewFile', $task->board_column_id);
            }

            $this->files = TaskReviewFile::where('task_id', $request->task_id)->orderByDesc('id');
            $viewTaskFilePermission = user()->permission('view_task_files');

            if ($viewTaskFilePermission == 'added') {
                $this->files = $this->files->where('added_by', user()->id);
            }

            $this->reviewFiles = $this->files->get();
            $view = view('tasks.review-files.show', $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'view' => $view]);
        }

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $file = TaskReviewFile::findOrFail($id);
        // $this->deletePermission = user()->permission('delete_task_files');
        // abort_403(!($this->deletePermission == 'all' || ($this->deletePermission == 'added' && $file->added_by == user()->id)));

        TaskReviewFile::destroy($id);

        $this->reviewFiles = TaskReviewFile::where('task_id', $file->task_id)->orderByDesc('id')->get();

        $view = view('tasks.review-files.show', $this->data)->render();

        return Reply::successWithData(__('messages.deleteSuccess'), ['view' => $view]);
    }    

    public function downloadReviewFile($id)
    {
        $file = TaskReviewFile::whereRaw('md5(id) = ?', $id)->firstOrFail();
        $this->viewPermission = user()->permission('view_task_files');
        abort_403(!($this->viewPermission == 'all' || ($this->viewPermission == 'added' && $file->added_by == user()->id)));
        return download_local_s3($file, 'task-review-files/' . $file->task_id . '/' . $file->hashname);

    }
}
