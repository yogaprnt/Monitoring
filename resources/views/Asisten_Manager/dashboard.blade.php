@extends('layouts.app')

@section('title', 'Dashboard Asisten Manager - RI CCSL')

@push('styles')
<style>
    .font-display { font-family: 'Plus Jakarta Sans', sans-serif; }

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

    /* ── KPI Cards ── */
    .kpi-grid { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 16px; }
    @media(max-width: 1340px) { .kpi-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
    @media(max-width: 920px)  { .kpi-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media(max-width: 600px)  { .kpi-grid { grid-template-columns: minmax(0, 1fr); } }

    .kpi-icon-wrap {
        width: 38px; height: 38px; border-radius: 12px;
        background: rgba(255,255,255,0.2);
        display: flex; align-items: center; justify-content: center;
        margin-bottom: 12px;
    }
    .kpi-icon-wrap i { font-size: 15px; }
    .kpi-lbl  { font-size: 11px; font-weight: 700; margin-bottom: 6px; color: #64748b; letter-spacing: 0.2px; }
    .kpi-val  { font-size: 28px; font-weight: 800; color: #0f172a; line-height: 1; margin-bottom: 10px; letter-spacing: -1px; flex-wrap: wrap; }
    .kpi-pill {
        display: inline-flex; align-items: center; gap: 4px;
        font-size: 11px; font-weight: 700; padding: 3px 9px;
        border-radius: 99px;
        max-width: 100%;
    }
    .kpi-bar  { height: 4px; border-radius: 99px; background: #f1f5f9; margin-top: 12px; overflow: hidden; }
    .kpi-bar-fill { height: 100%; border-radius: 99px; background: linear-gradient(90deg, #3b82f6, #06b6d4); }
    .kpi-capaian { font-size: 10px; color: #94a3b8; font-weight: 600; margin-top: 5px; flex-wrap: wrap; gap: 4px; }

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

    /* ---------- CHART CARDS ---------- */
    .chart-card {
        background: #ffffff;
        border: 1px solid rgba(241, 245, 249, 1);
        border-radius: 16px;
        padding: 22px 24px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
        transition: box-shadow 0.3s;
        margin-bottom: 16px;
    }
    .chart-card:hover {
        box-shadow: 0 12px 20px -8px rgba(0,0,0,0.06);
    }
    .chart-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px; }
    .chart-title-wrap { display: flex; align-items: center; gap: 10px; }
    .chart-dot { width: 10px; height: 10px; border-radius: 999px; background: #3b82f6; box-shadow: 0 0 8px rgba(59, 130, 246, 0.5); }
    .chart-title { font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 700; font-size: 15px; color: #0f172a; }
    .chart-sub { font-size: 12px; color: #64748b; margin-top: 2px; }
    .chart-latest {
        font-size: 11px; font-weight: 700; color: #475569;
        background: #f1f5f9; padding: 4px 10px; border-radius: 999px;
        border: 1px solid #e2e8f0;
    }

    .sec-title {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 12px; font-weight: 800; text-transform: uppercase;
        letter-spacing: .08em; color: #475569; margin-bottom: 16px;
        display: flex; align-items: center; gap: 10px;
    }
    .sec-title::after { content: ''; flex: 1; height: 1px; background: #e2e8f0; }

    .badge-coe {
        display: inline-flex; align-items: center; gap: 7px;
        font-size: 12px; font-weight: 700;
        padding: 6px 14px; border-radius: 10px;
        background: #fef3c7; color: #92400e;
        border: 1px solid #fde68a;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(253, 230, 138, 0.15);
    }

    /* ---------- GAP & PROBLEM PANEL ---------- */
    .gap-card {
        background: #ffffff;
        border: 1px solid rgba(241, 245, 249, 1);
        border-radius: 16px;
        padding: 8px 0;
        overflow: hidden;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
    }
    .gap-row {
        display: flex; align-items: center; justify-content: space-between;
        padding: 14px 24px; border-bottom: 1px solid #f1f5f9;
        gap: 16px;
        border-left: 4px solid #ef4444;
        transition: background 0.2s;
    }
    .gap-row:hover { background: #fff5f5; }
    .gap-row:last-child { border-bottom: none; }
    .gap-info { display: flex; flex-direction: column; gap: 3px; min-width: 0; }
    .gap-judul { font-size: 13.5px; font-weight: 600; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 480px; }
    .gap-meta { font-size: 12px; color: #64748b; }
    .gap-meta b { color: #334155; font-weight: 600; }
    .gap-amount {
        font-size: 13px; font-weight: 700; color: #be185d;
        background: #fff1f2; padding: 5px 14px; border-radius: 999px;
        border: 1px solid #fecdd3;
        white-space: nowrap; flex-shrink: 0;
        display: inline-flex; align-items: center; gap: 4px;
    }
    .gap-empty {
        text-align: center; padding: 42px 24px; color: #64748b; font-size: 13.5px;
    }
    .gap-empty i { font-size: 32px; color: #a7f3d0; margin-bottom: 10px; display: block; }

    /* ---------- DETAIL TABLE ---------- */
    .detail-card {
        background: #ffffff;
        border: 1px solid rgba(241, 245, 249, 1);
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
    }
    .detail-table { width: 100%; border-collapse: collapse; font-size: 13.5px; }
    .detail-table thead th {
        text-align: left; font-size: 11px; font-weight: 800; letter-spacing: .05em;
        text-transform: uppercase; color: #ffffff;
        background: #1a2e4a; padding: 14px 20px; border-bottom: 1px solid #e2e8f0;
        white-space: nowrap;
    }
    .detail-table tbody td {
        padding: 14px 20px; border-bottom: 1px solid #f1f5f9; color: #334155;
        vertical-align: middle;
    }
    .detail-table tbody tr:last-child td { border-bottom: none; }
    .detail-table tbody tr:hover { background: #f8fafc; }
    .dt-judul { font-weight: 600; color: #0f172a; max-width: 300px; }
    .dt-badge {
        font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 999px;
        display: inline-block;
    }
    .dt-badge-coe { background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
    .dt-pos { color: #047857; font-weight: 700; }
    .dt-neg { color: #be185d; font-weight: 700; }

    /* ---------- PAGINATION ---------- */
    .table-foot {
        padding: 14px 20px;
        font-size: 12.5px;
        color: #64748b;
        border-top: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        background: #f8fafc;
    }
    .pagination-wrap {
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .pg-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 6px 14px;
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        background: #ffffff;
        font-size: 12.5px;
        font-weight: 600;
        color: #334155;
        cursor: pointer;
        transition: all 0.2s;
        font-family: 'Inter', sans-serif;
        white-space: nowrap;
        box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    }
    .pg-btn:hover:not(:disabled) {
        background: #f1f5f9;
        border-color: #94a3b8;
        color: #0f172a;
    }
    .pg-btn:disabled {
        opacity: 0.45;
        cursor: default;
    }
    .pg-num {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        background: #ffffff;
        font-size: 12.5px;
        font-weight: 700;
        color: #475569;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
        font-family: 'Inter', sans-serif;
        box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    }
    .pg-num:hover { background: #f1f5f9; border-color: #94a3b8; color: #0f172a; }
    .pg-num.active {
        background: #3b82f6;
        border-color: #3b82f6;
        color: #ffffff;
        box-shadow: 0 4px 8px rgba(59, 130, 246, 0.3);
    }
    .pg-ellipsis {
        font-size: 13px; color: #94a3b8;
        display: inline-flex; align-items: center;
        padding: 0 4px;
    }
    
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
</style>
@endpush

@section('content')
<div class="content">

    {{-- Page heading --}}
    <div class="flex items-end justify-between flex-wrap gap-4 mb-6">
        <div>
            <h1 class="page-title flex items-center gap-2">
                Dashboard Asisten Manager
            </h1>
            <p class="page-subtitle">Ringkasan indikator KPI utama dan capaian per triwulan · {{ $tahun }}</p>
        </div>
    </div>

    {{-- CHECK DATA --}}
    @if($coeList->isEmpty())
        <div class="bg-white p-10 text-center rounded-xl border border-gray-100">
            <i class="fas fa-folder-open text-gray-300 text-3xl mb-3"></i>
            <div class="text-gray-500 text-sm">Belum ada data untuk ditampilkan.</div>
        </div>
    @else

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

        <div class="sec-lbl">Triwulan KPI — {{ $tahun }}</div>
        <div class="tw-grid">
            @foreach($triwulan as $tw)
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

        {{-- TREN CAPAIAN PER TRIWULAN --}}
        <div class="chart-card mb-8">
            <div class="chart-head">
                <div>
                    <div class="chart-title-wrap">
                        <span class="chart-dot"></span>
                        <span class="chart-title">Tren Capaian per Triwulan</span>
                    </div>
                    <div class="chart-sub">Realisasi total lintas TW &middot; {{ $tahun }}</div>
                </div>
                <span class="chart-latest" id="latest-c0">—</span>
            </div>
            <div style="height:220px"><canvas id="c0"></canvas></div>
        </div>

        {{-- CHARTS --}}
        <div class="sec-title">Tren COE</div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            <div class="chart-card">
                <div class="chart-head">
                    <div class="chart-title-wrap">
                        <span class="chart-dot"></span>
                        <span class="chart-title">Tren Publikasi Nasional</span>
                    </div>
                    <span class="chart-latest" id="latest-c1">—</span>
                </div>
                <div style="height:220px"><canvas id="c1"></canvas></div>
            </div>

            <div class="chart-card">
                <div class="chart-head">
                    <div class="chart-title-wrap">
                        <span class="chart-dot"></span>
                        <span class="chart-title">Tren HKI</span>
                    </div>
                    <span class="chart-latest" id="latest-c2">—</span>
                </div>
                <div style="height:220px"><canvas id="c2"></canvas></div>
            </div>

            <div class="chart-card">
                <div class="chart-head">
                    <div class="chart-title-wrap">
                        <span class="chart-dot"></span>
                        <span class="chart-title">Tren Unit Bisnis / LSP</span>
                    </div>
                    <span class="chart-latest" id="latest-c3">—</span>
                </div>
                <div style="height:220px"><canvas id="c3"></canvas></div>
            </div>

            <div class="chart-card">
                <div class="chart-head">
                    <div class="chart-title-wrap">
                        <span class="chart-dot"></span>
                        <span class="chart-title">Tren Jurnal Internasional (Q3/Q4)</span>
                    </div>
                    <span class="chart-latest" id="latest-c4">—</span>
                </div>
                <div style="height:220px"><canvas id="c4"></canvas></div>
            </div>

            <div class="chart-card md:col-span-2">
                <div class="chart-head">
                    <div class="chart-title-wrap">
                        <span class="chart-dot"></span>
                        <span class="chart-title">Tren Jurnal Internasional (Q1/Q2)</span>
                    </div>
                    <span class="chart-latest" id="latest-c5">—</span>
                </div>
                <div style="height:220px"><canvas id="c5"></canvas></div>
            </div>

        </div>

        {{-- GAP & PROBLEM --}}
        <div class="sec-title mt-8">Gap &amp; Problem <span style="font-weight:600;color:#B91C1C;text-transform:none;letter-spacing:0">({{ $totalMasalah }} indikator di bawah target)</span></div>

        <div class="gap-card mb-8">
            @forelse($topGaps as $gap)
                <div class="gap-row">
                    <div class="gap-info">
                        <div class="gap-judul" title="{{ $gap->judul }}">{{ $gap->judul }}</div>
                        <div class="gap-meta">
                            <b>{{ $gap->kategori }}</b> · {{ $gap->periode }}
                            · Realisasi Gabungan: <b>{{ $gap->realisasi }}</b> / Target Overall: <b>{{ $gap->target }}</b>
                        </div>
                    </div>
                    <div class="gap-amount">
                        <i class="fas fa-arrow-down"></i> {{ $gap->deviasi }} output
                    </div>
                </div>
            @empty
                <div class="gap-empty">
                    <i class="fas fa-circle-check"></i>
                    Tidak ada gap. Semua data sudah memenuhi atau melampaui target. 🎉
                </div>
            @endforelse
        </div>

        {{-- DETAIL DATA PER INPUT --}}
        <div class="sec-title">
            Detail Data per Input
            <span style="font-weight:600;color:#9ca3af;text-transform:none;letter-spacing:0">
                ({{ $allData->count() }} data)
            </span>
        </div>

        <div class="detail-card mb-8">
            <div class="overflow-x-auto">
                <table class="detail-table">
                    <thead>
                        <tr>
                            <th>Kategori</th>
                            <th>CoE</th>
                            <th>Judul / Indikator</th>
                            <th>Periode</th>
                            <th>Realisasi</th>
                            <th>Diinput Oleh</th>
                        </tr>
                    </thead>
                    <tbody id="detail-data-body">
                        @forelse($allData as $row)
                        <tr class="dt-all-row">
                            <td>{{ $row->kategori }}</td>
                            <td><span class="dt-badge dt-badge-coe">{{ $row->coe }}</span></td>
                            <td class="dt-judul" title="{{ $row->judul }}">{{ $row->judul }}</td>
                            <td>{{ $row->periode }}</td>
                            <td class="font-bold text-[#3b82f6]">{{ $row->realisasi }}</td>
                            <td>{{ $row->penginput->name ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="gap-empty">Tidak ada data untuk filter yang dipilih.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- PAGINATION FOOTER --}}
            <div class="table-foot">
                <span id="pg-info-text" style="color:#6b7280">—</span>

                <div class="pagination-wrap">
                    <button id="pg-btn-prev" class="pg-btn" onclick="pgChangePage(pgState.current - 1)" disabled>
                        <i class="fas fa-chevron-left" style="font-size:10px"></i> Sebelumnya
                    </button>

                    <div id="pg-numbers" style="display:flex;gap:4px;align-items:center"></div>

                    <button id="pg-btn-next" class="pg-btn" onclick="pgChangePage(pgState.current + 1)" disabled>
                        Selanjutnya <i class="fas fa-chevron-right" style="font-size:10px"></i>
                    </button>
                </div>
            </div>
        </div>

    @endif
</div>
@endsection

@push('scripts')
<script>
var CHART_YEARS = @json($chartYears ?? []);
var CHART_COES  = @json($chartCoes ?? []);

var TW_LABELS    = @json($triwulanChartLabels ?? []);
var TW_REALISASI = @json($triwulanChartRealisasi ?? []);

var LINE_PALETTE = ['#1a2e4a', '#3b6ea5', '#7da7cc', '#9b6b3c', '#5a8f7b', '#a85d6b'];

Chart.defaults.font.family = "'Inter', sans-serif";
Chart.defaults.font.size = 11;
Chart.defaults.color = '#8a93a3';

function mkDatasets(key){
    return CHART_COES.map((c, i) => ({
        label: c.name,
        data: c.data[key] || [],
        borderColor: c.color || LINE_PALETTE[i % LINE_PALETTE.length],
        backgroundColor: (c.color || LINE_PALETTE[i % LINE_PALETTE.length]) + '14',
        borderWidth: 2.25,
        tension: 0.35,
        pointRadius: 3,
        pointHoverRadius: 5,
        pointBackgroundColor: '#fff',
        pointBorderWidth: 2,
        pointBorderColor: c.color || LINE_PALETTE[i % LINE_PALETTE.length],
        fill: false,
    }));
}

var baseOptions = {
    responsive: true,
    maintainAspectRatio: false,
    interaction: { mode: 'index', intersect: false },
    plugins: {
        legend: {
            position: 'bottom',
            labels: {
                usePointStyle: true,
                pointStyle: 'circle',
                boxWidth: 7,
                boxHeight: 7,
                padding: 14,
                font: { size: 11, weight: '500' }
            }
        },
        tooltip: {
            backgroundColor: '#1a2e4a',
            titleFont: { size: 12, weight: '700' },
            bodyFont: { size: 12 },
            padding: 10,
            cornerRadius: 8,
            displayColors: true,
            boxPadding: 4,
        }
    },
    scales: {
        x: {
            grid: { display: false },
            border: { display: false },
            ticks: { font: { size: 11 } }
        },
        y: {
            beginAtZero: true,
            grid: { color: '#F1F3F7' },
            border: { display: false },
            ticks: { font: { size: 11 }, precision: 0 }
        }
    }
};

// ---------- CHART c0: TREN CAPAIAN PER TRIWULAN ----------
(function(){
    var el = document.getElementById('c0');
    if (!el) return;
    new Chart(el, {
        type: 'line',
        data: {
            labels: TW_LABELS,
            datasets: [{
                label: 'Realisasi',
                data: TW_REALISASI,
                borderColor: '#3b6ea5',
                backgroundColor: 'rgba(59, 110, 165, 0.12)',
                borderWidth: 2.5,
                tension: 0.35,
                pointRadius: 4,
                pointHoverRadius: 6,
                pointBackgroundColor: '#3b6ea5',
                pointBorderWidth: 2,
                pointBorderColor: '#fff',
                fill: true,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1a2e4a',
                    titleFont: { size: 12, weight: '700' },
                    bodyFont: { size: 12 },
                    padding: 10,
                    cornerRadius: 8,
                    callbacks: {
                        label: function(ctx){ return 'Realisasi: ' + ctx.parsed.y + ' output'; }
                    }
                }
            },
            scales: {
                x: { grid: { display: false }, border: { display: false }, ticks: { font: { size: 11 } } },
                y: { beginAtZero: true, grid: { color: '#F1F3F7' }, border: { display: false }, ticks: { font: { size: 11 }, precision: 0 } }
            }
        }
    });
    var latestEl = document.getElementById('latest-c0');
    if (latestEl && TW_REALISASI.length) {
        var lastIdx = TW_REALISASI.length - 1;
        var lastLabel = TW_LABELS[lastIdx] ?? '';
        latestEl.textContent = lastLabel ? (TW_REALISASI[lastIdx] + ' · ' + lastLabel) : TW_REALISASI[lastIdx];
    }
})();

['pub_nasional','hki','unit_bisnis','intl_selain_q12','intl_q12']
.forEach((key, i) => {
    var el = document.getElementById('c' + (i + 1));
    if (!el) return;
    var datasets = mkDatasets(key);
    new Chart(el, { type: 'line', data: { labels: CHART_YEARS, datasets: datasets }, options: baseOptions });
    var latestEl = document.getElementById('latest-c' + (i + 1));
    if (latestEl && datasets.length) {
        var lastIdx = (CHART_YEARS.length || 1) - 1;
        var total = datasets.reduce((sum, d) => sum + (Number(d.data[lastIdx]) || 0), 0);
        var yearLabel = CHART_YEARS[lastIdx] ?? '';
        latestEl.textContent = yearLabel ? (total + ' · ' + yearLabel) : total;
    }
});

// ============================================================
// PAGINATION — Detail Data per Input
// ============================================================
var pgState = { current: 1, perPage: 5, total: 0, totalPages: 1 };

(function initPagination() {
    var allRows = document.querySelectorAll('#detail-data-body .dt-all-row');
    pgState.total      = allRows.length;
    pgState.totalPages = Math.max(1, Math.ceil(pgState.total / pgState.perPage));

    if (pgState.total === 0) {
        // No rows — hide footer controls
        document.getElementById('pg-info-text').textContent = 'Tidak ada data.';
        document.getElementById('pg-btn-prev').style.display = 'none';
        document.getElementById('pg-btn-next').style.display = 'none';
        return;
    }

    pgRender(1);
})();

function pgChangePage(page) {
    pgRender(page);
}

function pgRender(page) {
    page = Math.min(Math.max(1, page), pgState.totalPages);
    pgState.current = page;

    var allRows = document.querySelectorAll('#detail-data-body .dt-all-row');
    var start   = (page - 1) * pgState.perPage;
    var end     = start + pgState.perPage;

    allRows.forEach(function(row, idx) {
        row.style.display = (idx >= start && idx < end) ? '' : 'none';
    });

    // Info text
    var infoEl = document.getElementById('pg-info-text');
    if (infoEl) {
        var showing = Math.min(end, pgState.total);
        infoEl.textContent =
            'Menampilkan ' + (start + 1) + '–' + showing + ' dari ' + pgState.total + ' data approved.';
    }

    // Prev / Next buttons
    var btnPrev = document.getElementById('pg-btn-prev');
    var btnNext = document.getElementById('pg-btn-next');
    if (btnPrev) btnPrev.disabled = page === 1;
    if (btnNext) btnNext.disabled = page === pgState.totalPages;

    // Page number buttons (with ellipsis for large sets)
    var numbersEl = document.getElementById('pg-numbers');
    if (!numbersEl) return;
    numbersEl.innerHTML = '';

    var total = pgState.totalPages;
    var cur   = page;

    // Build visible page list: always show first, last, cur±1, with '…' gaps
    var pages = buildPageList(cur, total);

    pages.forEach(function(p) {
        if (p === '…') {
            var dot = document.createElement('span');
            dot.className = 'pg-ellipsis';
            dot.textContent = '…';
            numbersEl.appendChild(dot);
        } else {
            var btn = document.createElement('button');
            btn.className = 'pg-num' + (p === cur ? ' active' : '');
            btn.textContent = p;
            btn.onclick = (function(pg){ return function(){ pgRender(pg); }; })(p);
            numbersEl.appendChild(btn);
        }
    });
}

function buildPageList(cur, total) {
    if (total <= 7) {
        // Show all pages
        var arr = [];
        for (var i = 1; i <= total; i++) arr.push(i);
        return arr;
    }

    var result = [];
    var left  = Math.max(2, cur - 1);
    var right = Math.min(total - 1, cur + 1);

    result.push(1);

    if (left > 2) result.push('…');

    for (var p = left; p <= right; p++) result.push(p);

    if (right < total - 1) result.push('…');

    result.push(total);
    return result;
}
</script>
@endpush