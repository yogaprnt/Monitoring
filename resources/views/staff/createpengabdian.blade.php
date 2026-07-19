@extends('layouts.app')

@section('title', 'Kinerja Pengabdian - RI CCSL')

@push('styles')
<style>
    .badge-approved { background-color: #d1fae5; color: #065f46; }
    .badge-waiting  { background-color: #dbeafe; color: #1e40af; }
    .badge-reviewed { background-color: #ede9fe; color: #5b21b6; }
    .badge-rejected { background-color: #fee2e2; color: #991b1b; }
</style>
@endpush

@section('content')
@php
    $judulOptions = [
        "Pengelolaan dan peningkatan/internasionalisasi seminar/konferensi internasional",
        "Kontrak non-riset (pelatihan, jasa konsultansi, industri, komunitas, pemerintah, dll.)",
        "Community services (kolaborasi, CSR, dll.)",
        "Proposal abdimas DRTPM",
        "Proposal abdimas yang berkaitan dengan SDGs",
        "Pengelolaan dan peningkatan/internasionalisasi akreditasi jurnal ilmiah",
    ];
    $coeOptions = [
        "PUI-PT AICOM5","AIIS","AILO","IMSS","INTEREST","MREC","SmartCT","SMICS","STAR","STAR-RG","IS-DIGIT","CONNECTED"
    ];
    $isEdit = isset($item);
    $periodeParts = $isEdit && isset($item->periode) ? explode(' ', $item->periode) : [];
    $selectedTriwulan = old('triwulan', $periodeParts[0] ?? '');
    $selectedTahun = old('tahun', $periodeParts[1] ?? '');
@endphp

<div class="content">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Kinerja Pengabdian Masyarakat</h1>
        <p class="text-gray-500 text-sm mt-1">Kelola data pengabdian masyarakat yang sedang berjalan</p>
    </div>

    {{-- ALERT SUCCESS --}}
    @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 text-sm">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
    </div>
    @endif

    {{-- ALERT ERROR --}}
    @if(session('error'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 text-sm">
        <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
    </div>
    @endif

    {{-- FORM TAMBAH/EDIT --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 px-6 py-4 mb-4">
        <div class="flex items-center justify-between mb-4">
            <span class="font-semibold text-gray-700">{{ $isEdit ? 'Edit Pengabdian' : 'Tambah Kinerja Pengabdian Baru' }}</span>
            @if(!$isEdit)
                <button onclick="toggleForm()" id="btnTambah"
                    class="bg-[#0470D4] hover:bg-[#05C5F5] text-white text-sm px-4 py-2 rounded-lg flex items-center space-x-2 transition">
                    <i class="fas fa-plus text-xs"></i><span>Tambah Kinerja</span>
                </button>
            @endif
        </div>

        <div id="formTambah" class="{{ $isEdit || $errors->any() ? '' : 'hidden' }}">
            <form action="{{ $isEdit ? route('pengabdian.update', $item->id) : route('pengabdian.store') }}"
                  method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                @if($isEdit) @method('PUT') @endif

                @if($isEdit && $item->catatan_reject)
                <div class="bg-red-50 border border-red-200 rounded-lg px-4 py-3">
                    <div class="text-sm font-semibold text-red-700 mb-1">
                        <i class="fas fa-exclamation-circle mr-1"></i>
                        Alasan Penolakan
                    </div>
                    <p class="text-sm text-red-600">{{ $item->catatan_reject }}</p>
                </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Triwulan</label>
                        <select name="triwulan" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#1a2e4a]">
                            <option value="">-- Pilih TW --</option>
                            <option value="TW1" {{ $selectedTriwulan=='TW1'?'selected':'' }}>TW1</option>
                            <option value="TW2" {{ $selectedTriwulan=='TW2'?'selected':'' }}>TW2</option>
                            <option value="TW3" {{ $selectedTriwulan=='TW3'?'selected':'' }}>TW3</option>
                            <option value="TW4" {{ $selectedTriwulan=='TW4'?'selected':'' }}>TW4</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tahun</label>
                        <select name="tahun" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#1a2e4a]">
                            <option value="">-- Pilih Tahun --</option>
                            @for($tahun=date('Y')-2; $tahun<=date('Y')+5; $tahun++)
                                <option value="{{ $tahun }}" {{ $selectedTahun==$tahun?'selected':'' }}>{{ $tahun }}</option>
                            @endfor
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Judul Pengabdian</label>
                        <select name="judul" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#1a2e4a]">
                            <option value="">-- Pilih Judul Pengabdian --</option>
                            @foreach($judulOptions as $judul)
                                <option value="{{ $judul }}"
                                    {{ old('judul', $item->judul ?? '') == $judul ? 'selected' : '' }}>
                                    {{ $judul }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">COE</label>
                        <select name="coe" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#1a2e4a]">
                            <option value="">-- Pilih COE --</option>
                            @foreach($coeOptions as $coe)
                                <option value="{{ $coe }}"
                                    {{ old('coe', $item->coe ?? '') == $coe ? 'selected' : '' }}>
                                    {{ $coe }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Realisasi</label>
                        <input type="number" name="realisasi" min="0" placeholder="0" required
                            value="{{ old('realisasi', $item->realisasi ?? '') }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#1a2e4a]" />
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">File Pendukung</label>
                        <input type="file" name="file_pendukung" {{ !$isEdit ? 'required' : '' }}
                            accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#1a2e4a]" />
                        @if(isset($item) && $item->file_pendukung)
                            <a href="{{ asset('storage/'.$item->file_pendukung) }}" target="_blank"
                                class="inline-flex items-center mt-2 text-sm text-blue-600 hover:underline">
                                <i class="fas fa-file-alt mr-1"></i>Lihat file saat ini
                            </a>
                        @endif
                    </div>
                </div>

                <div class="flex items-center space-x-3 pt-1">
                    <button type="submit"
                        class="px-5 py-2 text-sm text-white bg-green-600 hover:bg-green-700 rounded-lg flex items-center space-x-2">
                        <i class="fas fa-save text-xs"></i>
                        <span>{{ $isEdit ? 'Update & Kirim Ulang' : 'Simpan' }}</span>
                    </button>
                    @if(!$isEdit)
                        <button type="button" onclick="toggleForm()"
                            class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 flex items-center space-x-2">
                            <i class="fas fa-times text-xs"></i><span>Batal</span>
                        </button>
                    @else
                        <a href="{{ route('pengabdian.index') }}"
                            class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 flex items-center space-x-2">
                            <i class="fas fa-arrow-left text-xs"></i><span>Kembali</span>
                        </a>
                    @endif
                </div>

            </form>
        </div>
    </div>

    {{-- TABEL DAFTAR PENGABDIAN --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-700">Data Kinerja Pengabdian Masyarakat</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-[#1a2e4a] text-white text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-3 text-left font-medium">Periode</th>
                        <th class="px-6 py-3 text-left font-medium">Judul Pengabdian</th>
                        <th class="px-6 py-3 text-left font-medium">COE</th>
                        <th class="px-6 py-3 text-left font-medium">Realisasi</th>
                        <th class="px-6 py-3 text-left font-medium">File</th>
                        <th class="px-6 py-3 text-left font-medium">Status</th>
                        <th class="px-6 py-3 text-left font-medium">Tanggal Input</th>
                        <th class="px-6 py-3 text-left font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($pengabdian as $data)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 font-medium text-gray-800">{{ $data->periode }}</td>
                        <td class="px-6 py-4 text-gray-700 max-w-xs">
                            <div class="truncate" title="{{ $data->judul }}">{{ $data->judul }}</div>
                        </td>
                        <td class="px-6 py-4 text-gray-700">{{ $data->coe ?? '-' }}</td>
                        <td class="px-6 py-4 text-gray-700">{{ $data->realisasi ?? '-' }}</td>
                        <td class="px-6 py-4 text-gray-700">
                            @if($data->file_pendukung)
                                <a href="{{ asset('storage/'.$data->file_pendukung) }}" target="_blank"
                                    class="text-blue-600 hover:underline">Lihat</a>
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($data->status === 'approved')
                                <span class="badge-approved px-2.5 py-1 rounded-full text-xs font-semibold">Approved</span>
                            @elseif($data->status === 'reviewed')
                                <span class="badge-reviewed px-2.5 py-1 rounded-full text-xs font-semibold">Menunggu Manager</span>
                            @elseif($data->status === 'submitted')
                                <span class="badge-waiting px-2.5 py-1 rounded-full text-xs font-semibold">Menunggu Asisten Manager</span>
                            @elseif($data->status === 'rejected_by_asman')
                                <div>
                                    <span class="badge-rejected px-2.5 py-1 rounded-full text-xs font-semibold">Ditolak Asisten Manager</span>
                                    @if($data->catatan_reject)
                                        <div class="mt-1 text-xs text-red-600 bg-red-50 border border-red-100 rounded p-1.5 max-w-xs">
                                            <strong>Catatan:</strong> {{ $data->catatan_reject }}
                                        </div>
                                    @endif
                                </div>
                            @elseif($data->status === 'rejected_by_manager')
                                <div>
                                    <span class="badge-rejected px-2.5 py-1 rounded-full text-xs font-semibold">Ditolak Manager</span>
                                    @if($data->catatan_reject)
                                        <div class="mt-1 text-xs text-red-600 bg-red-50 border border-red-100 rounded p-1.5 max-w-xs">
                                            <strong>Catatan:</strong> {{ $data->catatan_reject }}
                                        </div>
                                    @endif
                                </div>
                            @else
                                <span class="bg-gray-100 text-gray-600 px-2.5 py-1 rounded-full text-xs font-semibold">{{ $data->status }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-gray-500">{{ \Carbon\Carbon::parse($data->created_at)->format('d M Y') }}</td>
                        <td class="px-6 py-4">
                            @if($data->canBeModifiedByStaff())
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('pengabdian.edit', $data->id) }}"
                                        class="inline-flex items-center gap-1 bg-yellow-400 hover:bg-yellow-500 text-white px-3 py-1.5 rounded-lg text-xs font-medium transition text-center" style="text-decoration:none">
                                        <i class="fas fa-edit"></i><span>Edit</span>
                                    </a>
                                </div>
                            @else
                                <span class="text-xs text-gray-400 italic">
                                    @if($data->status === 'approved') Final @else Sedang diproses @endif
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-10 text-center text-gray-400">
                            <i class="fas fa-hands-helping text-3xl mb-2 block text-gray-300"></i>
                            Belum ada data pengabdian.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function toggleForm() {
    const f = document.getElementById('formTambah');
    const b = document.getElementById('btnTambah');
    f.classList.toggle('hidden');
    b.classList.toggle('hidden');
}
</script>
@endpush