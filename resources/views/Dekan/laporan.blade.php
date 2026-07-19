@extends('layouts.app')

@section('title', 'Laporan Dekan - RI CCSL')

@push('styles')
<style>
    .filter-row { display:flex;gap:10px;flex-wrap:wrap;margin-bottom:1.5rem;align-items:center; }
    .filter-row label { font-size:12px;color:#6b7280; font-weight: 600; }
    .filter-row select { font-size:13px;padding:6px 10px;border:1px solid #d1d5db;border-radius:6px;background:#fff; font-family: inherit; }

    /* Premium Uniform Table Styling */
    .data-table, .dl-table { width:100%;border-collapse:collapse;font-size:13px; }
    .data-table th, .dl-table th { padding:12px 18px;text-align:left;font-weight:700;font-size:11px;color:#fff;background:#1a2e4a;text-transform:uppercase;letter-spacing:.6px; }
    .data-table td, .dl-table td { padding:14px 18px;border-bottom:1px solid #f1f5f9;color:#334155;vertical-align:middle; }
    .data-table tr:last-child td, .dl-table tr:last-child td { border-bottom:none; }
    .data-table tr:hover td, .dl-table tr:hover td { background:#f8fafc; transition: background-color 0.2s ease; }

    .badge { display:inline-flex;align-items:center;gap:4px;font-size:11px;padding:3px 9px;border-radius:99px;font-weight:600; }
    .badge-green { background:#dcfce7;color:#15803d; }
    .badge-red   { background:#fee2e2;color:#b91c1c; }
    .badge-gray  { background:#f3f4f6;color:#6b7280; }

    .progress-bar  { height:5px;border-radius:99px;background:#e5e7eb;overflow:hidden; }
    .progress-fill { height:100%;border-radius:99px; }

    .section-wrap { background:#fff;border:.5px solid #e5e7eb;border-radius:12px;padding:1.5rem;margin-bottom:1.25rem; }
    .section-title { font-size:14px;font-weight:600;color:#111827;margin-bottom:.2rem; }
    .section-sub   { font-size:12px;color:#6b7280;margin-bottom:1.25rem; }

    .empty-state { text-align:center;padding:3rem 1rem;color:#9ca3af; }
    .empty-state i { font-size:2rem;margin-bottom:.75rem;display:block; }

    .export-btn { display:inline-flex;align-items:center;gap:7px;font-size:13px;font-weight:600;padding:9px 18px;border-radius:8px;transition:all .15s;text-decoration:none; }
    .export-csv   { background:#2563eb;color:#fff; }
    .export-csv:hover   { background:#1d4ed8; }

    .download-btn { display:inline-flex;align-items:center;gap:6px;font-size:12px;font-weight:600;padding:7px 16px;border-radius:7px;background:#2563eb;color:#fff;text-decoration:none;transition:all .15s; }
    .download-btn:hover { background:#1d4ed8; }

    /* Pagination tabel */
    .pg-row { display:flex;align-items:center;justify-content:space-between;padding:11px 14px;border-top:.5px solid #e5e7eb;background:#f9fafb;font-size:12px;color:#6b7280; }
    .pg-btns { display:flex;gap:6px; }
    .pg-btn  { padding:5px 13px;border-radius:6px;border:1px solid #d1d5db;background:#fff;color:#374151;font-size:12px;cursor:pointer;transition:all .15s; }
    .pg-btn:hover:not(:disabled) { background:#1a2e4a;color:#fff;border-color:#1a2e4a; }
    .pg-btn:disabled { opacity:.4;cursor:not-allowed; }
</style>
@endpush

@section('content')
<div class="content">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Laporan</h1>
        <p class="text-gray-500 text-sm mt-1">Rekap realisasi dan target seluruh kategori berdasarkan data yang telah disetujui.</p>
    </div>

    {{-- ── FILTER ─────────────────────────────────────────────────── --}}
    <form method="GET" action="{{ route('dekan.laporan') }}" class="filter-row">
        <label>Filter:</label>

        <select name="tw" onchange="this.form.submit()">
            <option value="">Semua triwulan</option>
            @foreach($twList as $tw)
                <option value="{{ $tw }}" @selected($filterTw === $tw)>{{ $tw }}</option>
            @endforeach
        </select>

        <select name="tahun" onchange="this.form.submit()">
            <option value="">Semua tahun</option>
            @foreach($tahunList as $tahun)
                <option value="{{ $tahun }}" @selected($filterTahun === $tahun)>{{ $tahun }}</option>
            @endforeach
        </select>

        <select name="kategori" onchange="this.form.submit()">
            <option value="">Semua kategori</option>
            @foreach($kategoriList as $kat)
                <option value="{{ $kat }}" @selected($filterKategori === $kat)>{{ $kat }}</option>
            @endforeach
        </select>

        @if($filterTw || $filterTahun || $filterKategori)
            <a href="{{ route('dekan.laporan') }}"
               class="text-gray-400 hover:text-red-500 transition text-xs flex items-center gap-1">
                <i class="fas fa-times-circle"></i> Reset
            </a>
        @endif
    </form>

    {{-- ── SUMMARY CARDS ──────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-6">
        
        {{-- Total Realisasi --}}
        <div class="relative overflow-hidden bg-gradient-to-br from-[#378ADD] to-[#1e4875] text-white rounded-2xl p-6 shadow-lg border border-white/10 transition-all duration-300 hover:scale-[1.02] hover:shadow-xl" style="box-shadow: 0 10px 25px -10px rgba(55, 138, 221, 0.3)">
            <!-- Decorative background circles -->
            <div class="absolute -right-8 -top-8 w-20 h-20 bg-white/10 rounded-full blur-lg"></div>
            
            <div class="flex items-center justify-between relative z-10">
                <div>
                    <p class="text-[10px] font-bold text-white/70 uppercase tracking-widest mb-1">Total Realisasi</p>
                    <p class="text-3xl font-extrabold tracking-tight leading-none mb-1.5">{{ number_format($summary['totalRealisasi']) }}</p>
                    <span class="text-[10px] text-white/55 font-medium">dari target {{ number_format($summary['totalTarget']) }}</span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-white/15 flex items-center justify-center backdrop-blur-md border border-white/20 shadow-inner flex-shrink-0">
                    <i class="fas fa-chart-bar text-lg text-white"></i>
                </div>
            </div>
        </div>

        {{-- Deviasi Total --}}
        <div class="relative overflow-hidden bg-gradient-to-br from-[#1D9E75] to-[#0d503b] text-white rounded-2xl p-6 shadow-lg border border-white/10 transition-all duration-300 hover:scale-[1.02] hover:shadow-xl" style="box-shadow: 0 10px 25px -10px rgba(29, 158, 117, 0.3)">
            <!-- Decorative background circles -->
            <div class="absolute -right-8 -top-8 w-20 h-20 bg-white/10 rounded-full blur-lg"></div>
            
            <div class="flex items-center justify-between relative z-10">
                <div>
                    <p class="text-[10px] font-bold text-white/70 uppercase tracking-widest mb-1">Deviasi Total</p>
                    <p class="text-3xl font-extrabold tracking-tight leading-none mb-1.5">
                        {{ $summary['totalDeviasi'] >= 0 ? '+' : '' }}{{ number_format($summary['totalDeviasi']) }}
                    </p>
                    <span class="text-[10px] text-white/55 font-medium">{{ $summary['pct'] }}% capaian</span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-white/15 flex items-center justify-center backdrop-blur-md border border-white/20 shadow-inner flex-shrink-0">
                    <i class="fas fa-chart-line text-lg text-white"></i>
                </div>
            </div>
        </div>

        {{-- Periode Melampaui --}}
        <div class="relative overflow-hidden bg-gradient-to-br from-[#8b5cf6] to-[#581c87] text-white rounded-2xl p-6 shadow-lg border border-white/10 transition-all duration-300 hover:scale-[1.02] hover:shadow-xl" style="box-shadow: 0 10px 25px -10px rgba(139, 92, 246, 0.3)">
            <!-- Decorative background circles -->
            <div class="absolute -right-8 -top-8 w-20 h-20 bg-white/10 rounded-full blur-lg"></div>
            
            <div class="flex items-center justify-between relative z-10">
                <div>
                    <p class="text-[10px] font-bold text-white/70 uppercase tracking-widest mb-1">Periode Melampaui</p>
                    <p class="text-3xl font-extrabold tracking-tight leading-none mb-1.5">{{ $periodeOverTarget }}</p>
                    <span class="text-[10px] text-white/55 font-medium">dari {{ $daftarLaporan->count() }} laporan</span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-white/15 flex items-center justify-center backdrop-blur-md border border-white/20 shadow-inner flex-shrink-0">
                    <i class="fas fa-award text-lg text-white"></i>
                </div>
            </div>
        </div>

        {{-- Total Entri --}}
        <div class="relative overflow-hidden bg-gradient-to-br from-[#f59e0b] to-[#78350f] text-white rounded-2xl p-6 shadow-lg border border-white/10 transition-all duration-300 hover:scale-[1.02] hover:shadow-xl" style="box-shadow: 0 10px 25px -10px rgba(245, 158, 11, 0.3)">
            <!-- Decorative background circles -->
            <div class="absolute -right-8 -top-8 w-20 h-20 bg-white/10 rounded-full blur-lg"></div>
            
            <div class="flex items-center justify-between relative z-10">
                <div>
                    <p class="text-[10px] font-bold text-white/70 uppercase tracking-widest mb-1">Total Entri</p>
                    <p class="text-3xl font-extrabold tracking-tight leading-none mb-1.5">{{ number_format($totalEntri) }}</p>
                    <span class="text-[10px] text-white/55 font-medium">data approved di DB</span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-white/15 flex items-center justify-center backdrop-blur-md border border-white/20 shadow-inner flex-shrink-0">
                    <i class="fas fa-database text-lg text-white"></i>
                </div>
            </div>
        </div>
        
    </div>

    {{-- ── EXPORT BAR ─────────── --}}
    <div class="section-wrap flex items-center justify-between flex-wrap gap-4 mb-5"
         style="padding:1rem 1.5rem;">
        <div>
            <div class="text-sm font-medium text-gray-700">Ekspor Semua Data</div>
            <div class="text-xs text-gray-400 mt-0.5">
                {{ $totalEntri }} baris data
                @if($filterTw || $filterTahun || $filterKategori)
                    <span class="text-blue-500">
                        (filter aktif:
                        @if($filterTw) {{ $filterTw }} @endif
                        @if($filterTahun) {{ $filterTahun }} @endif
                        @if($filterKategori) {{ $filterKategori }} @endif
                        )
                    </span>
                @endif
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('dekan.laporan.exportCsv', array_filter(['tw' => $filterTw, 'tahun' => $filterTahun, 'kategori' => $filterKategori])) }}"
               class="export-btn export-csv">
                <i class="fas fa-file-csv text-xs"></i> Export CSV
            </a>
            <a href="{{ route('dekan.laporan.exportPdf', array_filter(['tw' => $filterTw, 'tahun' => $filterTahun, 'kategori' => $filterKategori])) }}"
               class="export-btn bg-red-600 hover:bg-red-700 text-white">
                <i class="fas fa-file-pdf text-xs"></i> Export PDF
            </a>
        </div>
    </div>

    {{-- ── DAFTAR LAPORAN ──────── --}}
    <div class="section-wrap" style="padding:0; overflow:hidden;">
        <div class="px-6 py-4 border-b border-gray-100">
            <div class="section-title" style="margin-bottom:0">Daftar Laporan</div>
            <div class="section-sub" style="margin-bottom:0">Unduh laporan lengkap per periode (triwulan) dan kategori dalam format CSV dan PDF</div>
        </div>

        @if($daftarLaporan->isEmpty())
            <div class="empty-state">
                <i class="fas fa-folder-open text-gray-300"></i>
                <p class="font-medium text-gray-500">Belum ada laporan tersedia</p>
                <p class="text-xs mt-1">Coba ubah filter atau tunggu data diinput dan disetujui.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="dl-table" id="laporan-table">
                    <thead>
                        <tr>
                            <th style="width:15%">Periode</th>
                            <th style="width:30%">Jenis Laporan</th>
                            <th style="width:20%">Tanggal Generate</th>
                            <th style="width:15%">Ukuran File</th>
                            <th style="width:20%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="laporan-tbody"></tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="pg-row">
                <div class="pg-info" id="pg-info">—</div>
                <div class="pg-btns">
                    <button class="pg-btn" id="pg-prev" onclick="changePage(-1)" disabled>
                        <i class="fas fa-chevron-left" style="font-size:10px"></i> Sebelumnya
                    </button>
                    <button class="pg-btn" id="pg-next" onclick="changePage(1)">
                        Selanjutnya <i class="fas fa-chevron-right" style="font-size:10px"></i>
                    </button>
                </div>
            </div>
        @endif
    </div>

    {{-- ── REKAP PER KATEGORI ─────────────────────────────────────── --}}
    @if($rekapKategori->isNotEmpty())
    <div class="section-wrap" style="padding:0; overflow:hidden; margin-top:20px;">
        <div class="px-6 py-4 border-b border-gray-100">
            <div class="section-title" style="margin-bottom:0">Rekapitulasi per Kategori</div>
            <div class="section-sub" style="margin-bottom:0">Agregat target, realisasi, dan deviasi per kategori dari filter aktif</div>
        </div>
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Kategori</th>
                        <th>Target</th>
                        <th>Realisasi</th>
                        <th>Deviasi</th>
                        <th style="width:200px">Capaian</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rekapKategori as $r)
                    @php $over = $r['deviasi'] >= 0; @endphp
                    <tr>
                        <td class="font-medium">{{ $r['kat'] }}</td>
                        <td>{{ number_format($r['target']) }}</td>
                        <td>{{ number_format($r['realisasi']) }}</td>
                        <td class="font-semibold {{ $over ? 'text-green-600' : 'text-red-500' }}">
                            {{ $over ? '+' : '' }}{{ number_format($r['deviasi']) }}
                        </td>
                        <td>
                            <div class="flex items-center gap-2">
                                <div class="flex-1 progress-bar">
                                    <div class="progress-fill {{ $over ? 'bg-green-400' : 'bg-red-400' }}"
                                         style="width:{{ min($r['pct'], 100) }}%"></div>
                                </div>
                                <span class="text-xs font-medium w-10 text-right {{ $over ? 'text-green-600' : 'text-red-500' }}">
                                    {{ $r['pct'] }}%
                                </span>
                            </div>
                        </td>
                        <td><span class="badge {{ $over ? 'badge-green' : 'badge-red' }}">
                            {{ $over ? 'Melampaui' : 'Di Bawah' }}
                        </span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- ── REKAP PER COE ──────────────────────────────────────────── --}}
    @if($rekapCoe->isNotEmpty())
    <div class="section-wrap" style="padding:0; overflow:hidden; margin-top:20px;">
        <div class="px-6 py-4 border-b border-gray-100">
            <div class="section-title" style="margin-bottom:0">Rekapitulasi per CoE</div>
            <div class="section-sub" style="margin-bottom:0">Agregat realisasi and persentase kontribusi terhadap target overall, diurutkan berdasarkan realisasi tertinggi</div>
        </div>
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>CoE</th>
                        <th>Total Entri</th>
                        <th>Realisasi</th>
                        <th style="width:250px">Kontribusi Target Overall</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rekapCoe as $r)
                    @php
                        $contrib = $summary['totalTarget'] > 0 ? round(($r['realisasi'] / $summary['totalTarget']) * 100, 1) : 0;
                    @endphp
                    <tr>
                        <td class="font-medium">{{ $r['coe'] }}</td>
                        <td class="text-gray-400">{{ $r['entri'] }} entri</td>
                        <td class="font-semibold text-blue-600">{{ number_format($r['realisasi']) }}</td>
                        <td>
                            <div class="flex items-center gap-2">
                                <div class="flex-1 progress-bar">
                                    <div class="progress-fill bg-blue-500"
                                         style="width:{{ min($contrib, 100) }}%"></div>
                                </div>
                                <span class="text-xs font-semibold w-12 text-right text-blue-600">
                                    {{ $contrib }}%
                                </span>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
// ── Data tabel dari server ────────────────────────────────────────────────
const TABLE_DATA = @json($daftarLaporan);
const PAGE_SIZE  = 8;
let currentPage  = 1;

function changePage(dir) {
    currentPage += dir;
    renderTable();
}

function renderTable() {
    const total      = TABLE_DATA.length;
    const totalPages = Math.max(1, Math.ceil(total / PAGE_SIZE));
    currentPage      = Math.max(1, Math.min(currentPage, totalPages));

    const start = (currentPage - 1) * PAGE_SIZE;
    const end   = Math.min(start + PAGE_SIZE, total);
    const paged = TABLE_DATA.slice(start, end);

    document.getElementById('pg-info').textContent =
        total === 0 ? 'Tidak ada data'
        : `Menampilkan ${start + 1}–${end} dari ${total} laporan`;

    document.getElementById('pg-prev').disabled = currentPage <= 1;
    document.getElementById('pg-next').disabled = currentPage >= totalPages;

    document.getElementById('laporan-tbody').innerHTML = paged.map(d => {
        return `<tr>
            <td style="font-weight:600;color:#1a2e4a">${d.periode_label}</td>
            <td>${d.jenis_laporan}</td>
            <td style="color:#6b7280">${d.tanggal_generate}</td>
            <td style="color:#6b7280">${d.ukuran_file}</td>
            <td>
                <div class="flex items-center gap-2">
                    <a href="${d.download_url}" class="download-btn bg-blue-600 hover:bg-blue-700 py-1.5 px-3 rounded-lg text-xs font-semibold text-white inline-flex items-center gap-1" title="Download CSV">
                        <i class="fas fa-file-csv" style="font-size:11px"></i> CSV
                    </a>
                    <a href="${d.download_pdf_url}" class="download-btn bg-red-600 hover:bg-red-700 py-1.5 px-3 rounded-lg text-xs font-semibold text-white inline-flex items-center gap-1" title="Download PDF">
                        <i class="fas fa-file-pdf" style="font-size:11px"></i> PDF
                    </a>
                </div>
            </td>
        </tr>`;
    }).join('');
}

document.addEventListener('DOMContentLoaded', () => {
    if (TABLE_DATA.length > 0) renderTable();
});
</script>
@endpush