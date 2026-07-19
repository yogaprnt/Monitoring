@extends('layouts.app')

@section('title', 'Master Target RI - RI CCSL')

@push('styles')
<style>
    .badge-pengabdian { background:#dcfce7; color:#166534; }
    .badge-riset { background:#ede9fe; color:#5b21b6; }
    .badge-bisnis { background:#dbeafe; color:#1e40af; }
    .badge-akademik { background:#fef3c7; color:#92400e; }
    .badge-inovasi { background:#ffedd5; color:#9a3412; }
</style>
@endpush

@section('content')
@php
    $isEdit     = isset($item);
    $periodeParts     = $isEdit && isset($item->periode) ? explode(' ', $item->periode) : [];
    $selectedTriwulan = old('triwulan', $periodeParts[0] ?? '');
    $selectedTahun    = old('tahun',    $periodeParts[1] ?? '');
    $selectedKategori = old('kategori', $item->kategori ?? '');
    $selectedJudul    = old('judul',    $item->judul    ?? '');
@endphp

<div class="content">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Data Master Target RI</h1>
        <p class="text-gray-500 text-sm mt-1">Kelola target kinerja RI CCSL secara keseluruhan (tanpa COE dan realisasi)</p>
    </div>

    {{-- FLASH --}}
    @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 text-sm">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 text-sm">
        <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
    </div>
    @endif

    {{-- FORM TAMBAH/EDIT --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 px-6 py-4 mb-6">
        <div class="flex items-center justify-between mb-4">
            <span class="font-semibold text-gray-700">{{ $isEdit ? 'Edit Master Target' : 'Tambah Master Target Baru' }}</span>
            @if(!$isEdit)
            <button onclick="toggleForm()" id="btnTambah"
                class="bg-[#0470D4] hover:bg-[#05C5F5] text-white text-sm px-4 py-2 rounded-lg flex items-center space-x-2 transition">
                <i class="fas fa-plus text-xs"></i><span>Tambah Target</span>
            </button>
            @endif
        </div>

        <div id="formTambah" class="{{ $isEdit || $errors->any() ? '' : 'hidden' }}">
            <form action="{{ $isEdit ? route('master-target.update', $item->id) : route('master-target.store') }}"
                  method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                @if($isEdit) @method('PUT') @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    {{-- Triwulan --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Triwulan</label>
                        <select name="triwulan" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#1a2e4a]">
                            <option value="">-- Pilih TW --</option>
                            @foreach(['TW1','TW2','TW3','TW4'] as $tw)
                            <option value="{{ $tw }}" {{ $selectedTriwulan == $tw ? 'selected' : '' }}>{{ $tw }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Tahun --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tahun</label>
                        <select name="tahun" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#1a2e4a]">
                            <option value="">-- Pilih Tahun --</option>
                            @for($t = date('Y')-2; $t <= date('Y')+5; $t++)
                            <option value="{{ $t }}" {{ $selectedTahun == $t ? 'selected' : '' }}>{{ $t }}</option>
                            @endfor
                        </select>
                    </div>

                    {{-- Kategori --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                        <select name="kategori" id="kategoriSelect" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#1a2e4a]"
                            onchange="updateJudul()">
                            <option value="">-- Pilih Kategori --</option>
                            @foreach($kategoriList as $kat)
                            <option value="{{ $kat }}" {{ $selectedKategori == $kat ? 'selected' : '' }}>{{ $kat }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Judul/Indikator --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Judul / Indikator</label>
                        <select name="judul" id="judulSelect" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#1a2e4a]">
                            <option value="">-- Pilih Kategori dulu --</option>
                            @if($selectedKategori && isset($judulAll[$selectedKategori]))
                                @foreach($judulAll[$selectedKategori] as $j)
                                <option value="{{ $j }}" {{ $selectedJudul == $j ? 'selected' : '' }}>{{ $j }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    {{-- Target --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Target</label>
                        <input type="number" name="target" min="0" placeholder="0" required
                            value="{{ old('target', $item->target ?? '') }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#1a2e4a]" />
                    </div>

                </div>

                {{-- Validation errors --}}
                @if($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded text-sm">
                    <ul class="list-disc ml-4">
                        @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
                    </ul>
                </div>
                @endif

                <div class="flex items-center space-x-3 pt-1">
                    <button type="submit"
                        class="px-5 py-2 text-sm text-white bg-green-600 hover:bg-green-700 rounded-lg flex items-center space-x-2">
                        <i class="fas fa-save text-xs"></i>
                        <span>{{ $isEdit ? 'Update' : 'Simpan' }}</span>
                    </button>
                    @if(!$isEdit)
                    <button type="button" onclick="toggleForm()"
                        class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 flex items-center space-x-2">
                        <i class="fas fa-times text-xs"></i><span>Batal</span>
                    </button>
                    @else
                    <a href="{{ route('master-target.index') }}"
                        class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 flex items-center space-x-2">
                        <i class="fas fa-arrow-left text-xs"></i><span>Kembali</span>
                    </a>
                    @endif
                </div>

            </form>
        </div>
    </div>

    {{-- TABEL DAFTAR MASTER TARGET --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-gray-700">Daftar Master Target RI</h2>
                <p class="text-xs text-gray-400 mt-0.5">Data target kinerja RI CCSL yang telah ditetapkan</p>
            </div>
            <span class="bg-blue-50 text-blue-700 text-xs font-semibold px-3 py-1.5 rounded-lg">
                {{ $items->count() }} data
            </span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-[#1a2e4a] text-white">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium">Periode</th>
                        <th class="px-4 py-3 text-left font-medium">Kategori</th>
                        <th class="px-4 py-3 text-left font-medium">Judul / Indikator</th>
                        <th class="px-4 py-3 text-center font-medium">Target</th>
                        <th class="px-4 py-3 text-center font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($items as $mt)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3 font-semibold text-gray-700 whitespace-nowrap">{{ $mt->periode }}</td>
                        <td class="px-4 py-3">
                            @php
                                $badgeClass = match($mt->kategori) {
                                    'Riset'      => 'badge-riset',
                                    'Bisnis'     => 'badge-bisnis',
                                    'Akademik'   => 'badge-akademik',
                                    'Pengabdian' => 'badge-pengabdian',
                                    'Inovasi'    => 'badge-inovasi',
                                    default      => 'bg-gray-100 text-gray-600',
                                };
                            @endphp
                            <span class="inline-block {{ $badgeClass }} text-xs font-semibold px-2.5 py-1 rounded-full">
                                {{ $mt->kategori }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-700 max-w-xs">
                            <div class="truncate" title="{{ $mt->judul }}">{{ $mt->judul }}</div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="font-bold text-blue-700 text-base">{{ number_format($mt->target) }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('master-target.edit', $mt->id) }}"
                                   class="inline-flex items-center gap-1 bg-yellow-50 text-yellow-700 border border-yellow-200 text-xs font-semibold px-2.5 py-1.5 rounded-lg hover:bg-yellow-100 transition">
                                    <i class="fas fa-edit text-[10px]"></i> Edit
                                </a>
                                <form action="{{ route('master-target.destroy', $mt->id) }}" method="POST"
                                      onsubmit="return confirm('Yakin hapus master target ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="inline-flex items-center gap-1 bg-red-50 text-red-700 border border-red-200 text-xs font-semibold px-2.5 py-1.5 rounded-lg hover:bg-red-100 transition border-0 cursor-pointer">
                                        <i class="fas fa-trash text-[10px]"></i> Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                            <i class="fas fa-bullseye text-4xl mb-3 block text-gray-300"></i>
                            <div class="font-medium text-gray-500">Belum ada master target.</div>
                            <div class="text-xs mt-1">Klik tombol "Tambah Target" untuk menambahkan.</div>
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
const judulData = @json($judulAll);
const selectedJudul = "{{ $selectedJudul }}";

function updateJudul() {
    const kat     = document.getElementById('kategoriSelect').value;
    const sel     = document.getElementById('judulSelect');
    sel.innerHTML = '<option value="">-- Pilih Judul/Indikator --</option>';
    if (kat && judulData[kat]) {
        judulData[kat].forEach(j => {
            const opt = document.createElement('option');
            opt.value = j;
            opt.textContent = j;
            if (j === selectedJudul) opt.selected = true;
            sel.appendChild(opt);
        });
    }
}

function toggleForm() {
    const f = document.getElementById('formTambah');
    const b = document.getElementById('btnTambah');
    f.classList.toggle('hidden');
    b.classList.toggle('hidden');
}

document.addEventListener('DOMContentLoaded', function () {
    const kat = document.getElementById('kategoriSelect').value;
    if (kat) updateJudul();
});
</script>
@endpush
