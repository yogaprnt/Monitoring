@extends('layouts.app')

@section('title', 'Dashboard Dekan - RI CCSL')

@push('styles')
<style>
    /* ── Page Heading ────────────────────────────────────────────── */
    .page-title {
        font-size: 1.5rem;
        font-weight: 800;
        background: linear-gradient(135deg, #1a2e4a 0%, #378ADD 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        letter-spacing: -0.5px;
    }
    .page-subtitle { font-size: 12px; color: #94a3b8; margin-top: 3px; font-weight: 500; }

    /* ── Filter ─────────────────────────────────────────────────── */
    .filter-row { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; max-width: 100%; }
    .filter-row label { font-size: 12px; color: #64748b; font-weight: 600; }
    .filter-row select {
        font-size: 12px; padding: 7px 12px;
        border: 1px solid #e2e8f0; border-radius: 10px;
        background: #fff; outline: none; cursor: pointer;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-weight: 500; color: #374151;
        box-shadow: 0 1px 4px rgba(0,0,0,0.06);
        transition: border-color .2s, box-shadow .2s;
        max-width: 100%;
    }
    .filter-row select:focus { border-color: #378ADD; box-shadow: 0 0 0 3px rgba(55,138,221,0.15); }
    .filter-sep { width: 1px; height: 24px; background: #e2e8f0; margin: 0 4px; }

    /* ── CoE active badge ─────────────────────────────────────────── */
    .coe-active-badge {
        display: none; align-items: center; gap: 5px;
        font-size: 11px; font-weight: 700; color: #185FA5;
        background: linear-gradient(135deg, #dbeafe, #bfdbfe);
        border: 1px solid #93c5fd;
        padding: 4px 10px; border-radius: 99px; margin-left: 8px;
    }
    .coe-active-badge.visible { display: inline-flex; }

    /* ── Section label ───────────────────────────────────────────── */
    .sec-lbl {
        font-size: 10px; font-weight: 700; color: #94a3b8;
        text-transform: uppercase; letter-spacing: 1px;
        margin: 24px 0 12px;
        display: flex; align-items: center; gap: 8px;
    }
    .sec-lbl::after {
        content: ''; flex: 1; height: 1px;
        background: linear-gradient(90deg, #e2e8f0, transparent);
    }

    /* ── KPI Cards ───────────────────────────────────────────────── */
    .kpi-grid { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 16px; }
    @media(max-width: 1340px) { .kpi-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
    @media(max-width: 920px)  { .kpi-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media(max-width: 600px)  { .kpi-grid { grid-template-columns: minmax(0, 1fr); } }

    /* ── Triwulan Cards ──────────────────────────────────────────── */
    .tw-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; }
    @media(max-width: 1140px) { .tw-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media(max-width: 600px)  { .tw-grid { grid-template-columns: minmax(0, 1fr); } }

    .tw-card {
        background: #fff;
        border: 1px solid rgba(226,232,240,0.8);
        border-radius: 16px;
        padding: 16px 18px;
        position: relative; overflow: hidden;
        box-shadow: 0 2px 12px rgba(0,0,0,0.05);
        transition: transform .2s ease, box-shadow .2s ease;
        min-width: 0;
    }
    .tw-card::before {
        content: '';
        position: absolute; top: 0; left: 0; bottom: 0; width: 4px;
        border-radius: 16px 0 0 16px;
    }
    .tw-card:hover { transform: translateY(-2px); box-shadow: 0 10px 28px rgba(0,0,0,0.09); }
    .tw-period { font-size: 16px; font-weight: 800; color: #0f172a; }
    .tw-desc   { font-size: 11px; color: #94a3b8; margin-bottom: 14px; margin-top: 3px; font-weight: 500; }
    .tw-top    { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 6px; gap: 8px; flex-wrap: wrap; }
    .tw-nums   { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; margin-bottom: 12px; }
    .tw-num-box { background: #f8fafc; border-radius: 8px; padding: 8px 10px; min-width: 0; }
    .tw-num-lbl { font-size: 10px; color: #94a3b8; margin-bottom: 3px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
    .tw-num-val { font-size: 15px; font-weight: 800; color: #0f172a; word-break: break-word; }
    .tw-bar    { height: 5px; border-radius: 99px; background: #f1f5f9; overflow: hidden; margin-bottom: 10px; }
    .tw-bar-fill { height: 100%; border-radius: 99px; }
    .tw-dev    { display: flex; justify-content: space-between; align-items: center; font-size: 11px; gap: 8px; flex-wrap: wrap; }
    .tw-dev-lbl { color: #94a3b8; font-weight: 500; }

    /* ── Badge ───────────────────────────────────────────────────── */
    .badge    { display: inline-flex; align-items: center; gap: 4px; font-size: 10px; font-weight: 700; padding: 3px 9px; border-radius: 99px; }
    .badge-up { background: linear-gradient(135deg, #d1fae5, #a7f3d0); color: #065f46; border: 1px solid #6ee7b7; }
    .badge-dn { background: linear-gradient(135deg, #fee2e2, #fecaca); color: #7f1d1d; border: 1px solid #fca5a5; }

    /* ── Chart Cards ─────────────────────────────────────────────── */
    .chart-card {
        background: #fff;
        border: 1px solid rgba(226,232,240,0.8);
        border-radius: 16px;
        padding: 18px 20px;
        margin-bottom: 12px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.05);
        transition: box-shadow .2s;
        min-width: 0;
    }
    .chart-card:hover { box-shadow: 0 8px 24px rgba(0,0,0,0.08); }
    .cc-head    { display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; gap: 8px; flex-wrap: wrap; }
    .cc-title   { font-size: 13px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 8px; }
    .cc-dot     { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
    .cc-badge   {
        font-size: 11px; color: #64748b; font-weight: 600;
        background: #f1f5f9; padding: 3px 10px; border-radius: 8px;
        border: 1px solid #e2e8f0;
        white-space: nowrap;
    }

    .chart-grid-2 { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; margin-bottom: 12px; }
    .chart-grid-3 { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; }
    @media(max-width: 1140px) {
        .chart-grid-2,
        .chart-grid-3 { grid-template-columns: minmax(0, 1fr); }
    }

    /* ── CoE Table ───────────────────────────────────────────────── */
    .coe-tbl-wrap { width: 100%; overflow-x: auto; }
    .coe-tbl { width: 100%; min-width: 420px; border-collapse: collapse; font-size: 12px; table-layout: fixed; }
    .coe-tbl th {
        padding: 8px 10px; color: #94a3b8; font-weight: 700; font-size: 10px;
        text-transform: uppercase; letter-spacing: .6px;
        border-bottom: 1px solid #f1f5f9; text-align: left;
        background: #f8fafc;
    }
    .coe-tbl td { padding: 9px 10px; border-bottom: 1px solid #f8fafc; color: #374151; font-weight: 500; word-break: break-word; }
    .coe-tbl tr:last-child td { border-bottom: none; }
    .coe-tbl tbody tr:hover td { background: #f0f9ff; }
    .bar-wrap { display: flex; align-items: center; gap: 8px; }
    .bar-bg   { flex: 1; height: 5px; background: #f1f5f9; border-radius: 99px; overflow: hidden; min-width: 30px; }
    .bar-fill { height: 100%; border-radius: 99px; }

    /* ── Snap header ─────────────────────────────────────────────── */
    .snap-hd   { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
    .snap-link { font-size: 11px; color: #378ADD; font-weight: 700; text-decoration: none; display: flex; align-items: center; gap: 3px; }
    .snap-link:hover { color: #1a5fa8; }

    /* ── Chart canvas wrap ───────────────────────────────────────── */
    .wrap-150 { position: relative; height: 160px; max-width: 100%; }
    .wrap-120 { position: relative; height: 130px; max-width: 100%; }
    .wrap-95  { position: relative; height: 110px; max-width: 100%; }

    /* ── Empty state ─────────────────────────────────────────────── */
    .empty-state { text-align: center; padding: 3rem 1rem; color: #94a3b8; }
    .empty-state i { font-size: 2rem; margin-bottom: .8rem; display: block; opacity: 0.5; }
    .empty-state p { font-size: 12px; font-weight: 500; }

    /* ── Summary strip ──────────────────────────────────────────── */
    .summary-strip {
        background: linear-gradient(135deg, #1a2e4a 0%, #1e3f6e 100%);
        border-radius: 16px;
        padding: 18px 22px;
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 20px;
        box-shadow: 0 8px 24px rgba(26,46,74,0.25);
        gap: 16px; flex-wrap: wrap;
    }
    .summary-strip-left { display: flex; align-items: center; gap: 14px; min-width: 0; }
    .summary-icon { width: 46px; height: 46px; border-radius: 12px; background: rgba(255,255,255,0.15); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .summary-icon i { color: #fff; font-size: 18px; }
    .summary-title { font-size: 13px; font-weight: 700; color: rgba(255,255,255,0.9); }
    .summary-sub   { font-size: 11px; color: rgba(255,255,255,0.5); margin-top: 2px; word-break: break-word; }
    .summary-pills { display: flex; gap: 8px; flex-wrap: wrap; }
    .summary-pill {
        background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.2);
        border-radius: 99px; padding: 5px 14px;
        font-size: 11px; font-weight: 700; color: #fff;
        white-space: nowrap;
    }
    .summary-pill span { color: rgba(255,255,255,0.6); font-weight: 500; margin-right: 4px; }

    /* ── Kategori KPI Cards ── */
    .kpi-cat-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 24px;
        margin-bottom: 24px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.01);
        min-width: 0;
    }
    .kpi-cat-title {
        font-size: 16px;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 4px;
    }
    .kpi-cat-subtitle {
        font-size: 13px;
        color: #64748b;
        margin-bottom: 20px;
        word-break: break-word;
    }
    .kpi-cat-tbl {
        width: 100%;
        border-collapse: collapse;
    }
    .kpi-cat-tbl th {
        text-align: left;
        padding: 10px 12px;
        font-size: 12px;
        font-weight: 700;
        color: #0f172a;
        border-bottom: 1.5px solid #e2e8f0;
        background: transparent;
    }
    .kpi-cat-tbl td {
        padding: 14px 12px;
        font-size: 13px;
        color: #334155;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
        word-break: break-word;
    }
    .kpi-cat-tbl tr:last-child td {
        border-bottom: none;
    }

    @media (max-width: 720px) {
        .kpi-cat-tbl thead { display: none; }
        .kpi-cat-tbl, .kpi-cat-tbl tbody, .kpi-cat-tbl tr, .kpi-cat-tbl td {
            display: block;
            width: 100%;
        }
        .kpi-cat-row {
            border: 1px solid #f1f5f9;
            border-radius: 12px;
            padding: 10px 12px;
            margin-bottom: 10px;
        }
        .kpi-cat-tbl td {
            border-bottom: none;
            padding: 5px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
        }
        .kpi-cat-tbl td::before {
            content: attr(data-label);
            font-size: 10px;
            font-weight: 700;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: .4px;
            flex-shrink: 0;
        }
        .kpi-cat-tbl td[data-label="No"] { display: none; }
        .kpi-cat-tbl td[data-label="Jenis"] {
            display: block;
            padding-bottom: 8px;
            border-bottom: 1px dashed #f1f5f9;
            margin-bottom: 6px;
        }
        .kpi-cat-tbl td[data-label="Jenis"]::before { display: block; margin-bottom: 3px; }
    }
    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        border-radius: 9999px;
        font-size: 11px;
        font-weight: 700;
        color: #fff;
        text-transform: none;
        white-space: nowrap;
    }
    .status-pill-up {
        background-color: #10b981;
    }
    .status-pill-dn {
        background-color: #ef4444;
    }
    
    /* Grid Layout for stacked cards */
    .kpi-cat-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 24px;
        margin-top: 24px;
        margin-bottom: 30px;
    }
    @media (max-width: 1606px) {
        .kpi-cat-grid {
            grid-template-columns: minmax(0, 1fr);
        }
    }
    
    /* Pagination Styling */
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
        white-space: nowrap;
    }
    .kpi-pag-btn:hover:not(:disabled) {
        background: #f1f5f9;
        color: #0f172a;
        border-color: #cbd5e1;
    }
    .kpi-pag-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    .kpi-pag-info {
        font-size: 11px;
        font-weight: 500;
        color: #64748b;
        white-space: nowrap;
    }
</style>
@endpush

@section('content')
<div class="content">

    {{-- Page heading --}}
    <div class="flex items-end justify-between flex-wrap gap-4 mb-6">
        <div>
            <h1 class="page-title flex items-center gap-2">
                Dashboard Dekan
                @if($filterCoe)
                    <span class="coe-active-badge visible">
                        <i class="fas fa-filter" style="font-size:9px"></i>
                        {{ $filterCoeName }}
                    </span>
                @endif
            </h1>
            <p class="page-subtitle">Ringkasan indikator KPI utama dan capaian per triwulan · {{ $filterTahun }}</p>
        </div>

        {{-- ── FILTER TAHUN + COE ──────────────────────────── --}}
        <form method="GET" action="{{ route('dekan.dashboard') }}" class="filter-row">
            <label><i class="fas fa-calendar-alt mr-1 text-blue-400"></i>Tahun:</label>
            <select name="tahun" onchange="this.form.submit()">
                @foreach($tahunList as $t)
                    <option value="{{ $t }}" @selected($filterTahun === $t)>{{ $t }}</option>
                @endforeach
            </select>

            <div class="filter-sep"></div>

            <label><i class="fas fa-building mr-1 text-blue-400"></i>CoE:</label>
            <select name="coe" onchange="this.form.submit()">
                <option value="">Semua CoE</option>
                @foreach($coeList as $coe)
                    <option value="{{ $coe->id }}" @selected($filterCoe == $coe->id)>
                        {{ $coe->nama }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    {{-- ── SUMMARY STRIP ──────────────────────────────────────── --}}
    <div class="summary-strip">
        <div class="summary-strip-left">
            <div class="summary-icon"><i class="fas fa-chart-line"></i></div>
            <div>
                <div class="summary-title">Ringkasan Kinerja {{ $filterTahun }}</div>
                <div class="summary-sub">{{ $filterCoe ? $filterCoeName : 'Semua Center of Excellence' }} · Diperbaharui otomatis</div>
            </div>
        </div>
        <div class="summary-pills">
            @php
                $totalRealisasi = array_sum(array_column(array_map(fn($m) => ['r'=>$m['realisasi']], $metrics), 'r'));
                $melampaui = collect($triwulanKpi)->where('melampaui', true)->count();
                $total_tw  = count($triwulanKpi);
            @endphp
            <div class="summary-pill"><span>Total Output</span>{{ number_format($totalRealisasi) }}</div>
            <div class="summary-pill"><span>TW Melampaui</span>{{ $melampaui }}/{{ $total_tw }}</div>
            <div class="summary-pill"><span>Total CoE</span>12</div>
        </div>
    </div>

    {{-- ── 5 KPI CARDS ──────────────────────────────────────────── --}}
    @php
        $kpiConfig = [
            'pub_nasional'    => [
                'label' => 'Pub Nasional',
                'icon' => 'fa-file-alt',
                'gradient' => 'from-[#378ADD] to-[#1e4875]',
                'glow' => 'rgba(55, 138, 221, 0.3)',
                'bar_color' => '#ffffff'
            ],
            'hki'             => [
                'label' => 'HKI',
                'icon' => 'fa-certificate',
                'gradient' => 'from-[#1D9E75] to-[#0d503b]',
                'glow' => 'rgba(29, 158, 117, 0.3)',
                'bar_color' => '#ffffff'
            ],
            'unit_bisnis'     => [
                'label' => 'Unit Bisnis',
                'icon' => 'fa-store',
                'gradient' => 'from-[#993556] to-[#4e1b2b]',
                'glow' => 'rgba(153, 53, 86, 0.3)',
                'bar_color' => '#ffffff'
            ],
            'intl_selain_q12' => [
                'label' => 'Intl Selain Q1/2',
                'icon' => 'fa-globe',
                'gradient' => 'from-[#BA7517] to-[#5d3a0b]',
                'glow' => 'rgba(186, 117, 23, 0.3)',
                'bar_color' => '#ffffff'
            ],
            'intl_q12'        => [
                'label' => 'Intl Q1/Q2',
                'icon' => 'fa-globe-americas',
                'gradient' => 'from-[#7F77DD] to-[#3a3585]',
                'glow' => 'rgba(127, 119, 221, 0.3)',
                'bar_color' => '#ffffff'
            ],
        ];
    @endphp

    <div class="sec-lbl">Indikator KPI Utama</div>
    <div class="kpi-grid">
        @foreach($kpiConfig as $key => $cfg)
            @php
                $m    = $metrics[$key];
                $over = $m['deviasi'] >= 0;
            @endphp
            <div class="relative overflow-hidden bg-gradient-to-br {{ $cfg['gradient'] }} text-white rounded-2xl p-5 shadow-lg border border-white/10 transition-all duration-300 hover:scale-[1.02] hover:shadow-xl flex flex-col justify-between" style="box-shadow: 0 10px 25px -10px {{ $cfg['glow'] }}">
                <!-- Decorative background circles -->
                <div class="absolute -right-8 -top-8 w-20 h-20 bg-white/10 rounded-full blur-lg"></div>
                <div class="absolute right-10 -bottom-10 w-16 h-16 bg-white/5 rounded-full blur-md"></div>
                
                <div class="relative z-10">
                    <div class="w-10 h-10 rounded-xl bg-white/15 flex items-center justify-center backdrop-blur-md border border-white/20 shadow-inner mb-4">
                        <i class="fas {{ $cfg['icon'] }} text-white text-lg"></i>
                    </div>
                    <div class="text-[11px] font-bold text-white/70 uppercase tracking-widest mb-1.5">{{ $cfg['label'] }}</div>
                    
                    {{-- Realisasi vs Target --}}
                    <div class="text-2xl font-extrabold tracking-tight leading-none mb-3 flex items-baseline gap-1">
                        <span>{{ number_format($m['realisasi']) }}</span>
                        <span class="text-xs text-white/50 font-medium" title="Target Overall">/ {{ number_format($m['target']) }}</span>
                    </div>

                    <div class="inline-flex items-center gap-1 text-[10px] font-bold py-1 px-3 rounded-full {{ $over ? 'bg-white/20 text-white border border-white/15' : 'bg-red-500/30 text-red-100 border border-red-500/25' }}">
                        <i class="fas fa-arrow-{{ $over ? 'up' : 'down' }}" style="font-size:7px"></i>
                        Gap: {{ $over ? '+' : '' }}{{ number_format($m['deviasi']) }}
                    </div>
                </div>
                
                <div class="relative z-10 mt-4">
                    <div class="h-1.5 rounded-full bg-white/20 overflow-hidden">
                        <div class="h-full rounded-full bg-white" style="width:{{ min($m['capaian'],100) }}%"></div>
                    </div>
                    <div class="text-[10px] text-white/50 font-medium mt-2 flex justify-between items-center">
                        <span>Capaian {{ $m['capaian'] }}%</span>
                        <span class="text-[9px]">Target: {{ number_format($m['target']) }}</span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- ── TRIWULAN KPI ──────────────────────────────────────────── --}}
    @php
        $twColors = ['TW1'=>'#378ADD','TW2'=>'#1D9E75','TW3'=>'#BA7517','TW4'=>'#D4537E'];
    @endphp

    <div class="sec-lbl">Triwulan KPI — {{ $filterTahun }}</div>
    <div class="tw-grid">
        @foreach($triwulanKpi as $tw)
            @php $twClr = $twColors[$tw['tw']] ?? '#6b7280'; @endphp
            <div class="tw-card">
                <div style="position:absolute;top:0;left:0;bottom:0;width:4px;background:{{ $twClr }};border-radius:16px 0 0 16px;"></div>
                <div style="padding-left:6px">
                    <div class="tw-top">
                        <div>
                            <div class="tw-period">{{ $tw['periode'] }}</div>
                            <div class="tw-desc">{{ $tw['keterangan'] }}</div>
                        </div>
                        <div class="badge {{ $tw['melampaui'] ? 'badge-up' : 'badge-dn' }}">
                            <i class="fas fa-arrow-{{ $tw['melampaui'] ? 'up' : 'down' }}" style="font-size:8px"></i>
                            {{ $tw['melampaui'] ? 'Melampaui' : 'Di Bawah' }}
                        </div>
                    </div>
                    <div class="tw-nums">
                        <div class="tw-num-box">
                            <div class="tw-num-lbl">Realisasi</div>
                            <div class="tw-num-val" style="color:{{ $twClr }}">{{ number_format($tw['realisasi']) }}</div>
                        </div>
                        <div class="tw-num-box">
                            <div class="tw-num-lbl">Target</div>
                            <div class="tw-num-val">{{ number_format($tw['target']) }}</div>
                        </div>
                    </div>
                    <div class="tw-bar">
                        <div class="tw-bar-fill"
                             style="width:{{ min($tw['capaian'],100) }}%;background:linear-gradient(90deg,{{ $twClr }},{{ $twClr }}88)"></div>
                    </div>
                    <div class="tw-dev">
                        <span class="tw-dev-lbl">Deviasi · {{ $tw['capaian'] }}% capaian</span>
                        <span style="font-size:11px;font-weight:700;color:{{ $tw['melampaui'] ? '#065f46' : '#7f1d1d' }}">
                            {{ $tw['deviasi'] >= 0 ? '+' : '' }}{{ number_format($tw['deviasi']) }}
                        </span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- ── SNAPSHOT KINERJA COE ──────────────────────────────────── --}}
    <div class="sec-lbl" style="margin-top:20px">Snapshot Kinerja CoE</div>

    {{-- Tren Capaian TW --}}
    <div class="chart-card">
        <div class="cc-head">
            <div class="cc-title">
                <div class="cc-dot" style="background:#378ADD"></div>
                Tren Capaian per Triwulan
            </div>
            <div class="cc-badge">
                {{ $filterCoe ? $filterCoeName : 'Semua CoE' }} · {{ $filterTahun }}
            </div>
        </div>
        <div class="wrap-150">
            <canvas id="chartCapaianTw"></canvas>
        </div>
    </div>

    {{-- Row 2: Tabel CoE + Tren Unit Bisnis --}}
    <div class="chart-grid-2">
        <div class="chart-card" style="margin-bottom:0">
            <div class="cc-head">
                <div class="cc-title">
                    <div class="cc-dot" style="background:#378ADD"></div>
                    Statistik Realisasi per CoE
                </div>
                <div class="cc-badge">{{ $filterTahun }}</div>
            </div>
            @php 
                $snap = $coeSnapshot ?? []; 
                $overallTargetSum = \App\Models\MasterTarget::where('periode', 'like', "%{$filterTahun}%")->sum('target');
            @endphp
            @if(count($snap))
                <div class="coe-tbl-wrap">
                <table class="coe-tbl">
                    <thead>
                        <tr>
                            <th>CoE</th>
                            <th style="text-align:right">Realisasi</th>
                            <th style="text-align:right">Kontribusi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($snap as $row)
                            @php
                                $contrib = $overallTargetSum > 0 ? round(($row['realisasi'] / $overallTargetSum) * 100, 1) : 0;
                                $isActive = $filterCoe && $filterCoe === ($row['coe'] ?? null);
                            @endphp
                            <tr style="{{ $isActive ? 'background:#F0F7FF;' : '' }}">
                                <td style="font-weight:600;color:#111827">
                                    {{ $row['coe'] }}
                                    @if($isActive)
                                        <span style="font-size:9px;color:#378ADD;margin-left:4px">● aktif</span>
                                    @endif
                                </td>
                                <td style="text-align:right;font-weight:600;color:#378ADD">{{ number_format($row['realisasi']) }}</td>
                                <td style="text-align:right">
                                    <div class="bar-wrap" style="justify-content: flex-end; gap: 8px;">
                                        <div class="bar-bg" style="width: 60px;">
                                            <div class="bar-fill" style="width:{{ min($contrib, 100) }}%;background:#378ADD"></div>
                                        </div>
                                        <span style="font-size:11px;font-weight:600;color:#378ADD;min-width:38px">
                                            {{ $contrib }}%
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
            @else
                <div class="empty-state">
                    <i class="fas fa-table"></i>
                    Belum ada data CoE.
                </div>
            @endif
        </div>

        <div class="chart-card" style="margin-bottom:0">
            <div class="cc-head">
                <div class="cc-title">
                    <div class="cc-dot" style="background:#993556"></div>
                    Tren Unit Bisnis / LSP
                </div>
                <div class="cc-badge" id="badgeUnitBisnis">–</div>
            </div>
            <div class="wrap-120">
                <canvas id="chartUnitBisnis"></canvas>
            </div>
        </div>
    </div>

    {{-- Row 3: 3 chart kecil --}}
    <div class="chart-grid-3" style="margin-top:10px">
        <div class="chart-card" style="margin-bottom:0">
            <div class="cc-head">
                <div class="cc-title">
                    <div class="cc-dot" style="background:#378ADD"></div>
                    Tren Pub. Nasional
                </div>
                <div class="cc-badge" id="badgePubNasional">–</div>
            </div>
            <div class="wrap-95">
                <canvas id="chartPubNasional"></canvas>
            </div>
        </div>
        <div class="chart-card" style="margin-bottom:0">
            <div class="cc-head">
                <div class="cc-title">
                    <div class="cc-dot" style="background:#1D9E75"></div>
                    Tren HKI
                </div>
                <div class="cc-badge" id="badgeHki">–</div>
            </div>
            <div class="wrap-95">
                <canvas id="chartHki"></canvas>
            </div>
        </div>
        <div class="chart-card" style="margin-bottom:0">
            <div class="cc-head">
                <div class="cc-title">
                    <div class="cc-dot" style="background:#7F77DD"></div>
                    Tren Jurnal Intl (Q1/Q2)
                </div>
                <div class="cc-badge" id="badgeIntlQ12">–</div>
            </div>
            <div class="wrap-95">
                <canvas id="chartIntlQ12"></canvas>
            </div>
        </div>
    </div>

    {{-- ── ANALISIS KINERJA DETIL PER KATEGORI (2-COLUMN GRID, LIMIT 5 ROWS) ── --}}
    @php
        $catDisplayName = [
            'Riset'      => 'Riset',
            'Bisnis'     => 'Bisnis ',
            'Pengabdian' => 'Pengabdian Masyarakat',
            'Akademik'   => 'Akademik',
            'Inovasi'    => 'Inovasi',
        ];
        $catDisplayLabel = [
            'Riset'      => 'Jenis Riset',
            'Bisnis'     => 'Jenis Bisnis',
            'Pengabdian' => 'Jenis Pengabdian',
            'Akademik'   => 'Jenis Akademik',
            'Inovasi'    => 'Jenis Inovasi',
        ];
        $catDisplayDesc = [
            'Riset'      => 'riset',
            'Bisnis'     => 'bisnis',
            'Pengabdian' => 'pengabdian',
            'Akademik'   => 'akademik',
            'Inovasi'    => 'inovasi',
        ];
    @endphp

    <div class="kpi-cat-grid">
        @foreach(['Riset', 'Bisnis', 'Pengabdian', 'Akademik', 'Inovasi'] as $katName)
            @php
                $rows = $categoryTables[$katName] ?? [];
                $totalTarget = array_sum(array_column($rows, 'target'));
                $totalRealisasi = array_sum(array_column($rows, 'realisasi'));
                $totalDev = $totalRealisasi - $totalTarget;
                $displayName = $catDisplayName[$katName] ?? $katName;
                $displayLabel = $catDisplayLabel[$katName] ?? 'Jenis';
                $displayDesc = $catDisplayDesc[$katName] ?? strtolower($katName);
            @endphp
            
            <div class="kpi-cat-card" style="margin-bottom: 0;">
                <div class="kpi-cat-title">Target vs Realisasi {{ $displayName }}</div>
                <div class="kpi-cat-subtitle">
                    Perbandingan target dan capaian {{ $displayDesc }} tahun {{ $filterTahun }} 
                    (Total Target: {{ number_format($totalTarget) }} | Total Realisasi: {{ number_format($totalRealisasi) }} | Deviasi: {{ ($totalDev >= 0 ? '+' : '') . number_format($totalDev) }})
                </div>

                @if(count($rows))
                    @php
                        $totalPages = ceil(count($rows) / 5);
                    @endphp
                    <div style="overflow-x: auto;">
                        <table class="kpi-cat-tbl">
                            <thead>
                                <tr>
                                    <th style="width: 40px; text-align: left;">No</th>
                                    <th style="text-align: left;">{{ $displayLabel }}</th>
                                    <th style="text-align: right; width: 70px;">Target</th>
                                    <th style="text-align: right; width: 70px;">Realisasi</th>
                                    <th style="text-align: right; width: 70px;">Deviasi</th>
                                    <th style="text-align: right; width: 140px;">Status</th>
                                </tr>
                            </thead>
                            <tbody class="kpi-cat-tbody" data-current-page="1">
                                @foreach($rows as $i => $row)
                                    @php
                                        $dev = $row['deviasi'];
                                        $over = $dev >= 0;
                                        $page = floor($i / 5) + 1;
                                    @endphp
                                    <tr class="kpi-cat-row" data-page="{{ $page }}" style="display: {{ $page == 1 ? 'table-row' : 'none' }};">
                                        <td data-label="No" style="font-weight: 700; color: #0f172a; text-align: left;">
                                            {{ $i + 1 }}
                                        </td>
                                        <td data-label="{{ $displayLabel }}" style="font-weight: 500; color: #334155; text-align: left; max-width: 260px; word-wrap: break-word; font-size: 12px; line-height: 1.3;">
                                            {{ $row['judul'] }}
                                        </td>
                                        <td data-label="Target" style="text-align: right; font-weight: 500; color: #475569;">
                                            {{ number_format($row['target']) }}
                                        </td>
                                        <td data-label="Realisasi" style="text-align: right; font-weight: 600; color: #1e293b;">
                                            {{ number_format($row['realisasi']) }}
                                        </td>
                                        <td data-label="Deviasi" style="text-align: right; font-weight: 700; color: {{ $over ? '#10b981' : '#ef4444' }}">
                                            {{ $over ? '+' : '' }}{{ number_format($dev) }}
                                        </td>
                                        <td data-label="Status" style="text-align: right;">
                                            @if($over)
                                                <span class="status-pill status-pill-up" style="padding: 4px 10px; font-size: 10px;">
                                                    <i class="fas fa-arrow-trend-up"></i> Melampaui
                                                 </span>
                                            @else
                                                <span class="status-pill status-pill-dn" style="padding: 4px 10px; font-size: 10px;">
                                                    <i class="fas fa-arrow-trend-down"></i> Di Bawah
                                                </span>
                                            @endif
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
                    <div class="empty-state" style="padding: 2rem 1rem; text-align: center;">
                        <i class="fas fa-folder-open" style="font-size: 2rem; color: #cbd5e1; margin-bottom: 0.5rem;"></i>
                        <p style="color: #94a3b8; font-size: 13px;">Tidak ada data untuk kategori {{ $displayName }} di tahun {{ $filterTahun }}.</p>
                    </div>
                @endif
            </div>
        @endforeach
    </div>

</div>
@endsection

@push('scripts')
<script>
/* ── Data dari Blade ──────────────────────────────────────── */
const tahunRange   = @json($tahunRange   ?? []);
const trenData     = @json($trenData     ?? []);
const triwulanKpi  = @json($triwulanKpi  ?? []);
const filterCoe    = @json($filterCoe    ?? null);
const filterCoeName= @json($filterCoeName ?? 'Semua CoE');
const filterTahun  = "{{ $filterTahun }}";

/* ── Konstanta warna ──────────────────────────────────────── */
const COLOR = {
    blue:   '#378ADD',
    green:  '#1D9E75',
    amber:  '#BA7517',
    pink:   '#993556',
    purple: '#7F77DD',
};

/* ── Defaults Chart.js ───────────────────────────────────── */
Chart.defaults.font.family = 'ui-sans-serif,system-ui,sans-serif';
Chart.defaults.font.size   = 11;
Chart.defaults.color       = '#9ca3af';

/* ── Helper: opsi dasar untuk semua line chart ───────────── */
function baseOpts(yMax) {
    return {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: ctx => ` ${ctx.dataset.label}: ${ctx.parsed.y}`,
                },
            },
        },
        scales: {
            x: {
                grid: { display: false },
                ticks: { font: { size: 10 } },
            },
            y: {
                beginAtZero: true,
                max: yMax,
                grid: { color: 'rgba(0,0,0,.04)' },
                ticks: { precision: 0, font: { size: 10 }, maxTicksLimit: 4 },
            },
        },
    };
}

/* ── Helper: buat satu dataset garis ────────────────────── */
function mkDs(label, data, color, filled = true) {
    return {
        label,
        data,
        borderColor: color,
        backgroundColor: filled ? color + '18' : 'transparent',
        borderWidth: 2,
        pointRadius: 3,
        tension: 0.4,
        fill: filled,
    };
}

/* ── Helper: buat line chart sederhana (1 garis) ─────────── */
function mkSimpleChart(canvasId, labels, dsLabel, data, color, badgeId) {
    const yMax = (Math.max(...data, 1)) * 1.25;

    new Chart(document.getElementById(canvasId), {
        type: 'line',
        data: {
            labels,
            datasets: [ mkDs(dsLabel, data, color) ],
        },
        options: baseOpts(yMax),
    });

    if (badgeId) {
        const last = data.length ? data[data.length - 1] : 0;
        document.getElementById(badgeId).textContent = last + ' · ' + filterTahun;
    }
}

/* ── 1. Tren Capaian per Triwulan (Realisasi vs Target) ─── */
(function () {
    const labels  = triwulanKpi.map(t => t.tw);
    const reals   = triwulanKpi.map(t => t.realisasi);
    const targets = triwulanKpi.map(t => t.target);
    const yMax    = Math.max(...reals, ...targets, 1) * 1.15;

    new Chart(document.getElementById('chartCapaianTw'), {
        type: 'line',
        data: {
            labels,
            datasets: [
                {
                    label: 'Realisasi',
                    data: reals,
                    borderColor: COLOR.blue,
                    backgroundColor: COLOR.blue + '18',
                    borderWidth: 2,
                    pointRadius: 3.5,
                    tension: 0.4,
                    fill: true,
                },
                {
                    label: 'Target',
                    data: targets,
                    borderColor: COLOR.amber,
                    backgroundColor: 'transparent',
                    borderWidth: 1.5,
                    borderDash: [5, 4],
                    pointRadius: 3,
                    tension: 0.4,
                    fill: false,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom',
                    labels: {
                        boxWidth: 8, padding: 10,
                        font: { size: 10 },
                        usePointStyle: true, pointStyle: 'circle',
                    },
                },
                tooltip: {
                    callbacks: { label: c => ` ${c.dataset.label}: ${c.parsed.y}` },
                },
            },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 10 } } },
                y: { beginAtZero: true, max: yMax, grid: { color: 'rgba(0,0,0,.04)' }, ticks: { precision: 0 } },
            },
        },
    });
})();

const dsLabel = filterCoe ? filterCoeName : 'Total';

(function () {
    const d = trenData['unit_bisnis'] || { labels: tahunRange, data: [] };
    mkSimpleChart('chartUnitBisnis', d.labels, dsLabel, d.data, COLOR.pink, 'badgeUnitBisnis');
})();

(function () {
    const d = trenData['pub_nasional'] || { labels: tahunRange, data: [] };
    mkSimpleChart('chartPubNasional', d.labels, dsLabel, d.data, COLOR.blue, 'badgePubNasional');
})();

(function () {
    const d = trenData['hki'] || { labels: tahunRange, data: [] };
    mkSimpleChart('chartHki', d.labels, dsLabel, d.data, COLOR.green, 'badgeHki');
})();

(function () {
    const d = trenData['intl_q12'] || { labels: tahunRange, data: [] };
    mkSimpleChart('chartIntlQ12', d.labels, dsLabel, d.data, COLOR.purple, 'badgeIntlQ12');
})();

/* Client-side pagination helper for KPI category tables */
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
    
    // Show/hide rows
    rows.forEach(row => {
        const page = parseInt(row.getAttribute('data-page'));
        if (page === currentPage) {
            row.style.display = 'table-row';
        } else {
            row.style.display = 'none';
        }
    });
    
    // Enable/disable buttons
    prevBtn.disabled = (currentPage === 1);
    nextBtn.disabled = (currentPage === totalPages);
}
</script>
@endpush