<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('role')->orderBy('id')->get();

        return view('admin.userkelola', compact('users'));
    }

    public function edit(User $user)
    {
        $roles = Role::orderBy('name')->get();
        return view('admin.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => [
                'required',
                'email',
                \Illuminate\Validation\Rule::unique('users', 'email')->ignore($user->id)->whereNull('deleted_at')
            ],
            'role_id' => 'required|exists:roles,id',
        ]);

        $user->update($request->only('name', 'email', 'role_id'));

        return redirect()->route('admin.user')
            ->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.user')
                ->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        $user->delete();

        return redirect()->route('admin.user')
            ->with('success', 'Pengguna berhasil dihapus.');
    }

    public function ubahStatus(User $user)
    {
        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak dapat menonaktifkan akun sendiri.',
            ], 403);
        }

        $user->status = strtolower($user->status) === 'aktif' ? 'tidak aktif' : 'aktif';
        $user->save();

        return response()->json([
            'success' => true,
            'status'  => $user->status,
            'message' => 'Status berhasil diperbarui.',
        ]);
    }
}
