@extends('layouts.app')

@section('title', 'Approval Manager - RI CCSL')

@push('styles')
<style>
    /* HERO BANNER */
    .approval-banner{background:linear-gradient(135deg,#1b2436 0%,#2c3b59 100%);border-radius:20px;padding:28px 32px;margin-bottom:28px;box-shadow:0 8px 30px rgba(27,36,54,.18);position:relative;overflow:hidden;display:flex;align-items:center;justify-content:space-between;gap:16px;}
    .approval-banner::before{content:'';position:absolute;top:-50px;right:-50px;width:220px;height:220px;background:radial-gradient(circle,rgba(255,255,255,.06) 0%,transparent 70%);border-radius:50%;}
    .approval-banner::after{content:'';position:absolute;bottom:-30px;left:40%;width:140px;height:140px;background:radial-gradient(circle,rgba(255,255,255,.04) 0%,transparent 70%);border-radius:50%;}
    .banner-text{position:relative;z-index:1;}
    .banner-title{font-size:1.45rem;font-weight:800;color:#fff;margin-bottom:4px;}
    .banner-sub{font-size:13px;color:rgba(255,255,255,.6);font-weight:500;}
    .banner-icon{position:relative;z-index:1;width:64px;height:64px;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.15);border-radius:16px;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
    .banner-icon i{font-size:26px;color:rgba(255,255,255,.85);}
    
    /* STAT CARDS */
    .stat-grid{display:grid;grid-template-columns:repeat(5,1fr);gap:14px;margin-bottom:24px;}
    @media(max-width:1100px){.stat-grid{grid-template-columns:repeat(3,1fr);}}
    @media(max-width:640px){.stat-grid{grid-template-columns:repeat(2,1fr);}}
    .stat-card{border-radius:16px;padding:18px 20px;display:flex;align-items:center;gap:14px;box-shadow:0 4px 16px rgba(0,0,0,.06);border:1px solid rgba(255,255,255,.12);transition:transform .2s,box-shadow .2s;cursor:default;position:relative;overflow:hidden;}
    .stat-card:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(0,0,0,.1);}
    .stat-card::after{content:'';position:absolute;top:-20px;right:-20px;width:60px;height:60px;background:rgba(255,255,255,.08);border-radius:50%;}
    .stat-icon{width:42px;height:42px;border-radius:12px;background:rgba(255,255,255,.18);border:1px solid rgba(255,255,255,.12);display:flex;align-items:center;justify-content:center;flex-shrink:0;backdrop-filter:blur(4px);}
    .stat-icon i{font-size:16px;color:#fff;}
    .stat-label{font-size:10px;font-weight:700;color:rgba(255,255,255,.65);letter-spacing:.5px;text-transform:uppercase;margin-bottom:2px;}
    .stat-count{font-size:26px;font-weight:800;color:#fff;line-height:1;}
    .stat-sub{font-size:10px;color:rgba(255,255,255,.5);font-weight:600;margin-top:2px;}
    
    /* ALERT BANNER */
    .alert-banner{display:flex;align-items:center;gap:12px;padding:14px 20px;border-radius:12px;margin-bottom:20px;font-size:13.5px;font-weight:600;}
    .alert-banner i{font-size:18px;flex-shrink:0;}
    
    /* TABLE SECTION */
    .table-card{background:#fff;border-radius:16px;border:1px solid var(--line);box-shadow:0 2px 12px rgba(20,30,50,.06);overflow:hidden;}
    .table-header{padding:20px 24px;border-bottom:1px solid var(--line);display:flex;align-items:center;justify-content:space-between;gap:12px;}
    .table-title{font-size:15px;font-weight:700;color:var(--ink);}
    .table-sub{font-size:12px;color:var(--muted);margin-top:2px;}
    .count-pill{background:var(--navy-100);color:var(--navy-700);font-size:12px;font-weight:700;padding:5px 14px;border-radius:999px;}
    .tbl{width:100%;border-collapse:collapse;}
    .tbl thead tr{background:linear-gradient(135deg,var(--navy-800),var(--navy-900));}
    .tbl thead th{padding:13px 16px;text-align:left;font-size:11.5px;font-weight:700;color:rgba(255,255,255,.8);letter-spacing:.4px;text-transform:uppercase;white-space:nowrap;}
    .tbl thead th:last-child{text-align:center;}
    .tbl tbody tr{border-bottom:1px solid #f1f5f9;transition:background .12s;}
    .tbl tbody tr:last-child{border-bottom:none;}
    .tbl tbody tr:hover{background:#f8fafc;}
    .tbl td{padding:14px 16px;font-size:13.5px;color:var(--ink);}
    .row-num{color:var(--muted);font-weight:600;font-size:12.5px;}
    .tipe-badge{display:inline-flex;align-items:center;gap:5px;padding:4px 11px;border-radius:999px;font-size:11.5px;font-weight:700;}
    .judul-cell{max-width:280px;}
    .judul-main{font-weight:700;color:var(--ink);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
    .penginput-cell{display:flex;align-items:center;gap:8px;}
    .penginput-avatar{width:28px;height:28px;border-radius:50%;background:linear-gradient(135deg,var(--navy-800),#378ADD);color:#fff;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;flex-shrink:0;}
    .penginput-name{font-weight:600;color:var(--ink);font-size:13px;}
    .date-cell{font-size:12.5px;color:var(--muted);font-weight:500;}
    .status-badge{display:inline-flex;align-items:center;gap:5px;padding:4px 11px;border-radius:999px;font-size:11.5px;font-weight:700;}
    .btn-detail{display:inline-flex;align-items:center;gap:5px;background:linear-gradient(135deg,#378ADD,#1a5fa8);color:#fff;font-size:12px;font-weight:700;padding:7px 14px;border-radius:8px;text-decoration:none;transition:all .15s;border:none;}
    .btn-detail:hover{transform:translateY(-1px);box-shadow:0 4px 12px rgba(55,138,221,.35);}
    .empty-state{padding:60px 20px;text-align:center;}
    .empty-state i{font-size:40px;color:#cbd5e1;display:block;margin-bottom:12px;}
    .empty-state .empty-title{font-weight:700;color:#64748b;font-size:15px;margin-bottom:4px;}
    .empty-state .empty-sub{font-size:12.5px;color:#94a3b8;}
    
    /* MODAL */
    .modal-backdrop-custom{background-color:rgba(15,23,42,.6);backdrop-filter:blur(4px);}
    .modal-box{background:#fff;border-radius:20px;box-shadow:0 20px 60px rgba(0,0,0,.18);width:100%;max-width:460px;overflow:hidden;}
    .modal-header{padding:24px 24px 20px;border-bottom:1px solid var(--line);display:flex;align-items:center;gap:14px;}
    .modal-icon{width:44px;height:44px;border-radius:12px;background:#fee2e2;color:#dc2626;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:18px;}
    .modal-title{font-size:16px;font-weight:800;color:var(--ink);}
    .modal-subtitle{font-size:12.5px;color:var(--muted);margin-top:2px;}
    .modal-body{padding:20px 24px;}
    .form-label{font-size:12.5px;font-weight:700;color:#374151;margin-bottom:6px;display:block;}
    .form-info{background:#f8fafc;border:1px solid var(--line);border-radius:10px;padding:10px 14px;font-size:13px;color:var(--ink);margin-bottom:16px;}
    .form-textarea{width:100%;border:1.5px solid var(--line);border-radius:10px;padding:10px 14px;font-size:13px;font-family:inherit;resize:none;transition:border .15s,box-shadow .15s;outline:none;}
    .form-textarea:focus{border-color:#ef4444;box-shadow:0 0 0 3px rgba(239,68,68,.1);}
    .form-hint{font-size:11px;color:var(--muted);margin-top:5px;}
    .modal-footer{padding:16px 24px;background:#f8fafc;border-top:1px solid var(--line);display:flex;justify-content:flex-end;gap:10px;}
    .btn-cancel{padding:8px 18px;font-size:13px;font-weight:700;color:#64748b;background:#fff;border:1.5px solid var(--line);border-radius:9px;cursor:pointer;transition:all .15s;}
    .btn-cancel:hover{background:var(--navy-100);color:var(--navy-800);}
    .btn-reject-confirm{display:inline-flex;align-items:center;gap:7px;padding:8px 18px;font-size:13px;font-weight:700;color:#fff;background:linear-gradient(135deg,#dc2626,#b91c1c);border:none;border-radius:9px;cursor:pointer;transition:all .15s;}
    .btn-reject-confirm:hover{box-shadow:0 4px 14px rgba(220,38,38,.35);transform:translateY(-1px);}
    
    /* Flash alerts */
    .flash-ok{background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d;padding:12px 16px;border-radius:10px;margin-bottom:16px;font-size:13.5px;font-weight:600;display:flex;align-items:center;gap:8px;}
    .flash-err{background:#fef2f2;border:1px solid #fecaca;color:#dc2626;padding:12px 16px;border-radius:10px;margin-bottom:16px;font-size:13.5px;font-weight:600;display:flex;align-items:center;gap:8px;}
</style>
@endpush

@section('content')
<div class="content">

    <!-- HERO BANNER -->
    <div class="approval-banner">
        <div class="banner-text">
            <div class="banner-title">Approval Final <span style="opacity:.75">Manager</span></div>
            <div class="banner-sub">Data yang telah disetujui Asisten Manager dan menunggu keputusan akhir Anda</div>
        </div>
        <div class="banner-icon"><i class="fas fa-clipboard-check"></i></div>
    </div>

    <!-- FLASH -->
    @if(session('success'))
    <div class="flash-ok"><i class="fas fa-check-circle"></i>{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="flash-err"><i class="fas fa-exclamation-circle"></i>{{ session('error') }}</div>
    @endif
    @if($errors->any())
    <div class="flash-err">
        <i class="fas fa-exclamation-triangle"></i>
        <div><div style="margin-bottom:4px">Data belum dapat diproses.</div><ul style="margin-left:16px">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    </div>
    @endif

    <!-- STAT CARDS -->
    @php
    $catStyleMap = [
        'Riset'      => ['grad'=>'linear-gradient(135deg,#378ADD,#1e4875)', 'glow'=>'rgba(55,138,221,.3)', 'icon'=>'fas fa-flask'],
        'Bisnis'     => ['grad'=>'linear-gradient(135deg,#8b5cf6,#581c87)', 'glow'=>'rgba(139,92,246,.3)', 'icon'=>'fas fa-briefcase'],
        'Pengabdian' => ['grad'=>'linear-gradient(135deg,#10b981,#064e3b)', 'glow'=>'rgba(16,185,129,.3)', 'icon'=>'fas fa-hands-helping'],
        'Akademik'   => ['grad'=>'linear-gradient(135deg,#f59e0b,#78350f)', 'glow'=>'rgba(245,158,11,.3)', 'icon'=>'fas fa-graduation-cap'],
        'Inovasi'    => ['grad'=>'linear-gradient(135deg,#f97316,#7c2d12)', 'glow'=>'rgba(249,115,22,.3)', 'icon'=>'fas fa-lightbulb'],
    ];
    @endphp
    <div class="stat-grid">
        @foreach($categories as $cat)
        @php $s = $catStyleMap[$cat['label']] ?? ['grad'=>'linear-gradient(135deg,#64748b,#334155)','glow'=>'rgba(0,0,0,.1)','icon'=>'fas fa-file']; @endphp
        <div class="stat-card" style="background:{{ $s['grad'] }};box-shadow:0 8px 24px -8px {{ $s['glow'] }};">
            <div class="stat-icon"><i class="{{ $s['icon'] }}"></i></div>
            <div class="stat-body">
                <div class="stat-label">{{ $cat['label'] }}</div>
                <div class="stat-count">{{ $cat['count'] }}</div>
                <div class="stat-sub">Menunggu approval</div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- ALERT -->
    @if($jumlahMenunggu > 0)
    <div class="alert-banner" style="background:#faf5ff;border:1px solid #ddd6fe;color:#6d28d9;">
        <i class="fas fa-hourglass-half" style="color:#8b5cf6;"></i>
        <span>Ada <strong>{{ $jumlahMenunggu }} data</strong> telah disetujui Asisten Manager dan menunggu keputusan akhir Anda.</span>
    </div>
    @else
    <div class="alert-banner" style="background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d;">
        <i class="fas fa-check-circle" style="color:#22c55e;"></i>
        <span>Semua data sudah mendapat keputusan akhir. Tidak ada yang perlu di-approve.</span>
    </div>
    @endif

    <!-- TABLE -->
    <div class="table-card">
        <div class="table-header">
            <div>
                <div class="table-title">Data Siap Untuk Approval Final</div>
                <div class="table-sub">Status: sudah direview Asisten Manager &amp; menunggu keputusan Manager</div>
            </div>
            @if($recentReviewed->count() > 0)
            <span class="count-pill">{{ $recentReviewed->count() }} Data</span>
            @endif
        </div>
        <div style="overflow-x:auto;">
            <table class="tbl">
                <thead>
                    <tr>
                        <th style="width:50px;">No.</th>
                        <th>Jenis Data</th>
                        <th>Judul / Nama Data</th>
                        <th>Penginput</th>
                        <th>Tanggal Input</th>
                        <th>Status</th>
                        <th style="text-align:center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentReviewed as $index => $row)
                    @php
                    $tipeColors = ['Riset'=>'#ede9fe;color:#5b21b6','Bisnis'=>'#ede9fe;color:#5b21b6','Pengabdian'=>'#ede9fe;color:#5b21b6','Akademik'=>'#ede9fe;color:#5b21b6','Inovasi'=>'#ede9fe;color:#5b21b6'];
                    $tipeMap = [
                        'riset'      => ['bg'=>'#ede9fe','color'=>'#5b21b6','icon'=>'fa-flask'],
                        'bisnis'     => ['bg'=>'#dbeafe','color'=>'#1e40af','icon'=>'fa-briefcase'],
                        'pengabdian' => ['bg'=>'#d1fae5','color'=>'#065f46','icon'=>'fa-hands-helping'],
                        'akademik'   => ['bg'=>'#fef3c7','color'=>'#92400e','icon'=>'fa-graduation-cap'],
                        'inovasi'    => ['bg'=>'#ffedd5','color'=>'#9a3412','icon'=>'fa-lightbulb'],
                    ];
                    $tKey = strtolower($row->kategori ?? '');
                    $tc = $tipeMap[$tKey] ?? ['bg'=>'#f1f5f9','color'=>'#475569','icon'=>'fa-file'];
                    @endphp
                    <tr>
                        <td class="row-num">{{ $index + 1 }}</td>
                        <td>
                            <span class="tipe-badge" style="background:{{ $tc['bg'] }};color:{{ $tc['color'] }};">
                                <i class="fas {{ $tc['icon'] }}" style="font-size:10px;"></i>
                                {{ ucfirst($row->kategori ?? '-') }}
                            </span>
                        </td>
                        <td class="judul-cell">
                            <div class="judul-main" title="{{ $row->judul }}">{{ $row->judul ?? '-' }}</div>
                        </td>
                        <td>
                            <div class="penginput-cell">
                                <span class="penginput-avatar">{{ strtoupper(substr($row->penginput?->name ?? '?', 0, 1)) }}</span>
                                <span class="penginput-name">{{ $row->penginput?->name ?? '-' }}</span>
                            </div>
                        </td>
                        <td class="date-cell">
                            {{ $row->created_at ? \Carbon\Carbon::parse($row->created_at)->format('d M Y') : '-' }}
                        </td>
                        <td>
                            <span class="status-badge" style="background:#ede9fe;color:#5b21b6;">
                                <i class="fas fa-hourglass-half" style="font-size:10px;"></i>
                                Menunggu Approval
                            </span>
                        </td>
                        <td style="text-align:center;">
                            <a href="{{ route('manager.approve.detail', ['tipe' => $row->kategori, 'id' => $row->id]) }}"
                               class="btn-detail">
                                <i class="fas fa-eye" style="font-size:11px;"></i> Detail
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <i class="fas fa-check-double" style="color:#86efac;"></i>
                                <div class="empty-title">Semua data sudah diproses</div>
                                <div class="empty-sub">Tidak ada data yang perlu di-approve saat ini.</div>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- MODAL REJECT -->
<div id="rejectModal" class="hidden fixed inset-0 z-[100] modal-backdrop-custom items-center justify-center px-4">
    <div class="modal-box">
        <div class="modal-header">
            <div class="modal-icon"><i class="fas fa-times"></i></div>
            <div>
                <div class="modal-title">Tolak Data</div>
                <div class="modal-subtitle">Data akan dikembalikan kepada Staff untuk diperbaiki.</div>
            </div>
        </div>
        <form id="rejectForm" method="POST">
            @csrf
            <div class="modal-body">
                <label class="form-label">Data yang ditolak</label>
                <div id="rejectJudul" class="form-info">-</div>
                <label for="catatan_reject" class="form-label">Alasan Penolakan <span style="color:#ef4444">*</span></label>
                <textarea id="catatan_reject" name="catatan_reject" rows="4" maxlength="1000" required
                    placeholder="Jelaskan bagian data yang harus diperbaiki oleh Staff..."
                    class="form-textarea"></textarea>
                <p class="form-hint">Alasan ini akan ditampilkan kepada Staff.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeRejectModal()">Batal</button>
                <button type="submit" class="btn-reject-confirm">
                    <i class="fas fa-times-circle"></i> Ya, Tolak Data
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openRejectModal(button) {
    document.getElementById('rejectForm').action       = button.dataset.rejectUrl;
    document.getElementById('rejectJudul').textContent = button.dataset.judul || '-';
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
document.getElementById('rejectModal').addEventListener('click', e => { if (e.target === document.getElementById('rejectModal')) closeRejectModal(); });
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeRejectModal(); });
</script>
@endpush