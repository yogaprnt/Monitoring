@extends('layouts.app')

@section('title', 'Dashboard Manager - RI CCSL')

@push('styles')
<style>
    /* ── Welcome Banner ── */
    .welcome-banner {
        background: linear-gradient(135deg, #1b2436 0%, #2c3b59 100%);
        border-radius: 20px;
        padding: 24px 28px;
        margin-bottom: 24px;
        box-shadow: 0 8px 30px rgba(27,36,54,0.15);
        position: relative;
        overflow: hidden;
    }
    .welcome-banner::before {
        content: '';
        position: absolute; top: -40px; right: -40px;
        width: 200px; height: 200px;
        background: radial-gradient(circle, rgba(255,255,255,0.06) 0%, transparent 70%);
        border-radius: 50%;
    }
    .welcome-title { font-size: 1.45rem; font-weight: 800; color: #fff; }
    .welcome-sub { font-size: 13px; color: rgba(255,255,255,0.65); margin-top: 5px; font-weight: 500; }
    .welcome-pills { display: flex; gap: 8px; margin-top: 16px; flex-wrap: wrap; }
    .welcome-pill {
        background: rgba(255,255,255,0.1);
        border: 1px solid rgba(255,255,255,0.18);
        border-radius: 99px; padding: 5px 14px;
        font-size: 11.5px; font-weight: 700; color: #fff;
        display: flex; align-items: center; gap: 6px;
    }

    /* ===== LEADERBOARD ===== */
    .lb-list { display: flex; flex-direction: column; gap: 10px; }
    .lb-row {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 14px;
        border-radius: 12px;
        border: 1.5px solid #f1f5f9;
        background: #fff;
        transition: all 0.2s;
        cursor: default;
        position: relative;
        overflow: hidden;
    }
    .lb-row:hover { transform: translateX(3px); box-shadow: 0 4px 16px rgba(27,36,54,0.07); border-color: #e2e8f0; }
    .lb-row.lb-first {
        background: linear-gradient(135deg, #1b2436 0%, #2c3b59 100%);
        border-color: transparent;
        box-shadow: 0 6px 20px rgba(27,36,54,0.22);
    }
    .lb-row.lb-first:hover { transform: translateX(3px); box-shadow: 0 8px 28px rgba(27,36,54,0.3); }
    .lb-row.lb-first::before {
        content: '';
        position: absolute; top:-20px; right:-20px;
        width:80px; height:80px;
        background: radial-gradient(circle, rgba(255,255,255,0.08) 0%, transparent 70%);
        border-radius:50%;
    }
    .lb-rank {
        width: 32px; height: 32px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 13px; font-weight: 800; flex-shrink: 0;
        background: #eef1f8; color: #1b2436;
    }
    .lb-first .lb-rank { background: rgba(255,255,255,0.15); color: #fff; }
    .lb-medal { font-size: 18px; line-height:1; }
    .lb-body { flex: 1; min-width: 0; }
    .lb-name { font-size: 13px; font-weight: 700; color: #1f2733; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .lb-first .lb-name { color: #fff; }
    .lb-bar-wrap { margin-top: 5px; height: 4px; background: #f1f5f9; border-radius: 99px; overflow:hidden; }
    .lb-first .lb-bar-wrap { background: rgba(255,255,255,0.15); }
    .lb-bar { height: 100%; border-radius: 99px; transition: width 0.6s cubic-bezier(0.16,1,0.3,1); }
    .lb-score {
        display: flex; flex-direction: column; align-items: flex-end; flex-shrink:0;
    }
    .lb-val { font-size: 14px; font-weight: 800; color: #1b2436; }
    .lb-first .lb-val { color: #fff; }
    .lb-unit { font-size: 10px; font-weight: 600; color: #7a8496; }
    .lb-first .lb-unit { color: rgba(255,255,255,0.6); }

    /* KPI Category Card Pagination */
    .kpi-pagination {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: 16px;
        padding-top: 12px;
        border-top: 1px solid #f1f5f9;
        gap: 8px;
        flex-wrap: wrap;
    }
    .kpi-pag-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        font-size: 11px;
        font-weight: 600;
        color: #475569;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s;
    }
    .kpi-pag-btn:hover:not(:disabled) {
        background: #f1f5f9;
        color: #0f172a;
    }
    .kpi-pag-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    .kpi-pag-info {
        font-size: 11px;
        font-weight: 500;
        color: #64748b;
    }
</style>
@endpush

@section('content')
<div class="content">

    <!-- Welcome Banner -->
    <div class="welcome-banner">
        <div style="position:relative;z-index:1">
            <div class="welcome-title">Selamat datang, {{ $user->name ?? 'Manager' }} 👋</div>
            <div class="welcome-sub">Pantau dan evaluasi kinerja Center of Excellence secara keseluruhan &middot; Tahun {{ $filterTahun }}</div>
            <div class="welcome-pills">
                <div class="welcome-pill"><i class="fas fa-layer-group"></i> 12 COE</div>
                <div class="welcome-pill"><i class="fas fa-file-alt"></i> {{ $metrics['total_entri'] ?? 0 }} Total Entri</div>
                @if(!empty($metrics['overall_capaian']))
                    <div class="welcome-pill"><i class="fas fa-chart-line"></i> {{ $metrics['overall_capaian'] }}% Capaian Overall RI</div>
                @endif
            </div>
        </div>
    </div>

    @php $isEmpty = $coeStats->isEmpty(); @endphp

    @if($isEmpty)
        <div class="bg-white p-12 text-center rounded-xl border border-gray-200 shadow-sm mb-8">
            <i class="fas fa-chart-pie text-gray-300 text-4xl mb-3 block"></i>
            <h3 class="font-bold text-gray-700 text-lg">Belum ada data yang disetujui</h3>
            <p class="text-sm text-gray-400 mt-1.5 max-w-md mx-auto">Data akan otomatis muncul di dashboard setelah ada inputan yang disetujui (Approved) secara final oleh Manager.</p>
        </div>
    @else

        <!-- Filters -->
        <form method="GET" action="{{ route('manager.dashboard') }}" class="flex items-center gap-3 mb-6 bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex-wrap">
            <div class="flex flex-col">
                <label class="text-xs font-bold text-gray-500 mb-1">Periode</label>
                <select name="periode" onchange="this.form.submit()" class="border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-[#1b2436] min-w-[140px]">
                    <option value="">Semua Periode</option>
                    @foreach($periodeList as $p)
                        <option value="{{ $p }}" @selected($periode == $p)>{{ $p }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex flex-col">
                <label class="text-xs font-bold text-gray-500 mb-1">COE</label>
                <select name="coe" onchange="this.form.submit()" class="border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-[#1b2436] min-w-[180px]">
                    <option value="">Semua COE</option>
                    @foreach($coeList as $c)
                        <option value="{{ $c }}" @selected($filterCoe == $c)>{{ $c }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex flex-col">
                <label class="text-xs font-bold text-gray-500 mb-1">Kategori</label>
                <select name="kategori" onchange="this.form.submit()" class="border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-[#1b2436] min-w-[140px]">
                    <option value="">Semua Kategori</option>
                    @foreach($kategoriList as $k)
                        <option value="{{ $k }}" @selected($filterKategori == $k)>{{ ucfirst($k) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end h-full pt-5">
                <a href="{{ route('manager.dashboard') }}" class="text-sm font-bold text-gray-500 hover:text-gray-700 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition bg-white" style="text-decoration:none">Reset</a>
            </div>
        </form>

        <!-- Metrics Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Card 1 -->
            <div class="relative bg-white rounded-xl border border-gray-200 shadow-sm p-5 overflow-hidden">
                <div class="absolute left-0 top-0 bottom-0 w-1 bg-[#378ADD]"></div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total COE</span>
                    <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600"><i class="fas fa-layer-group text-sm"></i></div>
                </div>
                <div class="text-3xl font-extrabold text-gray-800 leading-tight mb-2">12</div>
                <div class="text-xs text-gray-400 font-semibold"><i class="fas fa-circle text-[7px] mr-1 text-blue-400"></i> {{ $metrics['total_entri'] }} total entri</div>
            </div>

            <!-- Card 2 -->
            <div class="relative bg-white rounded-xl border border-gray-200 shadow-sm p-5 overflow-hidden">
                <div class="absolute left-0 top-0 bottom-0 w-1 bg-slate-500"></div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Target Overall RI</span>
                    <div class="w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center text-slate-500"><i class="fas fa-bullseye text-sm"></i></div>
                </div>
                <div class="text-3xl font-extrabold text-gray-800 leading-tight mb-2">{{ number_format($metrics['overall_target']) }}</div>
                <div class="text-xs text-gray-400 font-semibold"><i class="fas fa-circle text-[7px] mr-1 text-slate-400"></i> Diatur di Data Master</div>
            </div>

            <!-- Card 3 -->
            <div class="relative bg-white rounded-xl border border-gray-200 shadow-sm p-5 overflow-hidden">
                <div class="absolute left-0 top-0 bottom-0 w-1 bg-blue-700"></div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Realisasi Gabungan</span>
                    <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-700"><i class="fas fa-check-circle text-sm"></i></div>
                </div>
                <div class="text-3xl font-extrabold text-gray-800 leading-tight mb-2">{{ number_format($metrics['overall_realisasi']) }}</div>
                <div class="text-xs {{ $metrics['overall_deviasi'] >= 0 ? 'text-green-600' : 'text-red-650' }} font-bold">
                    <i class="fas fa-{{ $metrics['overall_deviasi'] >= 0 ? 'arrow-up' : 'arrow-down' }} text-[9px] mr-1"></i>
                    deviasi {{ $metrics['overall_deviasi'] >= 0 ? '+' : '' }}{{ number_format($metrics['overall_deviasi']) }}
                </div>
            </div>

            <!-- Card 4 -->
            <div class="relative bg-white rounded-xl border border-gray-200 shadow-sm p-5 overflow-hidden">
                <div class="absolute left-0 top-0 bottom-0 w-1 bg-red-500"></div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">COE Perlu Perhatian</span>
                    <div class="w-8 h-8 rounded-lg bg-red-50 flex items-center justify-center text-red-500"><i class="fas fa-exclamation-triangle text-sm"></i></div>
                </div>
                <div class="text-base font-extrabold text-red-700 truncate leading-tight mt-1 mb-2" title="{{ $metrics['worst_coe']['coe'] ?? '—' }}">
                    {{ $metrics['worst_coe']['coe'] ?? '—' }}
                </div>
                <div class="text-xs text-red-600 font-bold">
                    Realisasi Terendah: {{ number_format($metrics['worst_coe']['realisasi'] ?? 0) }} Output
                </div>
            </div>
        </div>

        <!-- COE Cards -->
        <h2 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-4">Ringkasan Kinerja per COE</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            @foreach($coeStats as $stat)
                @php $contrib = $metrics['overall_target'] > 0 ? round(($stat['realisasi'] / $metrics['overall_target']) * 100, 1) : 0; @endphp
                <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex flex-col justify-between">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600 flex-shrink-0">
                            <i class="fas fa-layer-group text-sm"></i>
                        </div>
                        <h3 class="text-sm font-bold text-gray-800 truncate" title="{{ $stat['coe'] }}">{{ $stat['coe'] }}</h3>
                    </div>
                    <div class="space-y-2 mb-3">
                        <div class="flex justify-between text-xs text-gray-500"><span>Realisasi</span><span class="font-bold text-blue-600">{{ number_format($stat['realisasi']) }} Output</span></div>
                        <div class="flex justify-between text-xs text-gray-500"><span>Kontribusi</span><span class="font-bold text-gray-700">{{ $contrib }}%</span></div>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-1.5 mb-1 overflow-hidden">
                        <div class="bg-blue-600 h-full rounded-full" style="width: {{ min($contrib, 100) }}%"></div>
                    </div>
                    <div class="text-[10px] text-gray-400">Kontribusi ke Target Overall</div>
                </div>
            @endforeach
        </div>

        <!-- Analytical Section -->
        <h2 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-4">Analitik Visual</h2>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Stacked Bar Chart -->
            <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                <h3 class="text-base font-bold text-gray-800 mb-1">Perbandingan Realisasi per Kategori</h3>
                <p class="text-xs text-gray-400 mb-4">Stacked per COE — total kontribusi realisasi</p>
                <div id="coe-legend" class="flex flex-wrap gap-2.5 mb-4 text-[11px] font-semibold text-gray-500"></div>
                <div style="position:relative;height:240px"><canvas id="coeBarChart"></canvas></div>
            </div>

            <!-- Leaderboard -->
            <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm flex flex-col">
                <div class="flex items-start justify-between mb-5">
                    <div>
                        <h3 class="text-base font-bold text-gray-800 mb-0.5">Papan Peringkat Kinerja COE</h3>
                        <p class="text-xs text-gray-400">Top COE diurutkan berdasarkan realisasi output</p>
                    </div>
                    <span class="inline-flex items-center gap-1.5 bg-[#eef1f8] text-[#2c3b59] text-[10.5px] font-bold px-3 py-1.5 rounded-full">
                        <i class="fas fa-trophy text-[10px]"></i> Ranking
                    </span>
                </div>
                <div class="lb-list" id="leaderboard-list"></div>
            </div>
        </div>

        <!-- Category Detail Grid -->
        <h2 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-4">Analisis Detil Kategori KPI</h2>
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-8">
            @foreach(['Riset', 'Bisnis', 'Pengabdian', 'Akademik', 'Inovasi'] as $katName)
                @php
                    $rows = $categoryTables[$katName] ?? [];
                    $totalTarget = array_sum(array_column($rows, 'target'));
                    $totalRealisasi = array_sum(array_column($rows, 'realisasi'));
                    $totalDev = $totalRealisasi - $totalTarget;
                    $displayName = match($katName) { 'Riset'=>'Riset','Bisnis'=>'Bisnis','Pengabdian'=>'Pengabdian Masyarakat','Akademik'=>'Akademik','Inovasi'=>'Inovasi',default=>$katName };
                    $displayLabel = match($katName) { 'Riset'=>'Jenis Riset','Bisnis'=>'Jenis Bisnis','Pengabdian'=>'Jenis Pengabdian','Akademik'=>'Jenis Akademik','Inovasi'=>'Jenis Inovasi',default=>'Jenis' };
                @endphp
                <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm kpi-cat-card">
                    <h3 class="font-bold text-gray-800 text-sm mb-1">Target vs Realisasi {{ $displayName }}</h3>
                    <p class="text-xs text-gray-400 mb-4">
                        Perbandingan target dan capaian {{ strtolower($katName) }} Tahun {{ $filterTahun }}
                        (Total Target: {{ number_format($totalTarget) }} | Total Realisasi: {{ number_format($totalRealisasi) }} | Deviasi: {{ ($totalDev >= 0 ? '+' : '') . number_format($totalDev) }})
                    </p>
                    @if(count($rows))
                        @php $totalPages = ceil(count($rows) / 5); @endphp
                        <div class="overflow-x-auto">
                            <table class="w-full text-xs border-collapse">
                                <thead>
                                    <tr class="border-b border-gray-200 text-gray-400 font-bold text-[11px] uppercase tracking-wide">
                                        <th class="py-2.5 text-left w-10">No</th>
                                        <th class="py-2.5 text-left">{{ $displayLabel }}</th>
                                        <th class="py-2.5 text-right w-16">Target</th>
                                        <th class="py-2.5 text-right w-16">Realisasi</th>
                                        <th class="py-2.5 text-right w-16">Deviasi</th>
                                        <th class="py-2.5 text-right w-24">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="kpi-cat-tbody" data-current-page="1">
                                    @foreach($rows as $i => $row)
                                        @php
                                            $dev = $row['deviasi'];
                                            $over = $dev >= 0;
                                            $page = floor($i / 5) + 1;
                                        @endphp
                                        <tr class="kpi-cat-row border-b border-gray-100 hover:bg-gray-50/50" data-page="{{ $page }}" style="display: {{ $page == 1 ? 'table-row' : 'none' }};">
                                            <td class="py-3.5 font-bold text-gray-600">{{ $i + 1 }}</td>
                                            <td class="py-3.5 font-semibold text-gray-700 leading-snug">{{ $row['judul'] }}</td>
                                            <td class="py-3.5 text-right text-gray-500 font-medium">{{ number_format($row['target']) }}</td>
                                            <td class="py-3.5 text-right font-bold text-gray-800">{{ number_format($row['realisasi']) }}</td>
                                            <td class="py-3.5 text-right font-extrabold {{ $over ? 'text-green-600' : 'text-red-500' }}">{{ $over ? '+' : '' }}{{ number_format($dev) }}</td>
                                            <td class="py-3.5 text-right">
                                                <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-full {{ $over ? 'bg-green-50 text-green-700 border border-green-150' : 'bg-red-50 text-red-650 border border-red-150' }}">
                                                    <i class="fas {{ $over ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down' }}"></i> {{ $over ? 'Melampaui' : 'Di Bawah' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if($totalPages > 1)
                            <div class="kpi-pagination">
                                <button class="kpi-pag-btn prev-btn" onclick="changeKpiPage(this, -1)" disabled>
                                    <i class="fas fa-chevron-left"></i> Prev
                                </button>
                                <span class="kpi-pag-info">Page <span class="curr-lbl">1</span> of {{ $totalPages }}</span>
                                <button class="kpi-pag-btn next-btn" onclick="changeKpiPage(this, 1)">
                                    Next <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                        @endif
                    @else
                        <div class="py-8 text-center text-gray-400">
                            <i class="fas fa-folder-open text-2xl mb-2 block opacity-55"></i>
                            <span class="text-xs">Tidak ada data untuk kategori ini.</span>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <!-- Detail Data Inputan Staff -->
        <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
            <h3 class="text-base font-bold text-gray-850 mb-4">Detail Data Inputan Staff</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm border-collapse text-left">
                    <thead>
                        <tr class="border-b border-gray-200 text-gray-400 font-bold text-xs uppercase tracking-wide">
                            <th class="py-3 w-12">#</th>
                            <th class="py-3 w-1/3">COE / Indikator</th>
                            <th class="py-3">Kategori</th>
                            <th class="py-3 text-right w-24">Realisasi</th>
                            <th class="py-3 w-1/4 pl-6">Staff Penginput</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-150">
                        @php
                            $katColorMap = ['riset'=>'bg-blue-50 text-blue-700 border-blue-150','bisnis'=>'bg-purple-50 text-purple-700 border-purple-150','pengabdian'=>'bg-green-50 text-green-700 border-green-150','akademik'=>'bg-yellow-50 text-yellow-700 border-yellow-150','inovasi'=>'bg-orange-50 text-orange-700 border-orange-150'];
                        @endphp
                        @forelse($detailData as $i => $row)
                            @php
                                $katKey = strtolower($row->kategori ?? '');
                                $katColor = $katColorMap[$katKey] ?? 'bg-gray-50 text-gray-700 border-gray-200';
                            @endphp
                            <tr class="hover:bg-gray-50/50">
                                <td class="py-4 text-gray-400 text-xs font-semibold">{{ ($detailData->currentPage() - 1) * $detailData->perPage() + $i + 1 }}</td>
                                <td class="py-4">
                                    <div class="font-bold text-gray-800">{{ $row->coe ?? '-' }}</div>
                                    <div class="text-xs text-gray-400 truncate max-w-[280px] mt-0.5" title="{{ $row->judul }}">{{ $row->judul }}</div>
                                </td>
                                <td class="py-4">
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $katColor }}">
                                        {{ ucfirst($row->kategori ?? '-') }}
                                    </span>
                                </td>
                                <td class="py-4 text-right font-extrabold text-[#185FA5]">{{ number_format($row->realisasi) }}</td>
                                <td class="py-4 pl-6">
                                    @if($row->penginput)
                                        <div class="flex items-center gap-2">
                                            <div class="w-7 h-7 rounded-full bg-gradient-to-br from-[#1a2e4a] to-[#378ADD] flex items-center justify-center text-xs font-bold text-white uppercase">{{ substr($row->penginput->name, 0, 1) }}</div>
                                            <span class="font-bold text-gray-700 text-sm">{{ $row->penginput->name }}</span>
                                        </div>
                                    @else
                                        <span class="text-gray-400 text-xs">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-12 text-center text-gray-400">
                                    <i class="fas fa-inbox text-3xl mb-2 block opacity-55"></i>
                                    <span class="text-xs">Tidak ada data inputan staff.</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($detailData->total() > 0)
                <div class="flex items-center justify-between mt-6 pt-4 border-t border-gray-150 flex-wrap gap-4 text-sm text-gray-500">
                    <div class="font-medium">Menampilkan {{ $detailData->firstItem() }}–{{ $detailData->lastItem() }} dari {{ $detailData->total() }} entri</div>
                    <div class="flex gap-1.5 items-center">
                        @if($detailData->onFirstPage())
                            <span class="px-2.5 py-1.5 rounded-lg border border-gray-200 text-gray-300 cursor-not-allowed bg-white"><i class="fas fa-chevron-left text-xs"></i></span>
                        @else
                            <a href="{{ $detailData->previousPageUrl() }}" class="px-2.5 py-1.5 rounded-lg border border-gray-200 text-gray-650 hover:bg-gray-50 bg-white"><i class="fas fa-chevron-left text-xs"></i></a>
                        @endif
                        @for($pg = 1; $pg <= $detailData->lastPage(); $pg++)
                            @if($pg == $detailData->currentPage())
                                <span class="px-3.5 py-1.5 rounded-lg bg-[#1a2e4a] text-white font-bold">{{ $pg }}</span>
                            @else
                                <a href="{{ $detailData->url($pg) }}" class="px-3.5 py-1.5 rounded-lg border border-gray-200 text-gray-650 hover:bg-gray-50 bg-white" style="text-decoration:none">{{ $pg }}</a>
                            @endif
                        @endfor
                        @if($detailData->hasMorePages())
                            <a href="{{ $detailData->nextPageUrl() }}" class="px-2.5 py-1.5 rounded-lg border border-gray-200 text-gray-650 hover:bg-gray-50 bg-white"><i class="fas fa-chevron-right text-xs"></i></a>
                        @else
                            <span class="px-2.5 py-1.5 rounded-lg border border-gray-200 text-gray-300 cursor-not-allowed bg-white"><i class="fas fa-chevron-right text-xs"></i></span>
                        @endif
                    </div>
                </div>
            @endif
        </div>

    @endif

</div>
@endsection

@push('scripts')
<script>
const CHART_DATA = @json($chartData);
const COE_STATS  = @json($coeStats);

// ─── STACKED BAR CHART ───────────────────────────────────────────────────────
(function renderStackedChart() {
    var canvas = document.getElementById('coeBarChart');
    if (!canvas) return;

    var coeList   = CHART_DATA.coe_list         ?? [];
    var coeKatMap = CHART_DATA.per_kategori_coe  ?? {};
    var labels    = CHART_DATA.labels            ?? [];

    if (!coeList.length) return;

    var palette = [
        '#4299e1', '#38a169', '#dd6b20', '#805ad5', '#e53e3e',
        '#d69e2e', '#319795', '#d53f8c', '#3182ce', '#2f855a'
    ];

    var datasets = coeList.map(function(coe, i) {
        return {
            label           : coe,
            data            : coeKatMap[coe] ?? new Array(labels.length).fill(0),
            backgroundColor : palette[i % palette.length],
            borderRadius    : 4,
            stack           : 'stack'
        };
    });

    var legendEl = document.getElementById('coe-legend');
    if (legendEl) {
        coeList.forEach(function(coe, i) {
            var span = document.createElement('span');
            span.style.cssText = 'display:flex;align-items:center;gap:6px;';
            span.innerHTML =
                '<span style="width:10px;height:10px;border-radius:3px;background:' +
                palette[i % palette.length] +
                ';flex-shrink:0;display:inline-block"></span>' + coe;
            legendEl.appendChild(span);
        });
    }

    new Chart(canvas, {
        type : 'bar',
        data : { labels: labels, datasets: datasets },
        options: {
            responsive          : true,
            maintainAspectRatio : false,
            plugins: {
                legend : { display: false },
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            var v = ctx.parsed.y;
                            return ' ' + ctx.dataset.label + ': ' + v;
                        }
                    }
                }
            },
            scales: {
                x: {
                    stacked : true,
                    ticks   : { color: '#64748b', font: { family: 'Plus Jakarta Sans', size: 10, weight: '600' } },
                    grid    : { color: 'rgba(0,0,0,0.04)' }
                },
                y: {
                    stacked : true,
                    ticks   : { color: '#64748b', font: { family: 'Plus Jakarta Sans', size: 11, weight: '500' } },
                    grid    : { color: 'rgba(0,0,0,0.04)' }
                }
            }
        }
    });
})();

// ─── LEADERBOARD ─────────────────────────────────────────────────────────────
(function renderLeaderboard() {
    var list = document.getElementById('leaderboard-list');
    if (!list || !COE_STATS.length) return;

    var top = COE_STATS.slice(0, 6);
    var maxVal = top[0].realisasi || 1;
    var medals = ['🥇','🥈','🥉'];
    var barColors = [
        'linear-gradient(90deg,#f59e0b,#fbbf24)',
        'linear-gradient(90deg,#94a3b8,#cbd5e1)',
        'linear-gradient(90deg,#f97316,#fdba74)',
        'linear-gradient(90deg,#60a5fa,#93c5fd)',
        'linear-gradient(90deg,#34d399,#6ee7b7)',
        'linear-gradient(90deg,#a78bfa,#c4b5fd)',
    ];

    top.forEach(function(stat, idx) {
        var rank   = idx + 1;
        var pct    = Math.round((stat.realisasi / maxVal) * 100);
        var isFirst = (idx === 0);
        var medalHTML = rank <= 3
            ? '<span class="lb-medal">' + medals[rank-1] + '</span>'
            : '';

        var row = document.createElement('div');
        row.className = 'lb-row' + (isFirst ? ' lb-first' : '');
        row.style.animationDelay = (idx * 60) + 'ms';

        var rankInner = medalHTML ||
            '<span class="lb-rank">' + rank + '</span>';

        row.innerHTML =
            rankInner +
            '<div class="lb-body">' +
                '<div class="lb-name" title="' + stat.coe + '">' + stat.coe + '</div>' +
                '<div class="lb-bar-wrap">' +
                    '<div class="lb-bar" data-pct="' + pct + '" style="width:0%;background:' + (isFirst ? 'rgba(255,255,255,0.5)' : barColors[idx]) + '"></div>' +
                '</div>' +
            '</div>' +
            '<div class="lb-score">' +
                '<div class="lb-val">' + stat.realisasi.toLocaleString() + '</div>' +
                '<div class="lb-unit">output</div>' +
            '</div>';

        list.appendChild(row);
    });

    // animate bars after paint
    requestAnimationFrame(function() {
        list.querySelectorAll('.lb-bar').forEach(function(bar) {
            bar.style.width = bar.getAttribute('data-pct') + '%';
        });
    });
})();

// Client-side pagination helper for KPI category tables
function changeKpiPage(btn, direction) {
    const card = btn.closest('.kpi-cat-card');
    const tbody = card.querySelector('.kpi-cat-tbody');
    const rows = tbody.querySelectorAll('.kpi-cat-row');
    const currLbl = card.querySelector('.curr-lbl');
    const prevBtn = card.querySelector('.prev-btn');
    const nextBtn = card.querySelector('.next-btn');
    
    let currentPage = parseInt(tbody.getAttribute('data-current-page') || '1');
    const totalPages = Math.ceil(rows.length / 5);
    
    currentPage += direction;
    if (currentPage < 1) currentPage = 1;
    if (currentPage > totalPages) currentPage = totalPages;
    
    tbody.setAttribute('data-current-page', currentPage);
    currLbl.textContent = currentPage;
    
    rows.forEach(row => {
        const page = parseInt(row.getAttribute('data-page'));
        if (page === currentPage) {
            row.style.display = 'table-row';
        } else {
            row.style.display = 'none';
        }
    });
    
    prevBtn.disabled = (currentPage === 1);
    nextBtn.disabled = (currentPage === totalPages);
}
</script>
@endpush