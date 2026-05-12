<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminStoreUserRequest;
use App\Http\Requests\AdminUpdateUserRequest;
use App\Models\Color;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class AdminController extends Controller
{
    public function users()
    {
        $users = User::orderBy('name', 'asc')
            ->whereIn('role', ['admin', 'user'])->get();

        return Inertia::render('admin/users/Index', [
            'users' => $users,
            'scope' => 'central'
        ]);
    }

    public function create()
    {
        $colors = Color::select(['id', 'name', 'class'])->get();
        return Inertia::render('admin/users/Create', [
            'colors' => $colors,
            'scope' => 'central',
        ]);
    }

    public function store(AdminStoreUserRequest $request)
    {
        $validated = $request->validated();

        $userData = [
            'name' => $validated['name'],
            'position' => $validated['position'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'color_id' => $validated['color_id'] ?? null,
        ];

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $userData['avatar'] = $path;
        }

        $user =User::create($userData);
        if ($user->role === 'rlqh') {
            return to_route('admin.rlqh.users.index')->with('success', 'Pengguna berhasil dibuat');
        }
        return to_route('admin.users.index')->with('success', 'Pengguna berhasil dibuat');
    }

    public function edit(User $user)
    {
        $user = User::find($user->id);
        $colors = Color::select(['id', 'name', 'class'])->get();
        return Inertia::render('admin/users/Edit', [
            'user' => $user,
            'colors' => $colors,
            'scope' => 'central',
        ]);
    }

    public function update(AdminUpdateUserRequest $request, User $user)
    {
        $validated = $request->validated();

        $user->name = $validated['name'];
        $user->position = $validated['position'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];
        $user->color_id = $validated['color_id'] ?? null;

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        if ($request->hasFile('avatar')) {
            // Hapus avatar lama jika ada
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $path;
        }

        $user->save();

        if ($user->role === 'rlqh') {
            return redirect()->route('admin.rlqh.users.index')->with('success', 'Pengguna berhasil diperbarui');
        }

        return redirect()->route('admin.users.index')->with('success', 'Pengguna berhasil diperbarui');
    }


    public function destroy(User $user)
    {
        try {
            $user->delete();
            return redirect()->route('admin.users.index')->with('success', 'Pengguna Berhasil dihapus');
        }catch (\Exception $exception){
            return back()->withErrors(['message' => 'Gagal menghapus pengguna: ' . $exception->getMessage()]);
        }
    }
}
