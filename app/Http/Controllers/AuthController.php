<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $remember = $request->has('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            $user = Auth::user();

            // Cek status user
            if (strtolower($user->status) !== 'aktif') {
                Auth::logout();
                return back()->withErrors([
                    'username' => 'Akun Anda tidak aktif. Hubungi admin.',
                ])->onlyInput('username');
            }

            $roleName = optional($user->role)->name;

            Log::info('User login: ' . $user->username . ' dengan role: ' . $roleName);

            if ($roleName === 'manager') {
                return redirect()->route('manager.dashboard');
            }

            if ($roleName === 'staff') {
                return redirect()->route('staff.dashboard');
            }

            if ($roleName === 'admin') {
                return redirect()->route('admin.dashboard');
            }

            if ($roleName === 'dekan') {
                return redirect()->route('dekan.dashboard');
            }

            if ($roleName === 'asisten_manager') {
                return redirect()->route('asisten_manager.dashboard');
            }

            Auth::logout();
            return redirect()->route('login')->withErrors([
                'access' => 'Role pengguna tidak dikenali. Hubungi admin.',
            ]);
        }

        return back()->withErrors([
            'username' => 'Username atau password salah.',
        ])->onlyInput('username');
    }

    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'username' => 'required|string|max:255',
            'email'    => 'required|email|max:255',
            'password' => 'required|string|confirmed|min:8',
            'role'     => 'required|string|in:admin,manager,staff,dekan,asisten_manager',
        ]);

        // Cek apakah email/username terdaftar pada akun aktif (bukan soft-deleted)
        if (User::where('email', $request->email)->exists()) {
            return back()->withErrors(['email' => 'Email ini sudah terdaftar pada akun aktif.'])->withInput();
        }
        if (User::where('username', $request->username)->exists()) {
            return back()->withErrors(['username' => 'Username ini sudah terdaftar pada akun aktif.'])->withInput();
        }

        $redirectTo = trim($request->input('redirect_to', ''));

        Log::info('Input role dari request: ' . $request->role);
        Log::info('redirect_to value: [' . $redirectTo . ']');

        $normalizedRole = strtolower($request->role);

        $role = Role::whereRaw('LOWER(name) = ?', [$normalizedRole])->first();

        if (!$role) {
            Log::error('Role tidak ditemukan untuk: ' . $normalizedRole);
            return back()->withErrors(['role' => 'Role tidak valid.'])->withInput();
        }

        try {
            // Cari apakah akun soft-deleted dengan email atau username yang sama sudah ada
            $user = User::withTrashed()
                ->where(function($query) use ($request) {
                    $query->where('email', $request->email)
                          ->orWhere('username', $request->username);
                })
                ->first();

            if ($user) {
                // Restore akun lama dan update datanya dengan data baru
                $user->restore();
                $user->update([
                    'name'     => $request->name,
                    'username' => $request->username,
                    'email'    => $request->email,
                    'password' => Hash::make($request->password),
                    'role_id'  => $role->id,
                    'status'   => 'aktif',
                ]);
                Log::info('User lama di-restore: ' . $user->username);
            } else {
                // Buat baru jika belum pernah terdaftar sama sekali
                $user = User::create([
                    'name'     => $request->name,
                    'username' => $request->username,
                    'email'    => $request->email,
                    'password' => Hash::make($request->password),
                    'role_id'  => $role->id,
                    'status'   => 'aktif',
                ]);
                Log::info('User baru dibuat: ' . $user->username);
            }

            if ($redirectTo === 'admin') {
                return redirect()->route('admin.user')->with('success', 'User berhasil ditambahkan!');
            }

            return redirect()->route('login')->with('success', 'Akun berhasil dibuat! Silakan login.');
        } catch (\Exception $e) {
            Log::error('Error saat membuat/merestore user: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Terjadi kesalahan saat registrasi.'])->withInput();
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah logout.');
    }
}
