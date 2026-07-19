@extends('layouts.app')

@section('title', 'RI-CCSL - Laporan Sistem')

@section('content')
<div class="content">
    <div class="page-title text-2xl font-bold text-gray-800 mb-1">Laporan Aktivitas Pengguna</div>
    <div class="page-sub text-gray-400 text-sm mb-6">Riwayat aktivitas seluruh pengguna sistem</div>

    {{-- Tabel Aktivitas --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden" style="margin-top: 15px;">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-700" style="font-size: 15px;">Daftar Aktivitas Pengguna</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm" style="border-collapse: collapse;">
                <thead>
                    <tr class="bg-[#1a2e4a] text-white text-xs uppercase tracking-wider">
                        <th class="px-6 py-3.5 text-left font-semibold">Nama Pengguna</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Role</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Aktivitas</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Waktu Aktivitas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($aktivitasPengguna as $aktivitas)
                    <tr class="hover:bg-gray-50/50 transition">
                        {{-- Nama Pengguna --}}
                        <td class="px-6 py-4 font-medium text-gray-800">
                            {{ $aktivitas->user->name ?? 'Tidak Dikenal' }}
                        </td>

                        {{-- Role --}}
                        <td class="px-6 py-4">
                            <span class="{{ $aktivitas->roleColor }} px-2.5 py-1 rounded-full text-xs font-semibold">
                                {{ ucfirst(optional($aktivitas->user->role)->name ?? 'N/A') }}
                            </span>
                        </td>

                        {{-- Aktivitas --}}
                        <td class="px-6 py-4">
                            <span class="{{ $aktivitas->aktColor }} px-2.5 py-1 rounded-full text-xs font-semibold">
                                {{ $aktivitas->aktivitas }}
                            </span>
                        </td>

                        {{-- Waktu Aktivitas --}}
                        <td class="px-6 py-4 text-gray-500">
                            {{ \Carbon\Carbon::parse($aktivitas->waktu_aktivitas)->format('d F Y, H:i') }} WIB
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-gray-400">
                            <i class="fas fa-history text-3xl mb-3 block text-gray-300"></i>
                            Belum ada aktivitas tercatat.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection