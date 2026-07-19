<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>@yield('title', 'RI CCSL')</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/logo_prscope.png') }}" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        :root {
            --navy-900: #1b2436;
            --navy-800: #232f47;
            --navy-700: #2c3b59;
            --navy-600: #3d5177;
            --navy-500: #4a5f88;
            --navy-100: #eef1f8;
            --ink: #1f2733;
            --muted: #7a8496;
            --line: #e6e9f0;
            --bg-page: #f4f6fa;
            --white: #ffffff;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', 'Inter', ui-sans-serif, system-ui, sans-serif;
            background: var(--bg-page);
            color: var(--ink);
            -webkit-font-smoothing: antialiased;
            min-height: 100vh;
        }

        /* ===== TOP NAVBAR ===== */
        .navbar {
            background: var(--white);
            border-bottom: 1px solid var(--line);
            padding: 12px 28px;
            display: flex;
            align-items: center;
            gap: 20px;
            box-shadow: 0 2px 10px rgba(20,30,50,0.04);
            flex-wrap: wrap;
            position: relative;
            z-index: 50;
        }

        .brand {
            display: flex;
            align-items: baseline;
            gap: 6px;
            flex-shrink: 0;
        }

        .brand .logo-badge {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: var(--navy-800);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: 8px;
            flex-shrink: 0;
        }

        .brand .logo-badge svg {
            width: 18px;
            height: 18px;
            stroke: #fff;
            fill: none;
            stroke-width: 1.8;
        }

        .brand-name {
            font-size: 19px;
            font-weight: 800;
            color: var(--navy-800);
            letter-spacing: 0.3px;
        }

        .brand-sub {
            font-size: 13px;
            color: var(--muted);
        }

        .role-badge {
            background: var(--navy-100);
            color: var(--navy-700);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.6px;
            padding: 6px 14px;
            border-radius: 999px;
            flex-shrink: 0;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 6px;
            flex: 1;
        }

        .nav-links a {
            text-decoration: none;
            color: var(--muted);
            font-size: 14.5px;
            font-weight: 600;
            padding: 9px 16px;
            border-radius: 8px;
            transition: background .15s, color .15s;
        }

        .nav-links a:hover {
            color: var(--navy-800);
            background: var(--navy-100);
        }

        .nav-links a.active {
            background: linear-gradient(135deg, var(--navy-700), var(--navy-900));
            color: #fff;
        }

        .navbar-right {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-shrink: 0;
            margin-left: auto;
        }

        .user-chip {
            display: flex;
            align-items: center;
            gap: 10px;
            background: var(--navy-100);
            padding: 6px 16px 6px 6px;
            border-radius: 999px;
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: var(--navy-800);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 700;
        }

        .user-meta .u-name {
            font-size: 13.5px;
            font-weight: 700;
            color: var(--ink);
            line-height: 1.2;
        }

        .user-meta .u-role {
            font-size: 10.5px;
            color: var(--muted);
            letter-spacing: 0.5px;
            font-weight: 700;
        }

        .btn-logout {
            display: flex;
            align-items: center;
            gap: 6px;
            border: 1px solid var(--line);
            background: #fff;
            color: var(--navy-800);
            font-size: 13.5px;
            font-weight: 700;
            padding: 9px 16px;
            border-radius: 999px;
            cursor: pointer;
            transition: background .15s;
        }

        .btn-logout:hover {
            background: var(--navy-100);
        }

        .btn-logout svg {
            width: 15px;
            height: 15px;
            stroke: var(--navy-800);
            fill: none;
            stroke-width: 2;
        }

        @media (max-width: 1100px) {
            .nav-links {
                order: 3;
                width: 100%;
                justify-content: flex-start;
                overflow-x: auto;
                padding-top: 6px;
            }
        }

        /* ===== CONTENT AREA ===== */
        .content {
            padding: 32px 36px;
            max-width: 1400px;
            margin: 0 auto;
        }

        /* ===== CONTENT AREA CARD OVERRIDES ===== */
        .content div[class*="bg-white"][class*="rounded-"],
        .content form[class*="bg-white"][class*="rounded-"],
        .content .section-wrap,
        .content .tw-card,
        .content .chart-card {
            box-shadow: 0 4px 20px rgba(27, 36, 54, 0.08), 0 2px 4px rgba(27, 36, 54, 0.03) !important;
            border: 1.5px solid #cbd5e1 !important; /* solid slate-300 stroke border */
        }

        .content .tw-card:hover,
        .content .chart-card:hover {
            box-shadow: 0 8px 30px rgba(27, 36, 54, 0.12) !important;
            border-color: #94a3b8 !important; /* slate-400 on hover */
        }

        @media (max-width: 900px) {
            .content {
                padding: 20px;
            }
        }

        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 999px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* ===== CUSTOM LOGOUT MODAL ===== */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(15, 23, 42, 0.6); /* backdrop slate overlay */
            backdrop-filter: blur(4px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            animation: fadeIn 0.2s ease-out;
        }

        .modal-box {
            background: #ffffff;
            width: 90%;
            max-width: 440px;
            border-radius: 28px;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            transform: scale(0.95);
            animation: scaleUp 0.2s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
        }

        .modal-header {
            background: linear-gradient(135deg, var(--navy-800), var(--navy-900));
            padding: 32px 24px 24px;
            text-align: center;
            color: #ffffff;
        }

        .modal-title {
            font-size: 26px;
            font-weight: 800;
            letter-spacing: -0.5px;
            margin-bottom: 8px;
        }

        .modal-subtitle {
            font-size: 14.5px;
            font-weight: 500;
            opacity: 0.9;
        }

        .modal-body {
            padding: 32px 32px 28px;
            text-align: center;
        }

        .modal-body p {
            font-size: 14.5px;
            line-height: 1.6;
            color: #4a5568;
            font-weight: 500;
            margin-bottom: 28px;
        }

        .modal-actions {
            display: flex;
            gap: 16px;
            justify-content: center;
        }

        .btn-cancel {
            flex: 1;
            padding: 14px 24px;
            border: 1px solid #e2e8f0;
            background: #f1f5f9;
            color: #334155;
            font-size: 15px;
            font-weight: 700;
            border-radius: 9999px;
            cursor: pointer;
            transition: background 0.15s, transform 0.1s;
            outline: none;
        }

        .btn-cancel:hover {
            background: #e2e8f0;
        }

        .btn-cancel:active {
            transform: scale(0.98);
        }

        .btn-confirm {
            flex: 1;
            padding: 14px 24px;
            border: none;
            background: linear-gradient(135deg, var(--navy-700), var(--navy-900));
            color: #ffffff;
            font-size: 15px;
            font-weight: 700;
            border-radius: 9999px;
            cursor: pointer;
            transition: background 0.15s, transform 0.1s, box-shadow 0.15s;
            outline: none;
            box-shadow: 0 4px 12px rgba(27, 36, 54, 0.2);
        }

        .btn-confirm:hover {
            background: var(--navy-900);
            box-shadow: 0 6px 16px rgba(27, 36, 54, 0.3);
        }

        .btn-confirm:active {
            transform: scale(0.98);
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes scaleUp {
            from { transform: scale(0.95); }
            to { transform: scale(1); }
        }
    </style>
    @stack('styles')
</head>
<body>
    @php
        $layoutUser = auth()->user();
        $layoutRoleName = optional($layoutUser->role)->name ?? 'unknown';
        
        $logoutMessage = 'Anda akan mengakhiri sesi masuk ini. Silakan login kembali untuk melanjutkan aktivitas Anda.';
        if (str_contains($layoutRoleName, 'admin')) {
            $logoutMessage = 'Anda akan mengakhiri sesi masuk sebagai Administrator. Silakan login kembali untuk mengelola sistem dan akun pengguna.';
        } elseif (str_contains($layoutRoleName, 'asisten_manager')) {
            $logoutMessage = 'Anda akan mengakhiri sesi masuk sebagai Asisten Manager. Silakan login kembali untuk melanjutkan pemeriksaan dan verifikasi dokumen.';
        } elseif (str_contains($layoutRoleName, 'manager')) {
            $logoutMessage = 'Anda akan mengakhiri sesi masuk sebagai Manager. Silakan login kembali untuk memverifikasi atau memberikan persetujuan dokumen kinerja.';
        } elseif (str_contains($layoutRoleName, 'dekan')) {
            $logoutMessage = 'Anda akan mengakhiri sesi masuk sebagai Dekan. Silakan login kembali untuk memantau rekapitulasi laporan capaian kinerja.';
        } elseif (str_contains($layoutRoleName, 'staff')) {
            $logoutMessage = 'Anda akan mengakhiri sesi masuk sebagai Staff. Silakan login kembali untuk menginput atau memperbarui data kinerja Anda.';
        }
    @endphp

    <div class="navbar">
        <div class="brand">
            <span class="brand-name">RI-CCSL</span>
        </div>

        <span class="role-badge">
            {{ strtoupper(str_replace('_', ' ', $layoutRoleName)) }}
        </span>

        <nav class="nav-links">
            @if(str_contains($layoutRoleName, 'staff'))
                <a href="{{ route('staff.dashboard') }}" class="{{ request()->routeIs('staff.dashboard') ? 'active' : '' }}">Dashboard</a>
                <a href="{{ route('riset.index') }}" class="{{ request()->routeIs('riset.*') ? 'active' : '' }}">Riset</a>
                <a href="{{ route('bisnis.index') }}" class="{{ request()->routeIs('bisnis.*') ? 'active' : '' }}">Bisnis</a>
                <a href="{{ route('pengabdian.index') }}" class="{{ request()->routeIs('pengabdian.*') ? 'active' : '' }}">Pengabdian</a>
                <a href="{{ route('akademik.index') }}" class="{{ request()->routeIs('akademik.*') ? 'active' : '' }}">Akademik</a>
                <a href="{{ route('inovasi.index') }}" class="{{ request()->routeIs('inovasi.*') ? 'active' : '' }}">Inovasi</a>
                <a href="{{ route('master-target.index') }}" class="{{ request()->routeIs('master-target.*') ? 'active' : '' }}">Target</a>
            @elseif(str_contains($layoutRoleName, 'asisten_manager'))
                <a href="{{ route('asisten_manager.dashboard') }}" class="{{ request()->routeIs('asisten_manager.dashboard') ? 'active' : '' }}">Dashboard</a>
                <a href="{{ route('asisten_manager.approve') }}" class="{{ request()->routeIs('asisten_manager.approve') || request()->routeIs('asisten_manager.item.*') ? 'active' : '' }}">Approval</a>
            @elseif(str_contains($layoutRoleName, 'manager'))
                <a href="{{ route('manager.dashboard') }}" class="{{ request()->routeIs('manager.dashboard') ? 'active' : '' }}">Dashboard</a>
                <a href="{{ route('manager.approve') }}" class="{{ request()->routeIs('manager.approve') || request()->routeIs('manager.approve.detail') ? 'active' : '' }}">Approval</a>
            @elseif(str_contains($layoutRoleName, 'dekan'))
                <a href="{{ route('dekan.dashboard') }}" class="{{ request()->routeIs('dekan.dashboard') ? 'active' : '' }}">Dashboard</a>
                <a href="{{ route('dekan.laporan') }}" class="{{ request()->routeIs('dekan.laporan') ? 'active' : '' }}">Laporan</a>
            @elseif(str_contains($layoutRoleName, 'admin'))
                <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">Dashboard</a>
                <a href="{{ route('admin.user') }}" class="{{ request()->routeIs('admin.user') || request()->routeIs('admin.users.*') ? 'active' : '' }}">Kelola User</a>
                <a href="{{ route('admin.laporan') }}" class="{{ request()->routeIs('admin.laporan') ? 'active' : '' }}">Laporan Sistem</a>
                
            @endif
        </nav>

        <div class="navbar-right">
            <div class="user-chip">
                <span class="user-avatar">{{ strtoupper(substr($layoutUser->name ?? 'U', 0, 1)) }}</span>
                <div class="user-meta">
                    <div class="u-name">{{ $layoutUser->name ?? 'User' }}</div>
                    <div class="u-role">{{ strtoupper(str_replace('_', ' ', $layoutRoleName)) }}</div>
                </div>
            </div>
            
            <form action="{{ route('logout') }}" method="POST" id="logout-form" style="display: none;">
                @csrf
            </form>
            <button class="btn-logout" onclick="event.preventDefault(); showLogoutModal();">
                <svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg>
                Logout
            </button>
        </div>
    </div>

    @yield('content')

    @stack('scripts')

    <!-- Custom Logout Confirmation Modal -->
    <div id="logout-modal" style="display: none;" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-header">
                <div class="modal-title">Konfirmasi</div>
                <div class="modal-subtitle">Apakah Anda yakin ingin keluar?</div>
            </div>
            <div class="modal-body">
                <p>{{ $logoutMessage }}</p>
                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="closeLogoutModal()">Batal</button>
                    <button type="button" class="btn-confirm" onclick="document.getElementById('logout-form').submit();">Ya, Keluar</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showLogoutModal() {
            document.getElementById('logout-modal').style.display = 'flex';
        }

        function closeLogoutModal() {
            document.getElementById('logout-modal').style.display = 'none';
        }

        // Close modal if user clicks outside of the modal box
        window.addEventListener('click', function(event) {
            var modal = document.getElementById('logout-modal');
            if (event.target == modal) {
                closeLogoutModal();
            }
        });
    </script>
</body>
</html>
