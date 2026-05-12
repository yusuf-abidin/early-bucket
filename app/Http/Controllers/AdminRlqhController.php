<?php

namespace App\Http\Controllers;

use App\Models\Color;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminRlqhController extends Controller
{
    public function users()
    {
        $users = User::orderBy('name')
            ->where('role', 'rlqh')->get();

        return Inertia::render('admin/rlqh/users/Index', [
            'users' => $users,
            'scope' => 'rlqh',
        ]);
    }

    public function create()
    {
        $colors = Color::select(['id', 'name', 'class'])->get();
        return Inertia::render('admin/rlqh/users/Create', [
            'colors' => $colors,
            'scope' => 'rlqh',
        ]);
    }

    public function edit(User $user)
    {
        $user = User::find($user->id);
        $colors = Color::select(['id', 'name', 'class'])->get();
        return Inertia::render('admin/rlqh/users/Edit', [
            'user' => $user,
            'colors' => $colors,
            'scope' => 'rlqh',
        ]);
    }

    public function destroy(User $user)
    {
        try {
            $user->delete();
            return redirect()->route('admin.rlqh.users.index')->with('success', 'Pengguna berhasil dihapus');
        } catch (\Exception $exception) {
            return back()->withErrors(['message' => 'Gagal menghapus pengguna: ' . $exception->getMessage()]);
        }
    }

}
