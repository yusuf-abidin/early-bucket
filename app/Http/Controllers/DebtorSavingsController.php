<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Inertia\Inertia;

class DebtorSavingsController extends Controller
{
    public function index(Request $request)
    {
        $queryDebtSavings = Task::with('category', 'users')
            ->where('type', Task::TYPE_DEBITUR)
            ->orderBy('due_date');

        if ($request->filled('user_id')) {
            $queryDebtSavings->whereHas('users', function ($query) use ($request) {
                $query->where('users.id', $request->user_id);
            });
        }

        $debtSavings = $queryDebtSavings->get();
        $users = User::select('id', 'name', 'avatar', 'position')->orderBy('name')->get();
        $categories = Category::where('type', 'debitur_menabung')->get();

        $usersSummary = User::select('id', 'name', 'avatar', 'position')->orderBy('name')
            ->withCount([
                // 1. Benar-benar Pending (Belum lewat deadline & bukan mendekati deadline)
                'tasks as pending_count' => function (Builder $query) {
                    $query->where('type', Task::TYPE_DEBITUR)
                        ->whereNull('completed_at')
                        ->whereDate('due_date', '>', today()->addDays(3));
                },

                // 2. Sudah Lewat Deadline (Overdue)
                'tasks as overdue_count' => function (Builder $query) {
                    $query->where('type', Task::TYPE_DEBITUR)
                        ->whereNull('completed_at')
                        ->where('due_date', '<', now());
                },

                // 3. Mendekati Deadline (H-0 sampai H+3)
                'tasks as near_overdue_count' => function (Builder $query) {
                    $query->where('type', Task::TYPE_DEBITUR)
                        ->whereNull('completed_at')
                        ->whereDate('due_date', '>=', today())
                        ->whereDate('due_date', '<=', today()->addDays(3));
                },

                'tasks as completed_count' => function (Builder $query) {
                    $query->where('type', Task::TYPE_DEBITUR)
                        ->whereNotNull('completed_at');
                }
            ])
            ->get();

        $thirtyDaysAgo = now()->subDays(30);

        $taskStats = [
            'pending' => Task::where('type', Task::TYPE_DEBITUR)
                ->whereNull('completed_at')
                ->whereDate('due_date', '>', today()->addDays(3))
                ->where('created_at', '>=', $thirtyDaysAgo)
                ->count(),

            'near_deadline' => Task::where('type', Task::TYPE_DEBITUR)
                ->whereNull('completed_at')
                ->whereDate('due_date', '>=', today())
                ->whereDate('due_date', '<=', today()->addDays(3))
                ->where('created_at', '>=', $thirtyDaysAgo)
                ->count(),

            'overdue' => Task::where('type', Task::TYPE_DEBITUR)
                ->whereNull('completed_at')
                ->where('due_date', '<', now())
                ->where('created_at', '>=', $thirtyDaysAgo)
                ->count(),

            'completed' => Task::where('type', Task::TYPE_DEBITUR)
                ->whereNotNull('completed_at')
                ->where('created_at', '>=', $thirtyDaysAgo)
                ->count(),
        ];

        $taskStats['total'] = $taskStats['pending'] + $taskStats['near_deadline'] +
            $taskStats['overdue'] + $taskStats['completed'];

        return Inertia::render('project/DebtorSavings', [
            'debtor_savings' => $debtSavings,
            'users' => $users,
            'categories' => $categories,
            'users_summary' => $usersSummary,
            'task_stats' => $taskStats
        ]);
    }

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

        $validated['type'] = Task::TYPE_DEBITUR;

        $debtSavings = Task::create($validated);
        if ($request->has('users')) {
            $debtSavings->users()->sync($request->users);
        }
        return redirect()->route('debtor-savings.index')->with('success', 'Debitur menabung berhasil disimpan');
    }

    public function update(Request $request, Task $debtorSaving)
    {
        if ($debtorSaving->type !== Task::TYPE_DEBITUR) {
            return back()->withErrors(['message' => 'Tidak dapat mengubah karena bukan debitur menabung.']);
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

        if($request->has('completed_at')) {
            $validated['completed_at'] = $request->completed_at ? now() : null;
        }
        $debtorSaving->update(Arr::except($validated, ['users']));

        if($request->has('users')) {
            $debtorSaving->users()->sync($validated['users'] ?? []);
        }
        return redirect()->route('debtor-savings.index')->with('success', 'Debitur menabung berhasil diupdate.');
    }

    public function destroy(Task $debtorSaving)
    {
        if ($debtorSaving->type !== Task::TYPE_DEBITUR) {
            return back()->withErrors(['message' => 'Tidak dapat menghapus karena bukan debitur menabung.']);
        }

        try {
            $debtorSaving->users()->detach();
            $debtorSaving->delete();
            return back()->with('success', 'Debitur menabung berhasil dihapus.');
        } catch (\Exception $exception) {
            return back()->withErrors(['message' => 'Gagal menghapus debitur menabung: ' . $exception->getMessage()]);
        }
    }
}
