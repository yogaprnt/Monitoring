@extends('layouts.app')

@section('title', 'Detail Approval - RI CCSL')

@push('styles')
<style>
    .modal-backdrop-custom { background-color: rgba(15, 23, 42, 0.58); backdrop-filter: blur(3px); }
    .label-field { font-size: 11px; font-weight: 600; letter-spacing: 0.06em; color: #94a3b8; text-transform: uppercase; margin-bottom: 4px; }
    .value-field { font-size: 14px; color: #1e293b; font-weight: 500; }
</style>
@endpush

@section('content')
@php
    $user      = auth()->user();
    $roleName  = optional($user->role)->name ?? 'unknown';
    $isManager = str_contains($roleName, 'manager') && !str_contains($roleName, 'asisten');

    $dashboardRoute = $isManager ? 'manager.dashboard'        : 'asisten_manager.dashboard';
    $approvalRoute  = $isManager ? 'manager.approve'          : 'asisten_manager.approve';
    $approveRoute   = $isManager ? 'manager.approve.data'     : 'asisten_manager.item.approve';
    $rejectRoute    = $isManager ? 'manager.reject.data'      : 'asisten_manager.item.reject';

    $backRoute = route($approvalRoute);

    $statusMap = [
        'submitted'           => ['label' => 'Submitted',        'bg' => 'bg-yellow-50', 'text' => 'text-yellow-700', 'border' => 'border-yellow-250', 'icon' => 'fa-clock'],
        'reviewed'            => ['label' => 'Menunggu Manager', 'bg' => 'bg-blue-50',   'text' => 'text-blue-700',   'border' => 'border-blue-250', 'icon' => 'fa-hourglass-half'],
        'approved'            => ['label' => 'Approved',         'bg' => 'bg-green-50',  'text' => 'text-green-700',  'border' => 'border-green-250', 'icon' => 'fa-check-circle'],
        'rejected_by_asman'   => ['label' => 'Ditolak Asman',    'bg' => 'bg-red-50',    'text' => 'text-red-650',    'border' => 'border-red-250', 'icon' => 'fa-times-circle'],
        'rejected_by_manager' => ['label' => 'Ditolak Manager',  'bg' => 'bg-red-50',    'text' => 'text-red-650',    'border' => 'border-red-250', 'icon' => 'fa-times-circle'],
    ];
    $st = $statusMap[$approval->status] ?? ['label' => ucfirst($approval->status), 'bg' => 'bg-gray-100', 'text' => 'text-gray-605', 'border' => 'border-gray-200', 'icon' => 'fa-circle'];

    $tipeColor = match(strtolower($tipe)) {
        'bisnis'     => 'bg-blue-50 text-blue-700 border-blue-200',
        'pengabdian' => 'bg-green-50 text-green-700 border-green-200',
        'akademik'   => 'bg-yellow-50 text-yellow-700 border-yellow-200',
        'inovasi'    => 'bg-orange-50 text-orange-700 border-orange-200',
        default      => 'bg-purple-50 text-purple-700 border-purple-200',
    };

    $skip = ['id','judul','status','created_at','updated_at','deleted_at',
             'penginput_id','input_by',
             'asisten_manager_approved_by','asisten_manager_approved_at',
             'manager_approved_by','manager_approved_at','catatan_reject',
             'coe','target','realisasi','file_pendukung','file','periode'];

    $nama    = $approval->penginput->name ?? 'Unknown';
    $inisial = strtoupper(implode('', array_map(fn($w) => $w[0], array_slice(explode(' ', $nama), 0, 2))));

    $target = \App\Models\MasterTarget::where('periode', $approval->periode)
        ->where('kategori', ucfirst($tipe))
        ->where('judul', $approval->judul)
        ->value('target') ?? 0;

    $realisasi = $approval->realisasi ?? null;
    $tercapai  = ($target !== null && $realisasi !== null && $realisasi >= $target);

    $filePath = $approval->file_pendukung ?? $approval->file ?? null;
    $fileUrl  = $filePath ? asset('storage/' . $filePath) : null;
    $fileName = $filePath ? basename($filePath) : null;
@endphp

<div class="content">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm text-gray-400 mb-6">
        <a href="{{ $backRoute }}" class="hover:text-gray-650 transition font-medium" style="text-decoration:none">Approval</a>
        <i class="fas fa-chevron-right text-xs"></i>
        <span class="text-gray-700 font-semibold">Detail Data</span>
    </div>

    @if(session('success'))
    <div class="bg-green-100 border border-green-300 text-green-700 px-4 py-3 rounded-lg mb-5 text-sm">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded-lg mb-5 text-sm">
        <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
    </div>
    @endif
    @if($errors->any())
    <div class="bg-red-100 border border-red-350 text-red-700 px-4 py-3 rounded-lg mb-5 text-sm">
        <div class="font-semibold mb-1"><i class="fas fa-exclamation-triangle mr-2"></i>Data belum dapat diproses.</div>
        <ul class="list-disc ml-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- KIRI --}}
        <div class="lg:col-span-2 space-y-4">

            {{-- Card 1: Informasi Umum --}}
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-150">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600">
                            <i class="fas fa-info-circle text-sm"></i>
                        </div>
                        <span class="font-bold text-gray-700 text-sm">Informasi Umum</span>
                    </div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold border {{ $st['bg'] }} {{ $st['text'] }} {{ $st['border'] ?? 'border-current/20' }}">
                        <i class="fas {{ $st['icon'] }} text-[10px]"></i>{{ $st['label'] }}
                    </span>
                </div>

                <div class="px-6 py-5 space-y-5">

                    {{-- Row 1: Periode, Jenis, Status --}}
                    <div class="grid grid-cols-3 gap-6">
                        <div>
                            <p class="label-field">Periode</p>
                            <p class="value-field">{{ $approval->periode ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="label-field">Jenis Data</p>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold border {{ $tipeColor }}">
                                <i class="fas fa-tag text-[10px]"></i>{{ ucfirst($tipe) }}
                            </span>
                        </div>
                        <div>
                            <p class="label-field">Status</p>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold border {{ $st['bg'] }} {{ $st['text'] }} {{ $st['border'] ?? 'border-current/20' }}">
                                <i class="fas {{ $st['icon'] }} text-[10px]"></i>{{ $st['label'] }}
                            </span>
                        </div>
                    </div>

                    {{-- Judul --}}
                    <div>
                        <p class="label-field">Judul / Nama Data</p>
                        <p class="text-base font-bold text-gray-800">{{ $approval->judul ?? '-' }}</p>
                    </div>

                    {{-- Row 2: Penginput, COE, Tanggal --}}
                    <div class="grid grid-cols-3 gap-6">
                        <div>
                            <p class="label-field">Penginput</p>
                            <div class="flex items-center gap-2 mt-1">
                                <div class="w-7 h-7 rounded-full bg-[#1a2e4a] flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                                    {{ $inisial }}
                                </div>
                                <span class="value-field">{{ $nama }}</span>
                            </div>
                        </div>
                        @if($approval->coe ?? false)
                        <div>
                            <p class="label-field">COE</p>
                            <p class="value-field">{{ $approval->coe }}</p>
                        </div>
                        @endif
                        <div>
                            <p class="label-field">Tanggal Input</p>
                            <p class="value-field">{{ optional($approval->created_at)->format('d M Y') }}</p>
                        </div>
                    </div>

                    {{-- Row 3: Target, Realisasi, File Pendukung --}}
                    <div class="grid grid-cols-3 gap-6">
                        @if($target !== null)
                        <div>
                            <p class="label-field text-blue-600 font-bold">Target (Overall)</p>
                            <p class="text-lg font-bold text-gray-800">{{ $target }}</p>
                        </div>
                        @endif
                        @if($realisasi !== null)
                        <div>
                            <p class="label-field">Realisasi</p>
                            <div class="flex items-center gap-2">
                                <p class="text-lg font-bold {{ $tercapai ? 'text-green-600' : 'text-red-500' }}">{{ $realisasi }}</p>
                                @if($tercapai)
                                    <span class="text-xs text-green-600 font-semibold bg-green-50 px-2 py-0.5 rounded-full">✓ Tercapai</span>
                                @else
                                    <span class="text-xs text-red-500 font-semibold bg-red-50 px-2 py-0.5 rounded-full">✗ Belum tercapai</span>
                                @endif
                            </div>
                        </div>
                        @endif
                        <div>
                            <p class="label-field">File Pendukung</p>
                            @if($fileUrl)
                                <a href="{{ $fileUrl }}" target="_blank"
                                   class="inline-flex items-center gap-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 px-3 py-1.5 rounded-lg text-xs font-semibold transition" style="text-decoration:none">
                                    <i class="fas fa-paperclip text-[10px]"></i>
                                    <span class="truncate max-w-[120px]" title="{{ $fileName }}">Lihat File</span>
                                    <i class="fas fa-external-link-alt text-[9px]"></i>
                                </a>
                            @else
                                <p class="text-sm text-gray-400 italic">Tidak ada file</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card 2: Detail Konten --}}
            @php
                $extraFields = collect($approval->getAttributes())
                    ->filter(fn($v, $k) => !in_array($k, $skip) && !is_null($v) && $v !== '');
            @endphp
            @if($extraFields->isNotEmpty())
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-150">
                    <div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-650">
                        <i class="fas fa-file-alt text-sm"></i>
                    </div>
                    <span class="font-bold text-gray-700 text-sm">Detail Konten Data</span>
                </div>
                <div class="px-6 py-5">
                    <div class="grid grid-cols-2 gap-x-8 gap-y-5">
                        @foreach($extraFields as $key => $value)
                        <div>
                            <p class="label-field">{{ ucwords(str_replace('_', ' ', $key)) }}</p>
                            <p class="value-field">{{ $value }}</p>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            {{-- Card 3: Catatan Reject --}}
            @if($approval->catatan_reject)
            <div class="bg-red-50 rounded-2xl border border-red-200 shadow-sm overflow-hidden">
                <div class="flex items-center gap-3 px-6 py-4 border-b border-red-200">
                    <div class="w-8 h-8 rounded-lg bg-red-100 flex items-center justify-center text-red-500">
                        <i class="fas fa-exclamation-circle text-sm"></i>
                    </div>
                    <span class="font-bold text-red-700 text-sm">Catatan Penolakan</span>
                </div>
                <div class="px-6 py-5">
                    <p class="text-sm text-red-600 leading-relaxed">{{ $approval->catatan_reject }}</p>
                </div>
            </div>
            @endif

        </div>

        {{-- KANAN --}}
        <div class="space-y-4">

            {{-- Riwayat Approval --}}
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-150">
                    <div class="w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center text-gray-400">
                        <i class="fas fa-history text-sm"></i>
                    </div>
                    <span class="font-bold text-gray-700 text-sm">Riwayat Approval</span>
                </div>
                <div class="px-6 py-5">
                    <ol class="relative border-l border-gray-200 space-y-6 ml-2">

                        <li class="ml-5">
                            <span class="absolute -left-3 flex items-center justify-center w-6 h-6 rounded-full bg-green-100 ring-4 ring-white">
                                <i class="fas fa-check text-green-600 text-[10px]"></i>
                            </span>
                            <p class="text-xs font-semibold text-gray-700">Input Staff</p>
                            <p class="text-xs text-gray-500 mt-0.5">{{ $nama }}</p>
                            <p class="text-xs text-gray-400">{{ optional($approval->created_at)->format('d M Y, H:i') }}</p>
                        </li>

                        <li class="ml-5">
                            @if($approval->asisten_manager_approved_at)
                                <span class="absolute -left-3 flex items-center justify-center w-6 h-6 rounded-full bg-green-100 ring-4 ring-white">
                                    <i class="fas fa-check text-green-600 text-[10px]"></i>
                                </span>
                                <p class="text-xs font-semibold text-gray-700">Disetujui Asisten Manager</p>
                                <p class="text-xs text-gray-500 mt-0.5">{{ optional($approval->asistenManagerApprover)->name ?? '-' }}</p>
                                <p class="text-xs text-gray-400">{{ optional($approval->asisten_manager_approved_at)->format('d M Y, H:i') }}</p>
                            @elseif(str_contains($approval->status, 'rejected_by_asman'))
                                <span class="absolute -left-3 flex items-center justify-center w-6 h-6 rounded-full bg-red-100 ring-4 ring-white">
                                    <i class="fas fa-times text-red-500 text-[10px]"></i>
                                </span>
                                <p class="text-xs font-semibold text-red-600">Ditolak Asisten Manager</p>
                            @else
                                <span class="absolute -left-3 flex items-center justify-center w-6 h-6 rounded-full bg-yellow-100 ring-4 ring-white">
                                    <i class="fas fa-clock text-yellow-500 text-[10px]"></i>
                                </span>
                                <p class="text-xs font-semibold text-gray-500">Menunggu Asisten Manager</p>
                            @endif
                        </li>

                        <li class="ml-5">
                            @if($approval->manager_approved_at)
                                <span class="absolute -left-3 flex items-center justify-center w-6 h-6 rounded-full bg-green-100 ring-4 ring-white">
                                    <i class="fas fa-check text-green-600 text-[10px]"></i>
                                </span>
                                <p class="text-xs font-semibold text-gray-700">Disetujui Manager</p>
                                <p class="text-xs text-gray-500 mt-0.5">{{ optional($approval->managerApprover)->name ?? '-' }}</p>
                                <p class="text-xs text-gray-400">{{ optional($approval->manager_approved_at)->format('d M Y, H:i') }}</p>
                            @elseif(str_contains($approval->status, 'rejected_by_manager'))
                                <span class="absolute -left-3 flex items-center justify-center w-6 h-6 rounded-full bg-red-100 ring-4 ring-white">
                                    <i class="fas fa-times text-red-500 text-[10px]"></i>
                                </span>
                                <p class="text-xs font-semibold text-red-600">Ditolak Manager</p>
                            @else
                                <span class="absolute -left-3 flex items-center justify-center w-6 h-6 rounded-full bg-yellow-100 ring-4 ring-white">
                                    <i class="fas fa-clock text-yellow-500 text-[10px]"></i>
                                </span>
                                <p class="text-xs font-semibold text-gray-500">Menunggu Manager</p>
                                <p class="text-xs text-gray-400">Belum diputuskan</p>
                            @endif
                        </li>

                    </ol>
                </div>
            </div>

            {{-- Keputusan --}}
            @if(in_array($approval->status, ['submitted', 'reviewed']))
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-150">
                    <div class="w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center text-gray-400">
                        <i class="fas fa-gavel text-sm"></i>
                    </div>
                    <span class="font-bold text-gray-700 text-sm">Keputusan</span>
                </div>
                <div class="px-6 py-5 space-y-3">
                    <p class="text-xs text-gray-400 mb-1">Pilih tindakan untuk data ini.</p>

                    <form action="{{ route($approveRoute, ['tipe' => $tipe, 'id' => $approval->id]) }}"
                          method="POST"
                          onsubmit="return confirm('Yakin ingin menyetujui data ini?')">
                        @csrf
                        <button type="submit"
                                class="w-full inline-flex items-center justify-center gap-2 bg-green-500 hover:bg-green-600 active:scale-95 text-white px-4 py-2.5 rounded-xl text-sm font-bold transition-all border-0 cursor-pointer">
                            <i class="fas fa-check-double"></i>
                            {{ $isManager ? 'Approve Final' : 'Approve & Teruskan' }}
                        </button>
                    </form>

                    <button type="button"
                            data-judul="{{ $approval->judul }}"
                            data-reject-url="{{ route($rejectRoute, ['tipe' => $tipe, 'id' => $approval->id]) }}"
                            onclick="openRejectModal(this)"
                            class="w-full inline-flex items-center justify-center gap-2 bg-red-500 hover:bg-red-600 active:scale-95 text-white px-4 py-2.5 rounded-xl text-sm font-bold transition-all border-0 cursor-pointer">
                        <i class="fas fa-times-circle"></i>Tolak Data
                    </button>

                    <a href="{{ $backRoute }}"
                       class="w-full inline-flex items-center justify-center gap-2 border border-gray-200 text-gray-500 hover:bg-gray-50 px-4 py-2.5 rounded-xl text-sm font-bold transition text-center" style="text-decoration:none">
                        <i class="fas fa-arrow-left text-xs"></i>Kembali
                    </a>
                </div>
            </div>
            @else
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm px-6 py-5">
                <a href="{{ $backRoute }}"
                   class="w-full inline-flex items-center justify-center gap-2 border border-gray-200 text-gray-500 hover:bg-gray-50 px-4 py-2.5 rounded-xl text-sm font-bold transition text-center" style="text-decoration:none">
                    <i class="fas fa-arrow-left text-xs"></i>Kembali ke Daftar
                </a>
            </div>
            @endif

        </div>
    </div>
</div>

{{-- MODAL REJECT --}}
<div id="rejectModal"
     class="hidden fixed inset-0 z-[100] modal-backdrop-custom items-center justify-center px-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-150">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-full bg-red-100 text-red-600 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-times"></i>
                </div>
                <div>
                    <h3 class="font-bold text-gray-800">Tolak Data</h3>
                    <p class="text-sm text-gray-500 mt-0.5">Data akan dikembalikan kepada Staff untuk diperbaiki.</p>
                </div>
            </div>
        </div>
        <form id="rejectForm" method="POST">
            @csrf
            <div class="px-6 py-5 space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Data yang ditolak</label>
                    <div id="rejectJudul" class="bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-700">-</div>
                </div>
                <div>
                    <label for="catatan_reject" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">
                        Alasan Penolakan <span class="text-red-500">*</span>
                    </label>
                    <textarea id="catatan_reject" name="catatan_reject" rows="4" maxlength="1000" required
                              placeholder="Jelaskan bagian yang harus diperbaiki..."
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-400 resize-none"></textarea>
                    <p class="text-xs text-gray-400 mt-1">Alasan ini akan ditampilkan kepada Staff.</p>
                </div>
            </div>
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-3">
                <button type="button" onclick="closeRejectModal()"
                        class="px-4 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-100 transition border-0 cursor-pointer">
                    Batal
                </button>
                <button type="submit"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm text-white bg-red-600 hover:bg-red-700 rounded-lg transition border-0 cursor-pointer font-bold">
                    <i class="fas fa-times-circle"></i>Ya, Tolak Data
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openRejectModal(btn) {
    document.getElementById('rejectForm').action       = btn.dataset.rejectUrl;
    document.getElementById('rejectJudul').textContent = btn.dataset.judul || '-';
    document.getElementById('catatan_reject').value    = '';
    const m = document.getElementById('rejectModal');
    m.classList.remove('hidden'); m.classList.add('flex');
    document.body.style.overflow = 'hidden';
    setTimeout(() => document.getElementById('catatan_reject').focus(), 100);
}
function closeRejectModal() {
    const m = document.getElementById('rejectModal');
    m.classList.add('hidden'); m.classList.remove('flex');
    document.body.style.overflow = '';
}
document.getElementById('rejectModal').addEventListener('click', e => { if (e.target === e.currentTarget) closeRejectModal(); });
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeRejectModal(); });
</script>
@endpush