<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Memo;
use App\Models\Task;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index() {
        $user = auth()->user();
        $userId = $user->id;

        // Task Statistics
        $taskStats = Task::whereHas('users', function($query) use ($userId) {
            $query->where('user_id', $userId);
        })
            ->where('created_at', '>=', now()->subDays(30))
            ->toBase() // Menggunakan query builder murni (lebih cepat untuk agregat)
            ->selectRaw('count(*) as total')
            ->selectRaw('count(case when completed_at is not null then 1 end) as completed')
            ->selectRaw('count(case when completed_at is null and due_date < ? then 1 end) as overdue', [now()])
            ->first();

        $totalTasks = $taskStats->total;
        $completedTasks = $taskStats->completed;
        $pendingTasks = $totalTasks - $completedTasks;
        $overdueTasks = $taskStats->overdue;


        // Upcoming Tasks (next 7 days)
        $upcomingTasks = Task::whereHas('users', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })
            ->whereNull('completed_at')
            ->whereBetween('due_date', [now(), now()->addDays(7)])
            ->with(['category.color'])
            ->orderBy('due_date', 'asc')
            ->limit(5)
            ->get();

        // Memo Statistics
        $totalMemos = Memo::whereHas('users', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })->where('received_at', '>=', now()->subDays(30))->count();

        // Recent Memos
        $recentMemos = Memo::whereHas('users', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })
            ->with(['category.color'])
            ->orderBy('received_at', 'desc')
            ->limit(5)
            ->get();

        // Task Completion Rate by Category
        $tasksByCategory = Category::where('type', 'pending_matter')
            ->withCount([
                'tasks as total_tasks' => function($query) use ($userId) {
                    $query->whereHas('users', fn($q) => $q->where('user_id', $userId))
                    ->where('created_at', '>=', now()->subDays(30));
                },
                'tasks as completed_tasks' => function($query) use ($userId) {
                    $query->whereHas('users', fn($q) => $q->where('user_id', $userId))
                        ->whereNotNull('completed_at')
                        ->where('completed_at', '>=', now()->subDays(30));
                }
            ])
            ->with('color')
            ->get()
            ->map(function($category) {
                return [
                    'name'       => $category->name,
                    'total'      => $category->total_tasks,
                    'completed'  => $category->completed_tasks,
                    'percentage' => $category->total_tasks > 0
                        ? round(($category->completed_tasks / $category->total_tasks) * 100)
                        : 0,
                    'color'      => $category->color
                ];
            });

        // Weekly Activity (last 7 days)
        $startDate = now()->subDays(6)->startOfDay();
        $endDate   = now()->endOfDay();

        $dailyTaskCounts = Task::whereHas('users', function($query) use ($userId) {
            $query->where('user_id', $userId);
        })
            ->whereBetween('completed_at', [$startDate, $endDate])
            ->selectRaw('DATE(completed_at) as date, count(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date');

        $dailyMemoCounts = Memo::whereHas('users', function($query) use ($userId) {
            $query->where('user_id', $userId);
        })
            ->whereNotNull('completed_at')
            ->whereBetween('completed_at', [$startDate, $endDate])
            ->selectRaw('DATE(completed_at) as date, count(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date');

        $weeklyActivity = collect(range(6, 0))->map(function($daysAgo) use ($dailyTaskCounts, $dailyMemoCounts) {
            $dateKey = now()->subDays($daysAgo)->format('Y-m-d'); // Format key agar cocok dengan database

            return [
                'date'            => now()->subDays($daysAgo)->format('M d'),
                // Ambil dari collection yang sudah di-fetch, default 0 jika tidak ada
                'tasks_completed' => $dailyTaskCounts->get($dateKey, 0),
                'memos_completed'  => $dailyMemoCounts->get($dateKey, 0),
            ];
        });
//        dd($weeklyActivity);

        return Inertia::render('Dashboard', [
            'stats' => [
                'totalTasks' => $totalTasks,
                'completedTasks' => $completedTasks,
                'pendingTasks' => $pendingTasks,
                'overdueTasks' => $overdueTasks,
                'totalMemos' => $totalMemos,
                'completionRate' => $totalTasks > 0
                    ? round(($completedTasks / $totalTasks) * 100)
                    : 0,
            ],
            'upcomingTasks' => $upcomingTasks,
            'recentMemos' => $recentMemos,
            'tasksByCategory' => $tasksByCategory,
            'weeklyActivity' => $weeklyActivity,
        ]);
    }
}
