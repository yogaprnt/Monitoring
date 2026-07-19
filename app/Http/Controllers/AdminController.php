<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\AktivitasPengguna;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    /**
     * Halaman dashboard admin.
     */
    public function dashboard()
    {
        $users = User::with('role')->get();

        $totalUsers          = $users->count();
        $countAdmin          = $users->filter(fn($u) => strtolower(optional($u->role)->name) === 'admin')->count();
        $countManager        = $users->filter(fn($u) => strtolower(optional($u->role)->name) === 'manager')->count();
        $countStaff          = $users->filter(fn($u) => strtolower(optional($u->role)->name) === 'staff')->count();
        $countDekan          = $users->filter(fn($u) => strtolower(optional($u->role)->name) === 'dekan')->count();
        $countAsistenManager = $users->filter(fn($u) => strtolower(optional($u->role)->name) === 'asisten_manager')->count();

        $recentUsers = User::with('role')->latest()->take(5)->get();

        return view('admin.dashboard', compact(
            'totalUsers',
            'countAdmin',
            'countManager',
            'countStaff',
            'countDekan',
            'countAsistenManager',
            'recentUsers'
        ));
    }

    /**
     * Toggle status user via AJAX.
     */
    public function ubahStatus(User $user)
    {
        try {
            $user->status = strtolower($user->status) === 'aktif' ? 'tidak aktif' : 'aktif';
            $user->save();

            return response()->json([
                'success' => true,
                'status'  => $user->status,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengubah status pengguna.',
            ], 500);
        }
    }

    /**
     * Approve user.
     */
    public function approveUser($userId)
    {
        $user = User::findOrFail($userId);
        $user->status = 'approved';
        $user->save();

        AktivitasPengguna::create([
            'user_id'         => Auth::id(),
            'aktivitas'       => 'Approve',
            'waktu_aktivitas' => now(),
        ]);

        return redirect()->back()->with('status', 'User approved successfully!');
    }

    /**
     * Halaman laporan.
     */
    public function laporan()
    {
        $roleColors = [
            'staff'           => 'bg-blue-100 text-blue-700',
            'manager'         => 'bg-green-100 text-green-700',
            'asisten_manager' => 'bg-purple-100 text-purple-700',
            'admin'           => 'bg-red-100 text-red-700',
            'dekan'           => 'bg-orange-100 text-orange-700',
        ];

        $aktivitasColors = [
            'Login'      => 'bg-green-50 text-green-700',
            'Logout'     => 'bg-gray-100 text-gray-600',
            'Input Data' => 'bg-blue-50 text-blue-700',
            'Approve'    => 'bg-emerald-50 text-emerald-700',
            'Reject'     => 'bg-red-50 text-red-700',
        ];

        $aktivitasPengguna = AktivitasPengguna::with('user.role')->latest()->get()
            ->map(function ($item) use ($roleColors, $aktivitasColors) {
                $role = strtolower(optional($item->user->role)->name ?? '');
                $item->roleColor = $roleColors[$role] ?? 'bg-gray-100 text-gray-600';
                $item->aktColor  = $aktivitasColors[$item->aktivitas] ?? 'bg-gray-50 text-gray-600';
                return $item;
            });

        return view('Admin.laporan', compact('aktivitasPengguna'));
    }

    /**
     * Logout.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
