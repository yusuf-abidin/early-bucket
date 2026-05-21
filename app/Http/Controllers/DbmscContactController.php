<?php

namespace App\Http\Controllers;

use App\Http\Requests\DbmscContactRequest;
use App\Models\DbmscContact;
use Illuminate\Http\Request;

class DbmscContactController extends Controller
{

    public function store(DbmscContactRequest $request)
    {
        $validated = $request->validated();

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $validated['avatar'] = $path;
        }

        DbmscContact::create($validated);

        return back()->with('success', 'Kontak berhasil ditambahkan.');
    }

    public function update(DbmscContactRequest $request, DbmscContact $dbmscContact)
    {
        $validated = $request->validated();

        if ($request->hasFile('avatar')) {
            if ($dbmscContact->avatar && \Storage::disk('public')->exists($dbmscContact)) {
                \Storage::disk('public')->delete($dbmscContact->avatar);
            }

            $path = $request->file('avatar')->store('avatars', 'public');
            $validated['avatar'] = $path;
        }elseif ($request->input('remove_avatar') == 1) {
            if ($dbmscContact->avatar) {
                \Storage::disk('public')->delete($dbmscContact->avatar);
                $validated['avatar'] = null;
            }
        }

        $dbmscContact->update($validated);

        return back()->with('success', 'Kontak berhasil diperbarui.');
    }

    public function destroy(DbmscContact $dbmscContact)
    {
        try {
            if ($dbmscContact->avatar && \Storage::disk('public')->exists($dbmscContact->avatar)) {
                \Storage::disk('public')->delete($dbmscContact->avatar);
            }
            $dbmscContact->delete();
            return back()->with('success', 'Kontak berhasil dihapus');
        }catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus kontak: ' . $e->getMessage());
        }
    }
}
