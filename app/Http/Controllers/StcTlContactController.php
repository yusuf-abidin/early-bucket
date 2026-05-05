<?php

namespace App\Http\Controllers;

use App\Http\Requests\StcTlContactRequest;
use App\Models\StcTlContact;
use Illuminate\Http\Request;

class StcTlContactController extends Controller
{

    /**
     * Store a newly created resource in storage.
     */
    public function store(StcTlContactRequest $request)
    {
        $validated = $request->validated();
        $contact = StcTlContact::create($validated);
        $contact->categories()->sync($request->categories);
        return back()->with('success', 'Kontak berhasil ditambahkan.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StcTlContactRequest $request, StcTlContact $stcTlContact)
    {
        $validated = $request->validated();
        $stcTlContact->update($validated);
        $stcTlContact->categories()->sync($request->categories);
        return back()->with('success', 'Kontak berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StcTlContact $stcTlContact)
    {
        try {
            $stcTlContact->categories()->detach();
            $stcTlContact->delete();
            return back()->with('success', 'Kontak berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus kontak: ' . $e->getMessage());
        }
    }
}
