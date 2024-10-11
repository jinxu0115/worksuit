<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Http\Requests\Admin\TaskStatus\StoreRequest;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\TaskLabel;
use App\Models\TaskLabelList;
use App\Models\ProjectTemplateTask;
use App\Models\TaskboardColumn;
use Illuminate\Http\Request;

class TaskStatusController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.taskStatus';
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
        $this->taskLabels = TaskLabelList::all();
        $this->projects = Project::all();
        $this->taskId = request()->task_id;
        $this->projectTemplateTaskId = request()->project_template_task_id;
        $this->projectId = request()->project_id;
        $this->status = TaskboardColumn::orderBy('priority', 'asc')->get();
        $this->waitingApprovalTaskBoardColumn = TaskboardColumn::waitingForApprovalColumn();
        return view('tasks.create_status', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        // abort_403(user()->permission('task_labels') !== 'all');
        
        if(count(TaskboardColumn::where('column_name', $request->status_name)->get()) > 0){
            return Reply::error('Status name is exist!');
        }

        $taskStatus = new TaskboardColumn();
        
        $taskStatus->column_name = $request->status_name;
        $taskStatus->slug = $request->status_slug;
        $taskStatus->priority = $request->status_priority;
        $taskStatus->label_color = $request->color;
        $user = User::find(user()->id);
        $taskStatus->company_id = $user->company_id;

        $taskStatus->save();
        
        return Reply::successWithData(__('messages.recordSaved'), ['data' => []]);
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
        // abort_403(user()->permission('task_labels') !== 'all');

        $taskStatus = TaskboardColumn::findOrFail($id);
        if(count(TaskboardColumn::where('column_name', $request->status_name)->get()) > 0 && $taskStatus->column_name != $request->status_name){
            return Reply::error('Status name is exist!');
        }

        $taskStatus->column_name = $request->status_name;
        $taskStatus->slug = $request->status_slug;
        $taskStatus->priority = $request->status_priority;
        $taskStatus->label_color = $request->color;
        $taskStatus->save();
        
        return Reply::successWithData(__('messages.recordSaved'), ['data' => []]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        TaskboardColumn::destroy($id);

        return Reply::successWithData(__('messages.recordSaved'), ['data' => []]);
    }
}
