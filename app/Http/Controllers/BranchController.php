<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBranchRequest;
use App\Http\Requests\UpdateBranchRequest;
use App\Models\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBranchRequest $request)
    {
        $validated = $request->validated();

        Branch::create($validated);
        return back()->with('success', 'Cabang berhasil dibuat.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBranchRequest $request, Branch $branch)
    {
        $validated = $request->validated();

        $branch->update($validated);
        return back()->with('success', 'Cabang berhasil diubah.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Branch $branch)
    {
        $branch->delete();
        return back()->with('success', 'Cabang berhasil dihapus.');
    }
}
