<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $status = 0;
        $tasks = Task::where('is_completed',$status)->get();
        return view('tasks', ['tasks' => $tasks]);
    }
    public function allTasks(Request $request)
    {
        if($request->has('status') && $request->status == 1){
            $tasks = Task::all();
        }else{
            $tasks = Task::where('is_completed','0')->get();
        }
        return response()->json($tasks);
    }

    public function store(Request $request)
    {
        $taskFind = Task::where('title',$request->title)->first();
        if(!empty($taskFind)){
            return response()->json(['success' => false, 'message' => 'Task title already exist.']);
        }

        $task = Task::create($request->all());
        return response()->json($task);
    }

    public function update(Request $request, $id)
    {
        $task = Task::find($request->id);
        $is_completed = 1-$task->is_completed;
        $task->update(['is_completed'=>$is_completed]);
        return response()->json($task);
    }

    public function destroy($id)
    {
        Task::destroy($id);
        return response()->json(['success' => true, 'message' => 'Task deleted successfully.']);
    }
}

