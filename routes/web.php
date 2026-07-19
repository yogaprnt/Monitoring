<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DekanController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\AsistenManagerController;
use App\Http\Controllers\RisetController;
use App\Http\Controllers\BisnisController;
use App\Http\Controllers\PengabdianController;
use App\Http\Controllers\AkademikController;
use App\Http\Controllers\InovasiController;
use App\Http\Controllers\MasterTargetController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', fn() => redirect()->route('login'));

// Auth
Route::get('/login',    [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login',   [AuthController::class, 'login'])->name('login.post');
Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');

// Logout
Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/');
})->name('logout');

// ─── Staff ────────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'check.staff'])->prefix('staff')->group(function () {
    Route::get('/dashboard', [StaffController::class, 'dashboard'])->name('staff.dashboard');

    // Master Target RI
    Route::get('/master-target',           [MasterTargetController::class, 'index'])->name('master-target.index');
    Route::post('/master-target',          [MasterTargetController::class, 'store'])->name('master-target.store');
    Route::get('/master-target/{id}/edit', [MasterTargetController::class, 'edit'])->name('master-target.edit');
    Route::put('/master-target/{id}',      [MasterTargetController::class, 'update'])->name('master-target.update');
    Route::delete('/master-target/{id}',   [MasterTargetController::class, 'destroy'])->name('master-target.destroy');

    foreach (['riset', 'bisnis', 'pengabdian', 'akademik', 'inovasi'] as $module) {
        $ctrl = match ($module) {
            'riset'      => RisetController::class,
            'bisnis'     => BisnisController::class,
            'pengabdian' => PengabdianController::class,
            'akademik'   => AkademikController::class,
            'inovasi'    => InovasiController::class,
        };
        Route::get("/$module",           [$ctrl, 'index'])->name("$module.index");
        Route::post("/$module",          [$ctrl, 'store'])->name("$module.store");
        Route::get("/$module/{id}/edit", [$ctrl, 'edit'])->name("$module.edit");
        Route::put("/$module/{id}",      [$ctrl, 'update'])->name("$module.update");
        Route::delete("/$module/{id}",   [$ctrl, 'destroy'])->name("$module.destroy");
    }
});

// ─── Manager ──────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'check.manager'])->prefix('manager')->group(function () {
    Route::get('/dashboard', [ManagerController::class, 'dashboard'])->name('manager.dashboard');
    Route::get('/approve',   [ManagerController::class, 'approve'])->name('manager.approve');

    // generik: {tipe} = riset | bisnis | pengabdian | akademik | inovasi
    Route::get('/approve/{tipe}/{id}/detail',   [ManagerController::class, 'detail'])->name('manager.approve.detail');
    Route::post('/approve/{tipe}/{id}/approve', [ManagerController::class, 'approveData'])->name('manager.approve.data');
    Route::post('/approve/{tipe}/{id}/reject',  [ManagerController::class, 'rejectData'])->name('manager.reject.data');
});

// ─── Admin ────────────────────────────────────────────────────────────────────
Route::middleware(['auth'])->prefix('admin')->group(function () {
    Route::get('/',              [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/laporan',       [AdminController::class, 'laporan'])->name('admin.laporan');
    Route::get('/user',          [UserController::class, 'index'])->name('admin.user');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('admin.users.edit');
    Route::put('/users/{user}',      [UserController::class, 'update'])->name('admin.users.update');
    Route::delete('/users/{user}',   [UserController::class, 'destroy'])->name('admin.users.destroy');
    // toggle status — satu route saja (hapus duplikat)
    Route::post('/users/{user}/status', [UserController::class, 'ubahStatus'])->name('admin.users.ubah-status');
});

// ─── Dekan ────────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'check.dekan'])->prefix('dekan')->group(function () {
    Route::get('/dashboard',             [DekanController::class, 'dashboard'])->name('dekan.dashboard');
    Route::get('/kinerjacoe',            [DekanController::class, 'kinerjaCoe'])->name('dekan.kinerjacoe');
    Route::get('/laporan',               [DekanController::class, 'laporan'])->name('dekan.laporan');
    Route::get('/laporan/export-csv',    [DekanController::class, 'exportCsv'])->name('dekan.laporan.exportCsv');
    Route::get('/laporan/export-pdf',    [DekanController::class, 'exportPdf'])->name('dekan.laporan.exportPdf');
    Route::get('/laporan/export-excel',  [DekanController::class, 'exportExcel'])->name('dekan.laporan.exportExcel');
    Route::get('/laporan/download/{id}', [DekanController::class, 'downloadLaporan'])->name('dekan.laporan.download');
});

// ─── Asisten Manager ─────────────────────────────────────────────────────────
Route::middleware(['auth', 'check.asisten_manager'])
    ->prefix('asisten-manager')
    ->group(function () {
        Route::get('/dashboard', [AsistenManagerController::class, 'dashboard'])
            ->name('asisten_manager.dashboard');

        Route::get('/approval', [AsistenManagerController::class, 'approve'])
            ->name('asisten_manager.approve');

        // generik: {tipe} = riset | bisnis | pengabdian | akademik | inovasi
        // FIX: ubah 'detailItem' -> 'detail' agar sesuai nama method di controller
        Route::get('/approval/{tipe}/{id}/detail',   [AsistenManagerController::class, 'detail'])
            ->name('asisten_manager.item.detail');

        Route::post('/approval/{tipe}/{id}/approve', [AsistenManagerController::class, 'approveItem'])
            ->name('asisten_manager.item.approve');

        Route::post('/approval/{tipe}/{id}/reject',  [AsistenManagerController::class, 'rejectItem'])
            ->name('asisten_manager.item.reject');
    });
