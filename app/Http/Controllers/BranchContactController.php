<?php

namespace App\Http\Controllers;

use App\Http\Requests\BranchContactRequest;
use App\Models\BranchContact;
use App\Models\Regional;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class BranchContactController extends Controller
{

    public function index()
    {
        $contacts = Regional::with('branchContacts')
            ->orderBy('regionals.name')
            ->get();

        return Inertia::render('contact_cluster/BranchContactIndex', [
            'contacts' => $contacts,
        ]);
    }


    public function store(BranchContactRequest $request)
    {
        $validated = $request->validated();
        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $validated['avatar'] = $path;
        }

        BranchContact::create($validated);

        return back()->with('success', 'Kontak berhasil ditambahkan');
    }

    public function update(BranchContactRequest $request, BranchContact $branchContact)
    {
        $validated = $request->validated();

        if ($request->hasFile('avatar')) {
            if ($branchContact->avatar && \Storage::disk('public')->exists($branchContact->avatar)) {
                \Storage::disk('public')->delete($branchContact->avatar);
            }

            $path = $request->file('avatar')->store('avatars', 'public');
            $validated['avatar'] = $path;
        } elseif ($request->input('remove_avatar') == 1) {
            Log::channel('stderr')->info('Removing avatar for BranchContact ID: ' . $branchContact->id);
            if ($branchContact->avatar) {
                \Storage::disk('public')->delete($branchContact->avatar);
                $validated['avatar'] = null;
            }
        }

        $branchContact->update($validated);

        return back()->with('success', 'Kontak berhasil diperbarui');
    }

    public function destroy(BranchContact $branchContact)
    {
        try {
            if ($branchContact->avatar && \Storage::disk('public')->exists($branchContact->avatar)){
                \Storage::disk('public')->delete($branchContact->avatar);
            }
            $branchContact->delete();
            return back()->with('success', 'Kontak berhasil dihapus');
        }catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus kontak: ' . $e->getMessage());
        }
    }
}
