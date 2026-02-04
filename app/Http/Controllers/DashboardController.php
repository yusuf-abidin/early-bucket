<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Memo;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index() {

        $totalAreas = Area::count();
        $totalBranches = Branch::count();

        // Pending Memo Statistics (Total)
        $now = now();
        $memoStats = Memo::selectRaw('
            count(*) as total,
            count(case when completed_at is null and due_date >= ? and due_date <= ? then 1 end) as approaching_deadline,
            count(case when completed_at is null and due_date < ? then 1 end) as overdue,
            count(case when completed_at is null and due_date > ? then 1 end) as pending
        ', [
            $now,
            $now->copy()->addDays(3),
            $now,
            $now->copy()->addDays(3)
        ])
            ->whereNull('completed_at')
            ->first();

        $pendingMemoData = [
            'total' => $memoStats->total ?? 0,
            'pending' => $memoStats->pending ?? 0,
            'approaching_deadline' => $memoStats->approaching_deadline ?? 0,
            'overdue' => $memoStats->overdue ?? 0,
        ];

        // Pending Matter Statistics (Total)
        $pendingMatterStats = Task::selectRaw('
            count(*) as total,
            count(case when completed_at is null and due_date >= ? and due_date <= ? then 1 end) as approaching_deadline,
            count(case when completed_at is null and due_date < ? then 1 end) as overdue,
            count(case when completed_at is null and due_date > ? then 1 end) as pending
        ', [
            $now,
            $now->copy()->addDays(3),
            $now,
            $now->copy()->addDays(3)
        ])
            ->whereNull('completed_at')
            ->where('type', Task::TYPE_PENDING)
            ->first();

        $pendingMatterData = [
            'total' => $pendingMatterStats->total ?? 0,
            'pending' => $pendingMatterStats->pending ?? 0,
            'approaching_deadline' => $pendingMatterStats->approaching_deadline ?? 0,
            'overdue' => $pendingMatterStats->overdue ?? 0,
        ];

        // Debitur Menabung Statistics (Total)
        $debiturStats = Task::selectRaw('
            count(*) as total,
            count(case when completed_at is not null then 1 end) as completed,
            count(case when completed_at is null and due_date >= ? and due_date <= ? then 1 end) as approaching_deadline,
            count(case when completed_at is null and due_date < ? then 1 end) as overdue,
            count(case when completed_at is null and due_date > ? then 1 end) as pending
        ', [
            $now,
            $now->copy()->addDays(3),
            $now,
            $now->copy()->addDays(3)
        ])
            ->where('type', Task::TYPE_DEBITUR)
            ->first();

        $debiturMenabungData = [
            'total' => $debiturStats->total ?? 0,
            'pending' => $debiturStats->pending ?? 0,
            'approaching_deadline' => $debiturStats->approaching_deadline ?? 0,
            'overdue' => $debiturStats->overdue ?? 0,
            'completed' => $debiturStats->completed ?? 0,
        ];

        return Inertia::render('Dashboard', [
            // General Overview Statistics
            'overview' => [
                'totalAreas' => $totalAreas,
                'totalBranches' => $totalBranches,
            ],

            // Pie Chart Data
            'pendingMemo' => $pendingMemoData,
            'pendingMatter' => $pendingMatterData,
            'debiturMenabung' => $debiturMenabungData,
        ]);
    }

    public function changeHero(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            return back()->withErrors(['message' => 'Anda tidak memiliki akses untuk mengubah hero.']);
        }

        try {
            $request->validate([
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:1024']
        ]);

            Storage::disk('public')->putFileAs(
                '',
                $request->file('image'),
                'early-bucket-hero.png'
            );

        return redirect()->route('dashboard')->with('success', 'Gambar berhasil diperbarui.');
        }catch (\Exception $exception){
            return back()->withErrors(['message' => 'Gagal mengubah hero: ' . $exception->getMessage()]);
        }

    }
}
