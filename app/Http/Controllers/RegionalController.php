<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Regional;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RegionalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $regionals = Regional::query()
            ->select('id', 'name')

            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")

                    ->orWhereHas('areas', function ($aq) use ($search) {
                        $aq->where('name', 'like', "%{$search}%");
                    })

                    ->orWhereHas('branches', function ($bq) use ($search) {
                        $bq->whereNull('area_id')
                            ->where('name', 'like', "%{$search}%");
                    })

                    ->orWhereHas('areas.branches', function ($bq) use ($search) {
                        $bq->where('name', 'like', "%{$search}%");
                    });
                });
            })

            ->with([
                'areas' => function ($aq) {
                    $aq->select('id', 'name', 'regional_id')
                        ->orderBy('name');
                },
                'branches' => function ($bq) {
                    $bq->select('id', 'name', 'regional_id', 'area_id')
                        ->whereNull('area_id')
                        ->orderBy('name');
                },
                'areas.branches' => function ($bq) {
                    $bq->select('id', 'name', 'area_id', 'regional_id')->orderBy('name');
                }
            ])

            ->orderBy('name')
            ->get();

        $allRegionals = Regional::query()
            ->select('id', 'name')
            ->orderBy('name')
            ->with([
                'areas' => function ($query) {
                    $query->select('id', 'name', 'regional_id')
                        ->orderBy('name');
                }
            ])
            ->get();

        return Inertia::render('admin/area_cabang/Area', [
            'regionals' => $regionals,
            'search' => $search,
            'all_regionals' => $allRegionals,
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
        $request->validate([
            'name' => 'required|string|max:255',
        ], [
            'name.required' => 'Nama regional harus diisi',
            'name.string' => 'Nama regional harus berupa teks',
            'name.max' => 'Maksimal 255 karakter',
        ]);

        Regional::create($request->only('name'));
        return back()->with('success', 'Regional berhasil dibuat.');
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Regional $regional)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ], [
            'name.required' => 'Nama regional harus diisi',
            'name.string' => 'Nama regional harus berupa teks',
            'name.max' => 'Maksimal 255 karakter',
        ]);

        $regional->update($request->only('name'));
        return back()->with('success', 'Regional berhasil diubah.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Regional $regional)
    {
        //
    }
}
