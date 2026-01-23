<?php

namespace App\Http\Controllers;

use App\Models\Color;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class AdminController extends Controller
{
    public function users()
    {
        $users = User::orderBy('name', 'asc')->get();

        return Inertia::render('admin/users/Index', [
            'users' => $users
        ]);
    }

    public function create()
    {
        $colors = Color::select(['id', 'name', 'class'])->get();
        return Inertia::render('admin/users/Create', [
            'colors' => $colors
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'position' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', 'in:admin,user'],
            'color_id' => ['nullable', 'exists:colors,id'],
            'avatar' => ['nullable', 'image', 'max:1024', 'mimes:jpg,jpeg,png'],
        ]);

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

        User::create($userData);
        return to_route('admin.users.index')->with('success', 'User successfully created.');
    }

    public function edit(User $user)
    {
        $user = User::find($user->id);
        $colors = Color::select(['id', 'name', 'class'])->get();
        return Inertia::render('admin/users/Edit', [
            'user' => $user,
            'colors' => $colors
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'position' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', 'in:admin,user'],
            'color_id' => ['nullable', 'exists:colors,id'],
            'avatar' => ['nullable', 'image', 'max:1024', 'mimes:jpg,jpeg,png'],
        ]);

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

        return redirect()->route('admin.users.index')->with('success', 'User successfully updated.');
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
