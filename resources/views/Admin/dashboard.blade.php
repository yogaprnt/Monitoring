@extends('layouts.app')

@section('title', 'RI-CCSL - Dashboard Admin')

@push('styles')
<style>
  .stat-grid{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:20px;
    margin-bottom:28px;
  }
  .stat-card{
    border-radius:14px;
    padding:22px 24px;
    color:#fff;
    position:relative;
    overflow:hidden;
    min-height:120px;
    transition: transform .2s ease, box-shadow .2s ease;
  }
  .stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 30px rgba(0,0,0,0.1);
  }
  .stat-card .label{font-size:12px;font-weight:700;letter-spacing:1px;opacity:0.85;}
  .stat-card .value{font-size:34px;font-weight:800;margin:4px 0 2px;}
  .stat-card .desc{font-size:12.5px;opacity:0.85;}
  .stat-card .icon{
    position:absolute;top:18px;right:20px;
    width:38px;height:38px;border-radius:10px;
    background:rgba(255,255,255,0.18);
    display:flex;align-items:center;justify-content:center;
  }
  .stat-card .icon svg{width:19px;height:19px;stroke:#fff;fill:none;stroke-width:1.8;}

  .c-total{background:linear-gradient(135deg,#1b2436,#2c3b59);}
  .c-admin{background:linear-gradient(135deg,#6366f1,#3730a3);}
  .c-manager{background:linear-gradient(135deg,#22c55e,#047857);}
  .c-staff{background:linear-gradient(135deg,#facc15,#b45309);}
  .c-dekan{background:linear-gradient(135deg,#ef4444,#b91c1c);}
  .c-asisten{background:linear-gradient(135deg,#a855f7,#6f2fbf);}

  .panel-grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:20px;
  }
  .panel{
    background:#fff;
    border:1px solid var(--line);
    border-radius:14px;
    padding:22px 24px;
  }
  .panel h3{font-size:16px;font-weight:700;margin-bottom:18px;}

  .pie-wrap{display:flex;flex-direction:column;align-items:center;gap:18px;}
  .legend{display:flex;flex-wrap:wrap;gap:14px;justify-content:center;font-size:13px;color:var(--ink);}
  .legend span{display:inline-flex;align-items:center;gap:6px;}
  .dot{width:10px;height:10px;border-radius:50%;display:inline-block;}

  .user-list{display:flex;flex-direction:column;gap:16px;}
  .user-row{display:flex;align-items:center;gap:12px;}
  .user-row .av{
    width:38px;height:38px;border-radius:50%;
    background:var(--navy-800);color:#fff;
    display:flex;align-items:center;justify-content:center;
    font-size:13px;font-weight:700;flex-shrink:0;
  }
  .user-row .nm{font-size:14px;font-weight:700;}
  .user-row .rl{font-size:12.5px;color:var(--muted);}

  @media (max-width:900px){
    .stat-grid{grid-template-columns:1fr 1fr;}
    .panel-grid{grid-template-columns:1fr;}
  }
</style>
@endpush

@section('content')
@php($authUser = auth()->user())

<div class="content">
    <div class="page-title text-2xl font-bold text-gray-800 mb-1">Dashboard</div>
    <div class="page-sub text-gray-400 text-sm mb-6">Selamat datang, {{ $authUser->name ?? 'Admin' }}!</div>

    <div class="stat-grid">
      
      {{-- Total User --}}
      <div class="stat-card c-total shadow-sm">
        <div class="icon"><svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="3"/><circle cx="17" cy="9" r="2.4"/><path d="M3 20c0-3.3 2.7-6 6-6s6 2.7 6 6M14.5 14.3c2.6.3 4.5 2.4 4.5 5.1"/></svg></div>
        <div class="label">TOTAL USER</div>
        <div class="value">{{ $totalUsers }}</div>
        <div class="desc">Pengguna terdaftar</div>
      </div>
      
      {{-- Admin --}}
      <div class="stat-card c-admin shadow-sm">
        <div class="icon"><svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4.4 3.6-8 8-8s8 3.6 8 8"/></svg></div>
        <div class="label">ADMIN</div>
        <div class="value">{{ $countAdmin }}</div>
        <div class="desc">Pengelola & monitoring user</div>
      </div>
      
      {{-- Manager --}}
      <div class="stat-card c-manager shadow-sm">
        <div class="icon"><svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4.4 3.6-8 8-8s8 3.6 8 8"/></svg></div>
        <div class="label">MANAGER</div>
        <div class="value">{{ $countManager }}</div>
        <div class="desc">Persetujuan & Review Final</div>
      </div>
      
      {{-- Staff --}}
      <div class="stat-card c-staff shadow-sm">
        <div class="icon"><svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4.4 3.6-8 8-8s8 3.6 8 8"/></svg></div>
        <div class="label">STAFF</div>
        <div class="value">{{ $countStaff }}</div>
        <div class="desc">Penginput data</div>
      </div>
      
      {{-- Dekan --}}
      <div class="stat-card c-dekan shadow-sm">
        <div class="icon"><svg viewBox="0 0 24 24"><path d="M12 3 2 8l10 5 10-5-10-5Z"/><path d="M6 10.5V16c0 1.5 2.7 3 6 3s6-1.5 6-3v-5.5"/></svg></div>
        <div class="label">DEKAN</div>
        <div class="value">{{ $countDekan }}</div>
        <div class="desc">Pemantau & laporan</div>
      </div>
      
      {{-- Asisten Manager --}}
      <div class="stat-card c-asisten shadow-sm">
        <div class="icon"><svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4.4 3.6-8 8-8s8 3.6 8 8"/><path d="m9.5 9.5 1.5 1.5 3-3"/></svg></div>
        <div class="label">ASISTEN MANAGER</div>
        <div class="value">{{ $countAsistenManager }}</div>
        <div class="desc">Persetujuan & Review Pertama</div>
      </div>
      
    </div>

    <div class="panel-grid">
      
      {{-- Statistik Role (Pie Chart) --}}
      <div class="panel shadow-sm">
        <h3 class="text-gray-700">Statistik Role User</h3>
        <div class="pie-wrap">
          <div style="width: 230px; height: 230px; position: relative;">
            <canvas id="roleChart"></canvas>
          </div>
          <div class="legend">
            <span><i class="dot" style="background:#6366f1"></i>Admin</span>
            <span><i class="dot" style="background:#22c55e"></i>Manager</span>
            <span><i class="dot" style="background:#facc15"></i>Staff</span>
            <span><i class="dot" style="background:#ef4444"></i>Dekan</span>
            <span><i class="dot" style="background:#a855f7"></i>Asisten Manager</span>
          </div>
        </div>
      </div>

      {{-- User Terbaru --}}
      <div class="panel shadow-sm">
        <h3 class="text-gray-700">User Terbaru</h3>
        <div class="user-list">
          @forelse($recentUsers as $u)
          <div class="user-row flex items-center gap-3">
            <span class="av flex items-center justify-center font-bold text-white bg-slate-700 rounded-full w-9 h-9">{{ strtoupper(substr($u->name, 0, 2)) }}</span>
            <div>
              <div class="nm font-semibold text-gray-800">{{ $u->name }}</div>
              <div class="rl text-xs text-gray-400 mt-0.5">{{ optional($u->role)->name ?? 'User' }}</div>
            </div>
          </div>
          @empty
          <p class="text-sm text-gray-400 py-4">Belum ada pengguna.</p>
          @endforelse
        </div>
      </div>
      
    </div>
</div>
@endsection

@push('scripts')
<script>
    const ctx = document.getElementById('roleChart').getContext('2d');
    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: ['Admin', 'Manager', 'Staff', 'Dekan', 'Asisten Manager'],
            datasets: [{
                data: [
                    {{ $countAdmin }},
                    {{ $countManager }},
                    {{ $countStaff }},
                    {{ $countDekan }},
                    {{ $countAsistenManager }}
                ],
                backgroundColor: ['#6366f1', '#22c55e', '#facc15', '#ef4444', '#a855f7'],
                borderWidth: 2,
                borderColor: '#fff',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ` ${ctx.label}: ${ctx.parsed} user`
                    }
                }
            }
        }
    });
</script>
@endpush