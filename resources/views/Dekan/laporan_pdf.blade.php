<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Kinerja - RI CCSL</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #334155;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #1a2e4a;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .logo {
            width: 50px;
            height: 50px;
        }
        .title {
            font-size: 18px;
            font-weight: bold;
            color: #1a2e4a;
            margin: 0;
        }
        .subtitle {
            font-size: 11px;
            color: #64748b;
            margin: 4px 0 0 0;
        }
        .meta-info {
            text-align: right;
            font-size: 10px;
            color: #64748b;
        }
        .summary-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 20px;
        }
        .summary-title {
            font-size: 12px;
            font-weight: bold;
            color: #1a2e4a;
            margin-bottom: 8px;
        }
        .summary-grid {
            width: 100%;
        }
        .summary-grid td {
            width: 25%;
            padding: 4px 0;
        }
        .summary-label {
            font-size: 9px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: bold;
        }
        .summary-value {
            font-size: 14px;
            font-weight: bold;
            color: #1e293b;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .data-table th {
            background-color: #1a2e4a;
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9px;
            padding: 8px 10px;
            border: 1px solid #1a2e4a;
            text-align: left;
        }
        .data-table td {
            padding: 8px 10px;
            border: 1px solid #e2e8f0;
            font-size: 10px;
        }
        .data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .text-right {
            text-align: right;
        }
        .badge {
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 8px;
        }
        .badge-green {
            background-color: #dcfce7;
            color: #15803d;
        }
        .badge-red {
            background-color: #fee2e2;
            color: #b91c1c;
        }
    </style>
</head>
<body>

    <table class="header-table" cellpadding="0" cellspacing="0">
        <tr>
            <td style="vertical-align: top;">
                <div style="font-size: 20px; font-weight: 800; color: #1a2e4a; letter-spacing: 0.5px;">RI-CCSL</div>
                <div style="font-size: 9px; color: #64748b; font-weight: bold; text-transform: uppercase; margin-top: 2px;">Research Center for Climate Change & Smart Land</div>
            </td>
            <td style="vertical-align: top; text-align: center; padding-top: 5px;">
                <h1 class="title" style="font-size: 16px; letter-spacing: 1px;">LAPORAN KINERJA</h1>
            </td>
            <td class="meta-info" style="vertical-align: top; padding-top: 5px;">
                Tanggal Cetak: {{ now()->setTimezone('Asia/Jakarta')->translatedFormat('d F Y H:i') }} WIB<br>
                Oleh: {{ auth()->user()->name ?? 'Dekan' }}
            </td>
        </tr>
    </table>

    <div class="summary-box">
        <div class="summary-title">Ringkasan Laporan</div>
        <table class="summary-grid" cellpadding="0" cellspacing="0">
            <tr>
                <td>
                    <div class="summary-label">Total Realisasi</div>
                    <div class="summary-value">{{ number_format($summary['totalRealisasi']) }}</div>
                </td>
                <td>
                    <div class="summary-label">Total Target</div>
                    <div class="summary-value">{{ number_format($summary['totalTarget']) }}</div>
                </td>
                <td>
                    <div class="summary-label">Total Deviasi</div>
                    <div class="summary-value" style="color: {{ $summary['totalDeviasi'] >= 0 ? '#15803d' : '#b91c1c' }}">
                        {{ $summary['totalDeviasi'] >= 0 ? '+' : '' }}{{ number_format($summary['totalDeviasi']) }}
                    </div>
                </td>
                <td>
                    <div class="summary-label">Persentase Capaian</div>
                    <div class="summary-value" style="color: {{ $summary['totalDeviasi'] >= 0 ? '#15803d' : '#b91c1c' }}">
                        {{ $summary['pct'] }}%
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <h3 style="color: #1a2e4a; margin-top: 25px; margin-bottom: 10px; font-size: 12px; border-left: 3px solid #1a2e4a; padding-left: 8px;">Daftar Realisasi Laporan</h3>
    <table class="data-table" cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">No</th>
                <th style="width: 15%;">Kategori</th>
                <th style="width: 25%;">CoE</th>
                <th style="width: 35%;">Judul Kinerja</th>
                <th style="width: 10%; text-align: center;">Periode</th>
                <th style="width: 10%; text-align: right;">Realisasi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($allData as $i => $row)
                <tr>
                    <td style="text-align: center;">{{ $i + 1 }}</td>
                    <td>{{ $row->kategori }}</td>
                    <td>{{ $row->coe ?: '-' }}</td>
                    <td>{{ $row->judul }}</td>
                    <td style="text-align: center;">{{ $row->periode }}</td>
                    <td style="text-align: right; font-weight: bold; color: #1a2e4a;">{{ number_format($row->realisasi) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: #64748b; padding: 20px;">Belum ada data realisasi yang disetujui.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>
