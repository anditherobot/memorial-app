<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;

class AdminTaskController extends Controller
{
    public function index()
    {
        $tasks = Task::with('assignedTo')
            ->orderedForKanban()
            ->get()
            ->groupBy('status');

        $users = User::all();

        return view('admin.tasks.index', compact('tasks', 'users'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:todo,in_progress,completed,blocked',
            'priority' => 'required|in:low,medium,high,urgent',
            'category' => 'nullable|string|max:255',
            'assigned_to' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        Task::create($data);

        return response()->json(['success' => true]);
    }

    public function show(Task $task)
    {
        $task->load('assignedTo');
        return response()->json($task);
    }

    public function update(Request $request, Task $task)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:todo,in_progress,completed,blocked',
            'priority' => 'required|in:low,medium,high,urgent',
            'category' => 'nullable|string|max:255',
            'assigned_to' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $task->update($data);

        return response()->json(['success' => true]);
    }

    public function updateStatus(Request $request, Task $task)
    {
        $data = $request->validate([
            'status' => 'required|in:todo,in_progress,completed,blocked',
        ]);

        $task->update($data);

        return response()->json(['success' => true]);
    }

    public function destroy(Task $task)
    {
        $task->delete();

        return response()->json(['success' => true]);
    }
}