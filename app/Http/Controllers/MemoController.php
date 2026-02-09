<?php

namespace App\Http\Controllers;

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
        $users = User::select('id', 'name')->get();
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

        $usersSummary = User::select('id', 'name', 'avatar', 'position')->orderBy('name')
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
            'received_at' => ['required', 'date', 'date_format:Y-m-d H:i:s'],
            'users' => ['nullable', 'array'],
            'users.*' => ['exists:users,id'],
            'document_link' => ['nullable', 'url', 'max:2048'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'due_date' => ['nullable', 'date', 'date_format:Y-m-d H:i:s'],
            'origin' => ['max:255'],
            'reference_number' => ['max:255'],
            'subject' => ['max:255'],
            'follow_up_note' => ['nullable', 'string', 'max:255']
        ]);

        $memo = Memo::create($validated);
        if ($request->has('users')) {
            $memo->users()->sync($request->users);
        }
        return redirect()->route('memos.index')->with('success', 'Memo berhasil dibuat');
    }

    /**
     * Display the specified resource.
     */
    public function show(Memo $memo)
    {

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Memo $memo)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Memo $memo)
    {
        $validated = $request->validate([
            'received_at' => ['sometimes', 'required', 'date', 'date_format:Y-m-d H:i:s'],
            'origin' => ['sometimes', 'max:255', 'string', 'nullable'],
            'reference_number' => ['max:255', 'string', 'nullable'],
            'users' => ['nullable', 'array'],
            'users.*' => ['integer', 'exists:users,id'],
            'due_date' => ['nullable', 'date', 'date_format:Y-m-d H:i:s'],
            'document_link' => ['nullable', 'url', 'max:2048'],
            'subject' => ['max:255', 'string', 'nullable'],
            'category_id' => ['sometimes', 'required', 'integer', 'exists:categories,id'],
            'follow_up_note' => ['max:255', 'string', 'nullable'],
            'completed_at' => ['nullable', 'date', 'date_format:Y-m-d H:i:s']
        ]);

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
        $users = User::select('id', 'name')->get();

        $sortBy = $request->sort_by ?? 'received_at';
        $sortDir = strtolower($request->sort_dir ?? 'desc') === 'asc' ? 'asc' : 'desc';

        $memos = Memo::query()
            ->with(['users', 'category'])
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
