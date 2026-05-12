<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RlqhTaskController extends Controller
{
    public function index(Request $request)
    {
        $queryTask = Task::with([
            'category',
            'users' => function ($q) {
                $q->whereIn('role', ['rlqh']);
            }
        ])
        ->where('scope', Task::SCOPE_RLQH)
        ->where('type', Task::TYPE_PENDING)
        ->whereNull('completed_at')
        ->orderBy('due_date');

        $queryTask
            ->when($request->filled('user_ids'), function ($query) use ($request) {
                $query->whereHas('users', function ($q) use ($request) {
                    $q->whereIn('users.id', $request->user_ids);
                });
            })
            ->when($request->date_from, function ($query, $date) {
                $query->whereDate('due_date', '>=', $date);
            })
            ->when($request->date_to, function ($query, $date) {
                $query->whereDate('due_date', '<=', $date);
            });

        if($request->filled('user_id')){
            $queryTask->whereHas('users', function ($query) use ($request) {
                $query->where('users.id', $request->user_id);
            });
        }

        $tasks = $queryTask->get();

        $users = User::select('id', 'name', 'avatar', 'position')
            ->whereIn('role', ['rlqh'])
            ->orderBy('name')
            ->get();
        $categories = Category::where('type', 'pending_matter')->get();

        $userSummary = User::select('id', 'name', 'avatar', 'position')
            ->whereIn('role', ['rlqh'])
            ->orderBy('name')
            ->withCount([
                // 1. Benar-benar Pending (Belum lewat deadline & bukan mendekati deadline)
                'tasks as pending_count' => function (Builder $query) {
                    $query->where('type', Task::TYPE_PENDING)
                        ->where('scope', Task::SCOPE_RLQH)
                        ->whereNull('completed_at')
                        ->whereDate('due_date', '>', today()->addDays(3));
                },

                // 2. Sudah Lewat Deadline (Overdue)
                'tasks as overdue_count' => function (Builder $query) {
                    $query->where('type', Task::TYPE_PENDING)
                        ->where('scope', Task::SCOPE_RLQH)
                        ->whereNull('completed_at')
                        ->where('due_date', '<', now());
                },

                // 3. Mendekati Deadline (H-0 sampai H+3)
                'tasks as near_overdue_count' => function (Builder $query) {
                    $query->where('type', Task::TYPE_PENDING)
                        ->where('scope', Task::SCOPE_RLQH)
                        ->whereNull('completed_at')
                        ->whereDate('due_date', '>=', today())
                        ->whereDate('due_date', '<=', today()->addDays(3));
                }
            ])
            ->get();

        return Inertia::render('main/task/RlqhTaskIndex', [
            'tasks' => $tasks,
            'users' => $users,
            'categories' => $categories,
            'users_summary' => $userSummary,
            'scope' => Task::SCOPE_RLQH
        ]);
    }

    public function taskHistory(Request $request)
    {
        $sortBy = $request->sort_by ?? 'created_at';
        $sortDir = strtolower($request->sort_dir ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $dateBy = $request->date_by ?? 'created_at';

        $allowedDateColumns = ['created_at', 'due_date', 'completed_at'];
        $targetColumn = in_array($dateBy, $allowedDateColumns) ? $dateBy : 'created_at';

        $usersSummary = User::select('id', 'name', 'avatar', 'position')
            ->whereIn('role', ['rlqh'])
            ->orderBy('name')
            ->withCount([
                // 1. Benar-benar Pending (Belum lewat deadline & bukan mendekati deadline)
                'tasks as completed_this_week_count' => function (Builder $query) {

                    $query->whereNotNull('completed_at')
                        ->where(function ($query) {
                            $query->where('scope', Task::SCOPE_RLQH);
                        });
                },
            ])
            ->get();

        $tasksQuery = Task::query()
            ->where('tasks.type', Task::TYPE_PENDING)
            ->whereNotNull('completed_at')
            ->where(function ($query) {
                $query->where('scope', Task::SCOPE_RLQH);
            })
            ->with(['category' => function ($query) {
                $query->withTrashed();
            }, 'users' => function ($query) {
                $query->whereIn('role', ['rlqh']);
            }]);

        $tasksQuery
            ->when($request->search, function ($query, $search) {
                $query->where('task_description', 'like', "%{$search}%");
            })
            ->when($request->date_from, function ($query, $date) use ($targetColumn){
                $query->whereDate("tasks.{$targetColumn}", '>=', $date);
            })
            ->when($request->date_to, function ($query, $date) use ($targetColumn) {
                $query->whereDate("tasks.{$targetColumn}", '<=', $date);
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

        $users = User::select('id', 'name')
            ->wherein('role', ['rlqh'])
            ->orderBy('name')->get();

        return Inertia::render('main/task/RlqhTaskHistory', [
            'tasks_history' => $tasks,
            'users_summary' => $usersSummary,
            'users' => $users
        ]);
    }
}
