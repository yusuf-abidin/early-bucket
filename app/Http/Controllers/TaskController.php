<?php
namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Inertia\Inertia;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $queryTask = Task::with('category', 'users')
            ->where('type', Task::TYPE_PENDING)
            ->whereNull('completed_at')
            ->orderBy('due_date');

        if($request->filled('user_id')){
            $queryTask->whereHas('users', function ($query) use ($request) {
                $query->where('users.id', $request->user_id);
            });
        }

        $tasks = $queryTask->get();
        $users = User::select('id', 'name', 'avatar', 'position')->orderBy('name')->get();
        $categories = Category::where('type', 'pending_matter')->get();

        $usersSummary = User::select('id', 'name', 'avatar', 'position')->orderBy('name')
            ->withCount([
                // 1. Benar-benar Pending (Belum lewat deadline & bukan mendekati deadline)
                'tasks as pending_count' => function (Builder $query) {
                    $query->where('type', Task::TYPE_PENDING)
                        ->whereNull('completed_at')
                        ->whereDate('due_date', '>', today()->addDays(3));
                },

                // 2. Sudah Lewat Deadline (Overdue)
                'tasks as overdue_count' => function (Builder $query) {
                    $query->where('type', Task::TYPE_PENDING)
                        ->whereNull('completed_at')
                        ->where('due_date', '<', now());
                },

                // 3. Mendekati Deadline (H-0 sampai H+3)
                'tasks as near_overdue_count' => function (Builder $query) {
                    $query->where('type', Task::TYPE_PENDING)
                        ->whereNull('completed_at')
                        ->whereDate('due_date', '>=', today())
                        ->whereDate('due_date', '<=', today()->addDays(3));
                }
            ])
            ->get();

        return Inertia::render('main/task/Index', [
            'tasks' => $tasks,
            'users' => $users,
            'categories' => $categories,
            'users_summary' => $usersSummary
        ]);
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
        $validated = $request->validate([
            'task_description' => ['required', 'max:255'],
            'users' => ['nullable', 'array'],
            'users.*' => ['exists:users,id'],
            'category_id' => ['required'],
            'due_date' => ['required'],
            'notes' => ['nullable', 'max:255']
        ]);

        $validated['type'] = Task::TYPE_PENDING;

        $task = Task::create($validated);
        if ($request->has('users')) {
            $task->users()->sync($request->users);
        }
        return redirect()->route('tasks.index')->with('success', 'Pending matter berhasil dibuat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Task $task)
    {

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Task $task)
    {
        if ($task->type !== Task::TYPE_PENDING) {
            return back()->withErrors(['message' => 'Tidak dapat menghapus karena bukan pending matter.']);
        }

        $validated = $request->validate([
            'task_description' => ['sometimes', 'required', 'max:255'],
            'category_id' => ['sometimes', 'required', 'exists:categories,id'],
            'users' => ['nullable', 'array'],
            'users.*' => ['exists:users,id'],
            'due_date' => ['sometimes', 'required'],
            'notes' => ['nullable', 'max:255'],
            'completed_at' => ['sometimes','nullable']
        ]);

        if ($request->has('completed_at')) {
            $validated['completed_at'] = $request->completed_at ? now() : null;
        }

        $task->update(Arr::except($validated, ['users']));

        if($request->has('users')) {
            $task->users()->sync($validated['users'] ?? []);
        }

        return redirect()->route('tasks.index')->with('success', 'Pending matter berhasil diupdate.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        if ($task->type !== Task::TYPE_PENDING) {
            return back()->withErrors(['message' => 'Tidak dapat menghapus karena bukan pending matter.']);
        }

        try {
            $task->users()->detach();
            $task->delete();

            return back()->with('success', 'Pending matter berhasil dihapus.');
        }catch (\Exception $exception){
            return back()->withErrors(['message' => 'Gagal menghapus pending matter: ' . $exception->getMessage()]);
        }
    }

    public function taskHistory(Request $request)
    {
        $sortBy = $request->sort_by ?? 'created_at';
        $sortDir = strtolower($request->sort_dir ?? 'desc') === 'asc' ? 'asc' : 'desc';

        $usersSummary = User::select('id', 'name', 'avatar', 'position')->orderBy('name')
            ->withCount([
                // 1. Benar-benar Pending (Belum lewat deadline & bukan mendekati deadline)
                'tasks as completed_this_week_count' => function (Builder $query) {
                    $query->whereNotNull('completed_at')
                        ->whereBetween('completed_at', [now()->startOfWeek(), now()->endOfWeek()
                    ]);
                },
            ])
            ->get();

        $tasksQuery = Task::query()
            ->where('type', Task::TYPE_PENDING)
            ->whereNotNull('completed_at')
            ->with(['category', 'users']);

        $tasksQuery
            ->when($request->search, function ($query, $search) {
                $query->where('task_description', 'like', "%{$search}%");
            })
            ->when($request->date_from, function ($query, $date) {
                $query->whereDate('tasks.created_at', '>=', $date);
            })
            ->when($request->date_to, function ($query, $date) {
                $query->whereDate('tasks.created_at', '<=', $date);
            })

            ->when($request->filled('user_id'), function ($query) use ($request) {
                $query->whereHas('users', function ($q) use ($request) {
                    $q->where('users.id', $request->user_id);
                });
            })

            ->when($request->filled('user_ids'), function ($query) use ($request) {
                $query->whereHas('users', function ($q) use ($request) {
                    $q->whereIn('users.id', $request->user_ids);
                });
            });

        if ($sortBy === 'category') {
            $tasksQuery
                ->join('categories', 'categories.id', '=', 'tasks.category_id')
                ->where('categories.type', 'pending_matter')
                ->orderBy('categories.order', $sortDir)
                ->select('tasks.*'); // WAJIB
        } else {
            if (in_array($sortBy, ['task_description'])) {
                $tasksQuery->orderByRaw("LOWER(tasks.{$sortBy}) {$sortDir}");
            } else {
                $tasksQuery->orderBy("tasks.{$sortBy}", $sortDir);
            }
        }

        $tasks = $tasksQuery
            ->paginate(20)
            ->withQueryString();

        $users = User::select('id', 'name')->orderBy('name')->get();

        return Inertia::render('main/task/History', [
            'tasks_history' => $tasks,
            'users_summary' => $usersSummary,
            'users' => $users
        ]);
    }
}
