<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMemoRequest;
use App\Http\Requests\UpdateMemoRequest;
use App\Models\Category;
use App\Models\Memo;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Inertia\Inertia;

class MemoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $users = User::select('id', 'name')
            ->whereIn('role', ['admin', 'user'])
            ->get();
        $categories = Category::select(['id', 'name', 'type'])->where('categories.type', 'sifat_memo')->with('color')->get();
        $totalArchive= Memo::archived()->count();

        $sortBy = $request->sort_by ?? 'received_at';
        $sortDir = strtolower($request->sort_dir ?? 'desc') === 'asc' ? 'asc' : 'desc';

        $memos =  Memo::query()
            ->with(['users', 'category'])
            ->active()
            ->filterAndSort($request)
            ->applySort($sortBy, $sortDir)
            ->paginate(20)
            ->withQueryString();

        $usersSummary = User::select('id', 'name', 'avatar', 'position')
            ->whereIn('role', ['admin', 'user'])->orderBy('name')
            ->withCount([
                'memos as pending_count' => function (Builder $query) {
                    $query->whereNull('completed_at')
                        ->whereDate('due_date', '>', today()->addDays(3));
                },
                'memos as overdue_count' => function (Builder $query) {
                    $query->whereNull('completed_at')
                        ->where('due_date', '<', now());
                },
                'memos as near_overdue_count' => function (Builder $query) {
                    $query->whereNull('completed_at')
                        ->whereDate('due_date', '>=', today())
                        ->whereDate('due_date', '<=', today()->addDays(3));
                }
            ])->get();

        return Inertia::render('main/memo/Index', [
            'users' => $users,
            'memos' => $memos,
            'categories' => $categories,
            'users_summary' => $usersSummary,
            'total_archive' => $totalArchive,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMemoRequest $request)
    {
        $validated = $request->validated();

        $memo = Memo::create($validated);
        if ($request->has('users')) {
            $memo->users()->sync($request->users);
        }
        return redirect()->route('memos.index')->with('success', 'Memo berhasil dibuat');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMemoRequest $request, Memo $memo)
    {
        $validated = $request->validated();

        if($request->has('completed_at')) {
            $validated['completed_at'] = $request->completed_at ? now() : null;
        }

        $memo->update(Arr::except($validated, ['users']));

        if($request->has('users')) {
            $memo->users()->sync($validated['users'] ?? []);
        }

        return back()->with('success', 'Memo berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Memo $memo)
    {
        try {
            $memo->users()->detach();
            $memo->delete();
            return back()->with('success', 'Memo berhasil dihapus.');
        }catch (\Exception $exception){
            return back()->withErrors(['message' => 'gagal menghapus memo: ' . $exception->getMessage()]);
        }
    }

    public function archive(Request $request) {
        $users = User::select('id', 'name')
            ->whereIn('role', ['admin', 'user']
            )->get();

        $sortBy = $request->sort_by ?? 'received_at';
        $sortDir = strtolower($request->sort_dir ?? 'desc') === 'asc' ? 'asc' : 'desc';

        $memos = Memo::query()
            ->with(['users', 'category' => function ($query) {
                $query->withTrashed();
            }])
            ->archived()
            ->filterAndSort($request)
            ->applySort($sortBy, $sortDir)
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('main/memo/Archive', [
            'users' => $users,
            'memos' => $memos,
        ]);
    }
}
