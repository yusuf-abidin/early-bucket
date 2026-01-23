<?php

namespace App\Http\Controllers;

use App\Models\Area;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AreaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $areas = Area::query()
            ->select('id', 'name')
            ->when($search, function ($query) use ($search){
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                    ->orWhereHas('branches', function ($bq) use ($search) {
                        $bq->where('name', 'like', "%{$search}%");
                    });
                });
            })
            ->with(['branches' => function ($query) use ($search) {
                $query->select('id', 'name', 'area_id')->orderBy('name', 'asc');
            }])
            ->orderBy('name', 'asc')
            ->get();

        $allAreas = Area::select('id', 'name')->orderBy('name', 'asc')->get();

        return Inertia::render('admin/area_cabang/Area', [
            'areas' => $areas,
            'search' => $search,
            'all_areas' => $allAreas,
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
        ]);
        Area::create($request->only('name'));
        return back()->with('success', 'Area berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Area $area)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Area $area)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Area $area)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $area->update($request->only('name'));
        return back()->with('success', 'Area berhasil diubah.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Area $area)
    {
        try {
            $area->delete();
            return back()->with('success', 'Area berhasil dihapus.');
        }catch (\Illuminate\Database\QueryException $e) {
            // Cek jika error disebabkan oleh relasi database (Integrity constraint violation)
            if ($e->getCode() === "23000") {
                return back()->withErrors([
                    'message' => 'Area tidak bisa dihapus karena masih memiliki data cabang terkait.'
                ]);
            }

            return back()->withErrors(['message' => 'Terjadi kesalahan pada database.']);
        } catch (\Exception $e) {
            return back()->withErrors(['message' => 'Maaf, terjadi kendala teknis. Silakan coba lagi nanti.']);
        }
    }
}
