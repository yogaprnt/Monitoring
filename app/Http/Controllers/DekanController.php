<?php

namespace App\Http\Controllers;

use App\Models\Riset;
use App\Models\Bisnis;
use App\Models\Pengabdian;
use App\Models\Akademik;
use App\Models\Inovasi;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DekanController extends Controller
{
    /**
     * Daftar model yang digunakan di seluruh controller.
     * Key = label kategori (untuk tampilan), Value = class model.
     */
    private array $models = [
        'Akademik'   => Akademik::class,
        'Bisnis'     => Bisnis::class,
        'Inovasi'    => Inovasi::class,
        'Pengabdian' => Pengabdian::class,
        'Riset'      => Riset::class,
    ];

    /**
     * Pemetaan indikator KPI berdasarkan kata kunci di kolom `judul`.
     * Sama persis dengan AsistenManagerController agar konsisten lintas role.
     */
    private array $indikatorMap = [
        'intl_selain_q12' => [
            'label'    => 'Jurnal Intl. Selain Q1/Q2',
            'keywords' => ['bereputasi selain q1'],
        ],
        'intl_q12' => [
            'label'    => 'Jurnal Intl. Setara Q1/Q2',
            'keywords' => ['bereputasi setara q1'],
        ],
        'pub_nasional' => [
            'label'    => 'Pub. Nasional S1–S4',
            'keywords' => ['jurnal nasional s1 s.d'],
        ],
        'hki' => [
            'label'    => 'HKI',
            'keywords' => ['hki'],
        ],
        'unit_bisnis' => [
            'label'    => 'Unit Bisnis / LSP',
            'keywords' => ['unit bisnis (lsp'],
        ],
    ];

    // ─────────────────────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Cocokkan kolom `judul` terhadap $indikatorMap untuk menentukan indikator KPI.
     * Mengembalikan null kalau tidak ada kata kunci yang cocok.
     */
    private function resolveIndikator(?string $judul): ?string
    {
        if (!$judul) {
            return null;
        }

        $judulLower = strtolower($judul);

        foreach ($this->indikatorMap as $key => $config) {
            foreach ($config['keywords'] as $keyword) {
                if (str_contains($judulLower, $keyword)) {
                    return $key;
                }
            }
        }

        return null;
    }

    /**
     * Ambil semua data berstatus 'approved' dari kelima model,
     * dengan opsional filter periode dan kategori.
     */
    private function collectAllApproved(
        ?string $filterTw,
        ?string $filterTahun,
        ?string $filterKategori
    ): Collection {
        $merged = collect();

        foreach ($this->models as $kategori => $modelClass) {
            if ($filterKategori && strtolower($filterKategori) !== strtolower($kategori)) {
                continue;
            }

            $query = $modelClass::query()
                ->where('status', 'approved')
                ->select(['id', 'coe', 'judul', 'periode', 'target', 'realisasi', 'status', 'input_by', 'created_at']);

            if ($filterTw) {
                $query->where('periode', 'like', "%{$filterTw}%");
            }

            if ($filterTahun) {
                $query->where('periode', 'like', "%{$filterTahun}%");
            }

            $rows = $query->get()->map(function ($row) use ($kategori) {
                $row->kategori  = $kategori;
                $row->deviasi   = $row->realisasi - $row->target;
                $row->indikator = $this->resolveIndikator($row->judul);

                preg_match('/(TW\d)/i', (string) $row->periode, $twMatch);
                preg_match('/(\d{4})/',  (string) $row->periode, $tahunMatch);
                $row->tw    = $twMatch[1]    ?? '-';
                $row->tahun = $tahunMatch[1] ?? '-';

                return $row;
            });

            $merged = $merged->merge($rows);
        }

        return $merged->values();
    }

    /**
     * Hitung total target, realisasi, deviasi, dan persentase capaian.
     */
    private function buildSummary(Collection $data, ?string $tw = null, ?string $tahun = null, ?string $kategori = null): array
    {
        $totalRealisasi = (int) $data->sum('realisasi');

        $qTarget = \App\Models\MasterTarget::query();
        if ($tw) {
            $qTarget->where('periode', 'like', "%{$tw}%");
        }
        if ($tahun) {
            $qTarget->where('periode', 'like', "%{$tahun}%");
        }
        if ($kategori) {
            $qTarget->where('kategori', ucfirst($kategori));
        }
        $totalTarget = (int) $qTarget->sum('target');

        $totalDeviasi   = $totalRealisasi - $totalTarget;
        $pct            = $totalTarget > 0
            ? round(($totalRealisasi / $totalTarget) * 100, 1)
            : 0;

        return compact('totalTarget', 'totalRealisasi', 'totalDeviasi', 'pct');
    }

    /**
     * Hitung realisasi/target/deviasi/capaian untuk 5 indikator KPI utama
     * (Pub Nasional, HKI, Unit Bisnis, Intl Selain Q1/2, Intl Q1/2),
     * berdasarkan pencocokan kata kunci di kolom judul (lihat $indikatorMap).
     */
    private function buildMetrics(Collection $data, ?string $tahun = null): array
    {
        $hitung = function (string $key) use ($data, $tahun): array {
            $rows      = $data->where('indikator', $key);
            $realisasi = (int) $rows->sum('realisasi');
            
            // Ambil target overall untuk tahun berjalan dari master_targets
            $query = \App\Models\MasterTarget::query();
            if ($tahun) {
                $query->where('periode', 'like', "%{$tahun}%");
            }
            $targets = $query->get()->filter(function ($mt) use ($key) {
                return $this->resolveIndikator($mt->judul) === $key;
            });
            $target = (int) $targets->sum('target');
            
            $capaian   = $target > 0
                ? round($realisasi / $target * 100, 1)
                : ($realisasi > 0 ? 100 : 0);

            return [
                'realisasi' => $realisasi,
                'target'    => $target,
                'deviasi'   => $realisasi - $target,
                'capaian'   => $capaian,
            ];
        };

        return [
            'pub_nasional'    => $hitung('pub_nasional', $tahun),
            'hki'             => $hitung('hki', $tahun),
            'unit_bisnis'     => $hitung('unit_bisnis', $tahun),
            'intl_selain_q12' => $hitung('intl_selain_q12', $tahun),
            'intl_q12'        => $hitung('intl_q12', $tahun),
        ];
    }

    /**
     * Bangun rekap KPI per triwulan (TW1–TW4) untuk satu tahun.
     * Opsional filter CoE — kalau diisi, hanya menghitung baris milik CoE tersebut.
     */
    private function buildTriwulanKpi(string $tahun, ?string $filterCoe = null): array
    {
        $twRanges = [
            'TW1' => 'Januari – Maret',
            'TW2' => 'April – Juni',
            'TW3' => 'Juli – September',
            'TW4' => 'Oktober – Desember',
        ];

        return collect(array_keys($twRanges))->map(function (string $tw) use ($tahun, $twRanges, $filterCoe) {
            $rows = $this->collectAllApproved($tw, $tahun, null);

            if ($filterCoe) {
                $rows = $rows->where('coe', $filterCoe);
            }

            $realisasi = (int) $rows->sum('realisasi');
            // Ambil target overall untuk triwulan + tahun ini dari master_targets
            $target    = (int) \App\Models\MasterTarget::where('periode', "{$tw} {$tahun}")->sum('target');
            $deviasi   = $realisasi - $target;
            $capaian   = $target > 0
                ? round($realisasi / $target * 100, 1)
                : ($realisasi > 0 ? 100 : 0);

            return [
                'tw'         => $tw,
                'periode'    => "{$tw} {$tahun}",
                'keterangan' => $twRanges[$tw],
                'realisasi'  => $realisasi,
                'target'     => $target,
                'deviasi'    => $deviasi,
                'melampaui'  => $deviasi >= 0,
                'capaian'    => $capaian,
            ];
        })->toArray();
    }

    /**
     * Ambil daftar periode unik yang tersedia di DB.
     */
    private function getAvailablePeriode(): array
    {
        $periodes = collect();
        foreach ($this->models as $modelClass) {
            $periodes = $periodes->merge(
                $modelClass::where('status', 'approved')
                    ->whereNotNull('periode')
                    ->distinct()
                    ->pluck('periode')
            );
        }
        return $periodes->unique()->sort()->values()->toArray();
    }

    /**
     * Ekstrak daftar triwulan unik (TW1, TW2, …) dari daftar periode.
     */
    private function extractTwList(array $periodeList): array
    {
        $tws = collect($periodeList)->map(function ($p) {
            preg_match('/(TW\d)/i', $p, $m);
            return $m[1] ?? null;
        })->filter()->unique()->sort()->values()->toArray();

        return $tws ?: ['TW1', 'TW2', 'TW3', 'TW4'];
    }

    /**
     * Ekstrak daftar tahun unik dari daftar periode.
     */
    private function extractTahunList(array $periodeList): array
    {
        $years = collect($periodeList)->map(function ($p) {
            preg_match('/(\d{4})/', $p, $m);
            return $m[1] ?? null;
        })->filter()->unique()->sortDesc()->values()->toArray();

        return $years ?: [(string) date('Y')];
    }

    /**
     * Bangun daftar laporan siap-unduh, satu baris per periode (TW + Tahun) PER KATEGORI.
     * Setiap baris punya tombol download langsung ke export CSV periode + kategori tersebut.
     */
    private function buildDaftarLaporan(Collection $allData): Collection
    {
        return $allData
            ->groupBy(fn($row) => $row->tahun . '|' . $row->tw . '|' . $row->kategori)
            ->map(function (Collection $rows) {
                $first    = $rows->first();
                $tw       = $first->tw;
                $tahun    = $first->tahun;
                $kategori = $first->kategori;

                // Ambil Target Overall dari master_targets
                $target    = (int) \App\Models\MasterTarget::where('periode', "{$tw} {$tahun}")
                    ->where('kategori', $kategori)
                    ->sum('target');
                $realisasi = (int) $rows->sum('realisasi');
                $deviasi   = $realisasi - $target;

                $tanggalTerakhir = $rows->max('created_at');

                return [
                    'periode_label'    => trim(str_replace('TW', 'TW ', $tw) . ' ' . $tahun),
                    'tw'               => $tw,
                    'tahun'            => $tahun,
                    'kategori'         => $kategori,
                    'jenis_laporan'    => "Laporan {$kategori} Lengkap",
                    'tanggal_generate' => $tanggalTerakhir
                        ? Carbon::parse($tanggalTerakhir)->format('Y-m-d')
                        : now()->format('Y-m-d'),
                    'ukuran_file'      => $this->estimateCsvSize($rows),
                    'entri'            => $rows->count(),
                    'target'           => $target,
                    'realisasi'        => $realisasi,
                    'deviasi'          => $deviasi,
                    'melampaui'        => $deviasi >= 0,
                    'download_url'     => route('dekan.laporan.exportCsv', array_filter([
                        'tw'       => $tw,
                        'tahun'    => $tahun,
                        'kategori' => $kategori,
                    ])),
                    'download_pdf_url' => route('dekan.laporan.exportPdf', array_filter([
                        'tw'       => $tw,
                        'tahun'    => $tahun,
                        'kategori' => $kategori,
                    ])),
                ];
            })
            ->sortBy([
                ['tahun', 'desc'],
                ['tw', 'desc'],
                ['kategori', 'asc'],
            ])
            ->values();
    }

    /**
     * Estimasi ukuran file CSV (header + seluruh baris) dalam format mudah dibaca (KB/MB).
     */
    private function estimateCsvSize(Collection $rows): string
    {
        $bom    = 3; // BOM UTF-8
        $header = "No,Kategori,CoE,Periode,Target,Realisasi,Deviasi\r\n";
        $size   = $bom + strlen($header);

        foreach ($rows as $i => $row) {
            $line  = implode(',', [$i + 1, $row->kategori, $row->coe, $row->periode, $row->target, $row->realisasi, $row->deviasi]);
            $size += strlen($line) + 2; // + CRLF
        }

        return $this->formatBytes($size);
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 1) . ' MB';
        }
        if ($bytes >= 1024) {
            return round($bytes / 1024, 1) . ' KB';
        }
        return $bytes . ' B';
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PAGES
    // ─────────────────────────────────────────────────────────────────────────

    public function dashboard(Request $request)
    {
        $filterTahun = $request->get('tahun', '') ?: (string) date('Y');
        $filterCoe   = $request->get('coe', '') ?: null;

        // ── Data dasar tahun berjalan (BELUM difilter CoE — dipakai untuk
        //    membangun daftar CoE & sebagai basis sebelum filter CoE diterapkan) ──
        $allDataTahunanGlobal = $this->collectAllApproved(null, $filterTahun, null);

        // Nama CoE unik (selalu dari data GLOBAL agar dropdown tetap lengkap
        // walau salah satu CoE sedang difilter)
        $coeNames = $allDataTahunanGlobal->pluck('coe')->filter()->unique()->sort()->values();

        // Daftar CoE untuk dropdown filter & nama CoE terpilih
        $coeList = $coeNames->map(function ($nama) {
            return (object) ['id' => $nama, 'nama' => $nama];
        })->values();

        $filterCoeName = $filterCoe
            ? ($coeList->firstWhere('id', $filterCoe)->nama ?? $filterCoe)
            : null;

        // ── Data tahun berjalan SETELAH filter CoE diterapkan ──
        // Dipakai untuk 5 KPI card (metrics) agar konsisten dengan filter yang dipilih.
        $allDataTahunan = $filterCoe
            ? $allDataTahunanGlobal->where('coe', $filterCoe)->values()
            : $allDataTahunanGlobal;

        $metrics     = $this->buildMetrics($allDataTahunan, $filterTahun);
        $triwulanKpi = $this->buildTriwulanKpi($filterTahun, $filterCoe);

        $periodeList = $this->getAvailablePeriode();
        $tahunList   = $this->extractTahunList($periodeList);
        $tahunRange  = array_reverse($tahunList);

        // Tren realisasi per CoE dipisah per indikator KPI
        $indikatorKeys = ['pub_nasional', 'hki', 'unit_bisnis', 'intl_selain_q12', 'intl_q12'];
        $trenCoe = [];
        foreach ($coeNames as $coeName) {
            foreach ($indikatorKeys as $indKey) {
                $dataTahun = [];
                foreach ($tahunRange as $tahun) {
                    $rows        = $this->collectAllApproved(null, $tahun, null);
                    $dataTahun[] = (int) $rows
                        ->where('coe', $coeName)
                        ->where('indikator', $indKey)
                        ->sum('realisasi');
                }
                $trenCoe[$indKey][$coeName] = $dataTahun;
            }
        }

        // Tren realisasi per kategori lintas tahun
        $trenKategori = [];
        foreach (array_keys($this->models) as $kategori) {
            $dataTahun = [];
            foreach ($tahunRange as $tahun) {
                $rows        = $this->collectAllApproved(null, $tahun, $kategori);
                $dataTahun[] = (int) $rows->sum('realisasi');
            }
            $trenKategori[$kategori] = $dataTahun;
        }

        // ── Tren per indikator KPI untuk chart di dashboard (filterCoe-aware) ──
        $trenData = [];
        foreach ($indikatorKeys as $indKey) {
            $dataTahun = [];
            foreach ($tahunRange as $tahun) {
                $rows = $this->collectAllApproved(null, $tahun, null)
                    ->where('indikator', $indKey);

                if ($filterCoe) {
                    $rows = $rows->where('coe', $filterCoe);
                }

                $dataTahun[] = (int) $rows->sum('realisasi');
            }
            $trenData[$indKey] = [
                'labels' => $tahunRange,
                'data'   => $dataTahun,
            ];
        }

        // ── Snapshot tabel CoE ──
        // Tetap dibangun dari data GLOBAL (semua CoE) supaya tabel "Statistik
        // Realisasi per CoE" selalu menampilkan semua baris CoE, dengan baris
        // yang aktif/dipilih ditandai (lihat $isActive di blade).
        $coeSnapshot = $allDataTahunanGlobal
            ->groupBy('coe')
            ->map(function ($rows, $coe) {
                $target    = 0;
                $realisasi = (int) $rows->sum('realisasi');
                $deviasi   = $realisasi;
                $capaian   = 0;
                return compact('coe', 'target', 'realisasi', 'deviasi', 'capaian');
            })
            ->sortByDesc('realisasi')
            ->values()
            ->toArray();

        // ── 5 Tabel Kategori untuk Analisis Realisasi vs Target ──
        $categoriesList = ['Riset', 'Bisnis', 'Pengabdian', 'Akademik', 'Inovasi'];
        $categoryTables = [];
        $rawOptions = \App\Models\MasterTarget::judulOptions();

        foreach ($categoriesList as $kategori) {
            // Standard options for this category
            $standardOptions = $rawOptions[$kategori] ?? [];

            // Add any other titles that might be in database targets or staff submissions
            $dbTargets = \App\Models\MasterTarget::where('kategori', $kategori)
                ->where('periode', 'like', "%{$filterTahun}%")
                ->distinct()
                ->pluck('judul')
                ->toArray();

            $staffJuduls = $allDataTahunan->where('kategori', $kategori)->pluck('judul')->unique()->toArray();
            
            // Merge all, keeping standard options first in order
            $allJuduls = array_unique(array_merge($standardOptions, $dbTargets, $staffJuduls));

            $indicatorRows = [];
            foreach ($allJuduls as $judul) {
                $targetSum = (int) \App\Models\MasterTarget::where('kategori', $kategori)
                    ->where('periode', 'like', "%{$filterTahun}%")
                    ->where('judul', $judul)
                    ->sum('target');

                $realisasiSum = (int) $allDataTahunan
                    ->where('kategori', $kategori)
                    ->where('judul', $judul)
                    ->sum('realisasi');

                $deviasi = $realisasiSum - $targetSum;

                $indicatorRows[] = [
                    'judul'     => $judul,
                    'target'    => $targetSum,
                    'realisasi' => $realisasiSum,
                    'deviasi'   => $deviasi,
                ];
            }

            $categoryTables[$kategori] = $indicatorRows;
        }

        return view('dekan.dashboard', compact(
            'metrics',
            'triwulanKpi',
            'filterTahun',
            'filterCoe',
            'filterCoeName',
            'coeList',
            'tahunList',
            'categoryTables',
            'tahunRange',
            'trenKategori',
            'trenCoe',
            'trenData',
            'coeNames',
            'coeSnapshot',
        ));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // KINERJA COE
    // ─────────────────────────────────────────────────────────────────────────
    public function kinerjaCoe(Request $request)
    {
        $filterTw       = $request->get('tw', '');
        $filterTahun    = $request->get('tahun', '') ?: (string) date('Y');
        $filterKategori = $request->get('kategori', '');

        // 1. Ambil data approved sesuai filter
        $allData = $this->collectAllApproved(
            $filterTw       ?: null,
            $filterTahun    ?: null,
            $filterKategori ?: null
        );

        // 2. Hitung KPI untuk 3 Card Utama (Publikasi, HKI, Bisnis)
        // 2. Hitung KPI untuk 3 Card Utama (Publikasi, HKI, Bisnis) dari master_targets
        // Publikasi (Riset + Akademik)
        $publikasiData = $allData->filter(fn($row) => in_array($row->kategori, ['Riset', 'Akademik']));
        $targetPublikasiQuery = \App\Models\MasterTarget::whereIn('kategori', ['Riset', 'Akademik']);
        if ($filterTw) { $targetPublikasiQuery->where('periode', 'like', "%{$filterTw}%"); }
        if ($filterTahun) { $targetPublikasiQuery->where('periode', 'like', "%{$filterTahun}%"); }
        $targetPubVal = (int) $targetPublikasiQuery->sum('target');
        
        $kpiPublikasi = [
            'target'    => $targetPubVal,
            'realisasi' => (int) $publikasiData->sum('realisasi'),
            'deviasi'   => (int) $publikasiData->sum('realisasi') - $targetPubVal,
        ];

        // HKI (Inovasi)
        $hkiData = $allData->filter(fn($row) => $row->kategori === 'Inovasi');
        $targetHkiQuery = \App\Models\MasterTarget::where('kategori', 'Inovasi');
        if ($filterTw) { $targetHkiQuery->where('periode', 'like', "%{$filterTw}%"); }
        if ($filterTahun) { $targetHkiQuery->where('periode', 'like', "%{$filterTahun}%"); }
        $targetHkiVal = (int) $targetHkiQuery->sum('target');

        $kpiHki = [
            'target'    => $targetHkiVal,
            'realisasi' => (int) $hkiData->sum('realisasi'),
            'deviasi'   => (int) $hkiData->sum('realisasi') - $targetHkiVal,
        ];

        // Bisnis
        $bisnisData = $allData->filter(fn($row) => $row->kategori === 'Bisnis');
        $targetBisnisQuery = \App\Models\MasterTarget::where('kategori', 'Bisnis');
        if ($filterTw) { $targetBisnisQuery->where('periode', 'like', "%{$filterTw}%"); }
        if ($filterTahun) { $targetBisnisQuery->where('periode', 'like', "%{$filterTahun}%"); }
        $targetBisnisVal = (int) $targetBisnisQuery->sum('target');

        $kpiBisnis = [
            'target'    => $targetBisnisVal,
            'realisasi' => (int) $bisnisData->sum('realisasi'),
            'deviasi'   => (int) $bisnisData->sum('realisasi') - $targetBisnisVal,
        ];

        // 3. Capaian per Triwulan (untuk chart/boxes triwulan)
        $tahunTw = $filterTahun;
        $twRanges = [
            'TW1' => 'Januari – Maret',
            'TW2' => 'April – Juni',
            'TW3' => 'Juli – September',
            'TW4' => 'Oktober – Desember',
        ];

        $triwulanCoe = collect(array_keys($twRanges))->map(function (string $tw) use ($filterTahun, $twRanges, $filterKategori) {
            $rows = $this->collectAllApproved($tw, $filterTahun, $filterKategori ?: null);

            $realisasi = (int) $rows->sum('realisasi');
            // Ambil target overall untuk triwulan + tahun ini
            $qTarget = \App\Models\MasterTarget::where('periode', "{$tw} {$filterTahun}");
            if ($filterKategori) {
                $qTarget->where('kategori', ucfirst($filterKategori));
            }
            $target    = (int) $qTarget->sum('target');
            $deviasi   = $realisasi - $target;
            $pct       = $target > 0 ? round($realisasi / $target * 100, 1) : ($realisasi > 0 ? 100 : 0);

            return [
                'tw'         => $tw,
                'periode'    => "{$tw} {$filterTahun}",
                'keterangan' => $twRanges[$tw],
                'realisasi'  => $realisasi,
                'target'     => $target,
                'deviasi'    => $deviasi,
                'melampaui'  => $deviasi >= 0,
                'pct'        => $pct,
            ];
        })->toArray();

        // 4. Statistik per CoE (Target diset 0 karena target overall)
        $coeStats = $allData
            ->groupBy('coe')
            ->map(function (Collection $rows, $coe) {
                $realisasi = (int) $rows->sum('realisasi');
                return [
                    'coe'       => $coe ?: '(Tidak ada CoE)',
                    'target'    => 0,
                    'realisasi' => $realisasi,
                    'deviasi'   => $realisasi,
                    'capaian'   => 0,
                    'entri'     => $rows->count(),
                ];
            })
            ->sortByDesc('realisasi')
            ->values();

        // 5. Tabel Target vs Realisasi per Kategori (dipecah per indikator/judul)
        // Ambil list master targets sesuai filter
        $qTargetKpi = \App\Models\MasterTarget::query();
        if ($filterTw) { $qTargetKpi->where('periode', 'like', "%{$filterTw}%"); }
        if ($filterTahun) { $qTargetKpi->where('periode', 'like', "%{$filterTahun}%"); }
        if ($filterKategori) { $qTargetKpi->where('kategori', ucfirst($filterKategori)); }
        
        $masterTargetsList = $qTargetKpi->get();

        $targetKpi = $masterTargetsList->map(function ($mt) use ($allData) {
            $real = (int) $allData->filter(function ($row) use ($mt) {
                return strtolower(trim($row->judul)) === strtolower(trim($mt->judul));
            })->sum('realisasi');

            $target = (int) $mt->target;

            return [
                'indikator' => $mt->judul,
                'target'    => $target,
                'realisasi' => $real,
                'deviasi'   => $real - $target,
            ];
        });

        $totalTargetKpi    = (int) $targetKpi->sum('target');
        $totalRealisasiKpi = (int) $targetKpi->sum('realisasi');
        $totalDeviasiKpi   = $totalRealisasiKpi - $totalTargetKpi;

        // 6. Data Tren (dipakai untuk line charts di bagian bawah page)
        $periodeList  = $this->getAvailablePeriode();
        $twList       = $this->extractTwList($periodeList);
        $tahunList    = $this->extractTahunList($periodeList);
        $tahunRange   = array_reverse($tahunList);
        $kategoriList = array_keys($this->models);

        $trenKategori = [];
        foreach ($kategoriList as $kategori) {
            $dataTahun = [];
            foreach ($tahunRange as $tahun) {
                $rows        = $this->collectAllApproved($filterTw ?: null, $tahun, $kategori);
                $dataTahun[] = (int) $rows->sum('realisasi');
            }
            $trenKategori[$kategori] = $dataTahun;
        }

        // Tren per CoE
        $coeNames = $allData->pluck('coe')->filter()->unique()->sort()->values();
        $trenCoe = [];
        foreach ($coeNames as $coeName) {
            $dataTahun = [];
            foreach ($tahunRange as $tahun) {
                $rows        = $this->collectAllApproved($filterTw ?: null, $tahun, $filterKategori ?: null);
                $dataTahun[] = (int) $rows->where('coe', $coeName)->sum('realisasi');
            }
            $trenCoe[$coeName] = $dataTahun;
        }

        return view('dekan.kinerjacoe', compact(
            'twList',
            'tahunList',
            'kategoriList',
            'filterTw',
            'filterTahun',
            'filterKategori',
            'kpiPublikasi',
            'kpiHki',
            'kpiBisnis',
            'tahunTw',
            'triwulanCoe',
            'coeStats',
            'totalTargetKpi',
            'totalRealisasiKpi',
            'totalDeviasiKpi',
            'targetKpi',
            'tahunRange',
            'trenKategori',
            'trenCoe',
            'coeNames',
        ));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // LAPORAN  ← SIMPLIFIED: satu baris per periode, langsung bisa diunduh CSV
    // ─────────────────────────────────────────────────────────────────────────

    public function laporan(Request $request)
    {
        // ── Filter aktif ──────────────────────────────────────────────────────
        $filterTw       = $request->get('tw', '');
        $filterTahun    = $request->get('tahun', '');
        $filterKategori = $request->get('kategori', '');

        // ── Ambil semua data approved sesuai filter ───────────────────────────
        $allData = $this->collectAllApproved(
            $filterTw       ?: null,
            $filterTahun    ?: null,
            $filterKategori ?: null,
        );

        // ── Daftar laporan siap unduh, satu baris per periode + kategori ──────
        $daftarLaporan = $this->buildDaftarLaporan($allData);

        // ── Summary cards ─────────────────────────────────────────────────────
        $summary = $this->buildSummary($allData, $filterTw ?: null, $filterTahun ?: null, $filterKategori ?: null);

        // ── Jumlah periode yang melampaui target ──────────────────────────────
        $periodeOverTarget = $daftarLaporan->where('melampaui', true)->count();

        // ── Total entri raw (bukan grouped) ───────────────────────────────────
        $totalEntri = $allData->count();

        // ── Rekap per Kategori ────────────────────────────────────────────────
        $rekapKategori = collect(array_keys($this->models))->map(function ($kat) use ($allData, $filterTw, $filterTahun) {
            $rows      = $allData->where('kategori', $kat);
            $realisasi = (int) $rows->sum('realisasi');
            
            // Ambil target untuk kategori ini dari master_targets
            $qTarget = \App\Models\MasterTarget::where('kategori', $kat);
            if ($filterTw) { $qTarget->where('periode', 'like', "%{$filterTw}%"); }
            if ($filterTahun) { $qTarget->where('periode', 'like', "%{$filterTahun}%"); }
            $target = (int) $qTarget->sum('target');
            
            $deviasi   = $realisasi - $target;
            $pct       = $target > 0 ? round(($realisasi / $target) * 100, 1) : 0;

            return compact('kat', 'target', 'realisasi', 'deviasi', 'pct');
        })->filter(fn($r) => $r['target'] > 0 || $r['realisasi'] > 0)->values();

        // ── Rekap per CoE ─────────────────────────────────────────────────────
        $rekapCoe = $allData
            ->groupBy('coe')
            ->map(function (Collection $rows, $coe) {
                $realisasi = (int) $rows->sum('realisasi');

                return [
                    'coe'       => $coe ?: '(Tidak ada CoE)',
                    'entri'     => $rows->count(),
                    'target'    => 0,
                    'realisasi' => $realisasi,
                    'deviasi'   => $realisasi,
                    'pct'       => 0,
                    'melampaui' => true,
                ];
            })
            ->sortByDesc('realisasi')
            ->values();

        // ── Dropdown filter ───────────────────────────────────────────────────
        $periodeList  = $this->getAvailablePeriode();
        $twList       = $this->extractTwList($periodeList);
        $tahunList    = $this->extractTahunList($periodeList);
        $kategoriList = array_keys($this->models);

        return view('dekan.laporan', compact(
            'daftarLaporan',
            'summary',
            'periodeOverTarget',
            'totalEntri',
            'rekapKategori',
            'rekapCoe',
            'twList',
            'tahunList',
            'kategoriList',
            'filterTw',
            'filterTahun',
            'filterKategori',
        ));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // EXPORT (CSV saja — Excel sudah dihapus)
    // ─────────────────────────────────────────────────────────────────────────

    public function exportCsv(Request $request): StreamedResponse
    {
        $filterTw       = $request->get('tw', '');
        $filterTahun    = $request->get('tahun', '');
        $filterKategori = $request->get('kategori', '');

        $allData  = $this->collectAllApproved(
            $filterTw       ?: null,
            $filterTahun    ?: null,
            $filterKategori ?: null,
        );

        $namaBagian = trim(($filterTw ?: '') . '-' . ($filterTahun ?: ''), '-') ?: 'semua-periode';
        $filename   = "laporan-dekan-{$namaBagian}-" . now()->format('Ymd') . '.csv';

        return response()->streamDownload(function () use ($allData) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM UTF-8

            fputcsv($handle, ['No', 'Kategori', 'CoE', 'Judul', 'Periode', 'Realisasi']);

            foreach ($allData->values() as $i => $row) {
                fputcsv($handle, [
                    $i + 1,
                    $row->kategori,
                    $row->coe,
                    $row->judul,
                    $row->periode,
                    $row->realisasi,
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function exportPdf(Request $request)
    {
        $filterTw       = $request->get('tw', '');
        $filterTahun    = $request->get('tahun', '');
        $filterKategori = $request->get('kategori', '');

        $allData  = $this->collectAllApproved(
            $filterTw       ?: null,
            $filterTahun    ?: null,
            $filterKategori ?: null,
        );

        $summary = $this->buildSummary($allData, $filterTw ?: null, $filterTahun ?: null, $filterKategori ?: null);

        $namaBagian = trim(($filterTw ?: '') . '-' . ($filterTahun ?: '') . '-' . ($filterKategori ?: ''), '-') ?: 'semua-periode';
        $filename   = "laporan-dekan-{$namaBagian}-" . now()->format('Ymd') . '.pdf';

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('Dekan.laporan_pdf', compact(
            'allData',
            'summary',
            'filterTw',
            'filterTahun',
            'filterKategori'
        ));

        return $pdf->download($filename);
    }
}
