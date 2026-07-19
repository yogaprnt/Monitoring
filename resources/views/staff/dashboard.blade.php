@extends('layouts.app')

@section('title', 'Dashboard Staff - RI CCSL')

@section('content')
<div class="content">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Overview Dashboard</h1>
        <p class="text-gray-400 text-sm mt-1">Ringkasan performa kinerja periode terkini</p>
    </div>

    {{-- Overview Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">

        {{-- Card 1: Total Diinput --}}
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-200">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-medium text-gray-500">Dokumen Sudah Diinput</h3>
                <span class="w-9 h-9 rounded-full bg-green-50 flex items-center justify-center">
                    <i class="fas fa-file-alt text-green-500 text-sm"></i>
                </span>
            </div>
            <p class="text-3xl font-bold text-gray-800">{{ $dokumenSudahDiinput }}</p>
            <p class="text-xs text-gray-400 mt-1">Total semua jenis dokumen</p>
        </div>

        {{-- Card 2: Approved Final --}}
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-200">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-medium text-gray-500">Total Dokumen Approved</h3>
                <span class="w-9 h-9 rounded-full bg-blue-50 flex items-center justify-center">
                    <i class="fas fa-check-double text-blue-500 text-sm"></i>
                </span>
            </div>
            <p class="text-3xl font-bold text-green-600">{{ $totalDokumenApproved }}</p>
            <p class="text-xs text-gray-400 mt-1">Disetujui final oleh Manager</p>
        </div>

        {{-- Card 3: Tertunda --}}
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-200">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-medium text-gray-500">Dokumen Tertunda</h3>
                <span class="w-9 h-9 rounded-full bg-yellow-50 flex items-center justify-center">
                    <i class="fas fa-clock text-yellow-500 text-sm"></i>
                </span>
            </div>
            <p class="text-3xl font-bold text-yellow-500">{{ $dokumenTertunda }}</p>
            <p class="text-xs text-gray-400 mt-1">Masih dalam proses review</p>
        </div>

    </div>

    {{-- Tabel Dokumen Diproses --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-gray-700">Dokumen yang Perlu Diproses</h2>
                <p class="text-xs text-gray-400 mt-0.5">Dokumen yang sedang dalam proses review atau ditolak</p>
            </div>
            <span class="bg-gray-100 text-gray-600 text-xs font-semibold px-3 py-1.5 rounded-lg">
                {{ $dokumenDiproses->count() }} Dokumen
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-[#1a2e4a] text-white">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-medium">Nama Dokumen</th>
                        <th class="px-6 py-3 text-left text-sm font-medium">Jenis Data</th>
                        <th class="px-6 py-3 text-left text-sm font-medium">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($dokumenDiproses as $item)
                    <tr class="hover:bg-gray-50 transition">

                        {{-- Nama / Judul --}}
                        <td class="px-6 py-4 text-gray-800 font-medium max-w-xs">
                            <div class="truncate" title="{{ $item->judul }}">{{ $item->judul }}</div>
                        </td>

                        {{-- Jenis Data --}}
                        <td class="px-6 py-4">
                            @php
                                $jenisBadge = match($item->jenis_data) {
                                    'Bisnis'     => 'bg-blue-100 text-blue-700',
                                    'Pengabdian' => 'bg-green-100 text-green-700',
                                    'Akademik'   => 'bg-yellow-100 text-yellow-700',
                                    'Inovasi'    => 'bg-orange-100 text-orange-700',
                                    default      => 'bg-purple-100 text-purple-700', // Riset
                                };
                            @endphp
                            <span class="inline-flex items-center gap-1 {{ $jenisBadge }} text-xs font-semibold px-2.5 py-1 rounded-full">
                                {{ $item->jenis_data }}
                            </span>
                        </td>

                        {{-- Status --}}
                        <td class="px-6 py-4">
                            @if($item->status === 'submitted')
                                <span class="inline-flex items-center gap-1.5 bg-yellow-100 text-yellow-700 text-xs font-semibold px-2.5 py-1 rounded-full">
                                    <i class="fas fa-clock text-[10px]"></i>Menunggu Asisten Manager
                                </span>
                            @elseif($item->status === 'reviewed')
                                <span class="inline-flex items-center gap-1.5 bg-blue-100 text-blue-700 text-xs font-semibold px-2.5 py-1 rounded-full">
                                    <i class="fas fa-hourglass-half text-[10px]"></i>Menunggu Manager
                                </span>
                            @elseif($item->status === 'rejected_by_asman')
                                <span class="inline-flex items-center gap-1.5 bg-red-100 text-red-700 text-xs font-semibold px-2.5 py-1 rounded-full">
                                    <i class="fas fa-times-circle text-[10px]"></i>Ditolak Asisten Manager
                                </span>
                            @elseif($item->status === 'rejected_by_manager')
                                <span class="inline-flex items-center gap-1.5 bg-red-100 text-red-700 text-xs font-semibold px-2.5 py-1 rounded-full">
                                    <i class="fas fa-times-circle text-[10px]"></i>Ditolak Manager
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 bg-gray-100 text-gray-600 text-xs font-semibold px-2.5 py-1 rounded-full">
                                    {{ ucfirst($item->status) }}
                                </span>
                            @endif
                        </td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-6 py-12 text-center text-gray-400">
                            <i class="fas fa-folder-open text-4xl mb-3 block text-gray-300"></i>
                            <div class="font-medium text-gray-500">Semua dokumen sudah diproses.</div>
                            <div class="text-xs mt-1">Tidak ada dokumen yang sedang menunggu atau ditolak.</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection