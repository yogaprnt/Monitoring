<?php

namespace App\Http\Controllers;

use App\Models\Riset;
use App\Models\Bisnis;
use App\Models\Pengabdian;
use App\Models\Akademik;
use App\Models\Inovasi;
use App\Events\ApproveSubmitted;
use App\Events\RejectSubmitted;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class AsistenManagerController extends Controller
{
    private array $models = [
        'Akademik'   => Akademik::class,
        'Bisnis'     => Bisnis::class,
        'Inovasi'    => Inovasi::class,
        'Pengabdian' => Pengabdian::class,
        'Riset'      => Riset::class,
    ];

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

    private function getModel(string $tipe): string
    {
        return match (strtolower($tipe)) {
            'riset'      => Riset::class,
            'bisnis'     => Bisnis::class,
            'pengabdian' => Pengabdian::class,
            'akademik'   => Akademik::class,
            'inovasi'    => Inovasi::class,
            default      => abort(404, 'Tipe data tidak dikenali.'),
        };
    }

    private function countByStatus(string $status): int
    {
        $total = 0;
        foreach ($this->models as $modelClass) {
            $total += $modelClass::where('status', $status)->count();
        }
        return $total;
    }

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

    private function getPendingStats(): array
    {
        $pendingRiset      = Riset::where('status', 'submitted')->count();
        $pendingBisnis     = Bisnis::where('status', 'submitted')->count();
        $pendingPengabdian = Pengabdian::where('status', 'submitted')->count();
        $pendingAkademik   = Akademik::where('status', 'submitted')->count();
        $pendingInovasi    = Inovasi::where('status', 'submitted')->count();

        $jumlahMenunggu = $pendingRiset + $pendingBisnis + $pendingPengabdian + $pendingAkademik + $pendingInovasi;

        $categories = [
            ['label' => 'Riset',      'icon' => 'fas fa-flask',          'color' => 'bg-blue-50 text-blue-700',     'count' => $pendingRiset],
            ['label' => 'Bisnis',     'icon' => 'fas fa-briefcase',      'color' => 'bg-purple-50 text-purple-700', 'count' => $pendingBisnis],
            ['label' => 'Pengabdian', 'icon' => 'fas fa-hands-helping',  'color' => 'bg-green-50 text-green-700',   'count' => $pendingPengabdian],
            ['label' => 'Akademik',   'icon' => 'fas fa-graduation-cap', 'color' => 'bg-yellow-50 text-yellow-700', 'count' => $pendingAkademik],
            ['label' => 'Inovasi',    'icon' => 'fas fa-lightbulb',      'color' => 'bg-orange-50 text-orange-700', 'count' => $pendingInovasi],
        ];

        return compact('categories', 'jumlahMenunggu');
    }

    private function collectAllData(?string $periode, ?string $filterCoe, ?string $filterIndikator, ?string $filterTahun = null): Collection
    {
        $merged = collect();

        foreach ($this->models as $kategori => $modelClass) {
            $query = $modelClass::query()
                ->with('penginput')
                ->where('status', 'approved')
                ->select(['id', 'coe', 'judul', 'periode', 'target', 'realisasi', 'status', 'input_by']);

            if ($periode) {
                $query->where('periode', $periode);
            } elseif ($filterTahun) {
                $query->where('periode', 'like', "%{$filterTahun}%");
            }

            if ($filterCoe) {
                $query->where('coe', $filterCoe);
            }

            $rows = $query->get()->map(function ($row) use ($kategori) {
                $row->kategori  = $kategori;
                $row->deviasi   = $row->realisasi - $row->target;
                $row->indikator = $this->resolveIndikator($row->judul);
                return $row;
            });

            if ($filterIndikator) {
                $rows = $rows->filter(fn($row) => $row->indikator === $filterIndikator)->values();
            }

            $merged = $merged->merge($rows);
        }

        return $merged->sortBy('coe')->values();
    }

    private function buildMetrics(Collection $allData, ?string $tahun = null): array
    {
        $hitungIndikator = function (string $key) use ($allData, $tahun): array {
            $rows      = $allData->where('indikator', $key);
            $realisasi = (int) $rows->sum('realisasi');
            
            // Ambil Target Overall dari master_targets
            $query = \App\Models\MasterTarget::query();
            if ($tahun) {
                $query->where('periode', 'like', "%{$tahun}%");
            }
            $targets = $query->get()->filter(function($mt) use ($key) {
                return $this->resolveIndikator($mt->judul) === $key;
            });
            $target = (int) $targets->sum('target');

            $capaian = $target > 0
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
            'pub_nasional'    => $hitungIndikator('pub_nasional', $tahun),
            'hki'             => $hitungIndikator('hki', $tahun),
            'unit_bisnis'     => $hitungIndikator('unit_bisnis', $tahun),
            'intl_selain_q12' => $hitungIndikator('intl_selain_q12', $tahun),
            'intl_q12'        => $hitungIndikator('intl_q12', $tahun),
        ];
    }

    private function buildTriwulan(int $tahun, ?string $filterCoe = null): array
    {
        $twConfig = [
            'TW1' => 'Januari – Maret',
            'TW2' => 'April – Juni',
            'TW3' => 'Juli – September',
            'TW4' => 'Oktober – Desember',
        ];

        return collect(array_keys($twConfig))->map(function (string $twKey) use ($tahun, $twConfig, $filterCoe) {
            $realisasi = 0;
            // Ambil Target Overall triwulan ini dari master_targets
            $target = (int) \App\Models\MasterTarget::where('periode', "{$twKey} {$tahun}")->sum('target');

            foreach ($this->models as $modelClass) {
                $query = $modelClass::where('status', 'approved')
                    ->where('periode', 'like', "%{$twKey}%")
                    ->where('periode', 'like', "%{$tahun}%");

                if ($filterCoe) {
                    $query->where('coe', $filterCoe);
                }

                $realisasi += (int) $query->sum('realisasi');
            }

            $deviasi = $realisasi - $target;
            $capaian = $target > 0
                ? round($realisasi / $target * 100, 1)
                : ($realisasi > 0 ? 100 : 0);

            return [
                'tw'         => $twKey,
                'periode'    => "{$twKey} {$tahun}",
                'keterangan' => $twConfig[$twKey],
                'realisasi'  => $realisasi,
                'target'     => $target,
                'deviasi'    => $deviasi,
                'melampaui'  => $deviasi >= 0,
                'capaian'    => $capaian,
            ];
        })->toArray();
    }

    private function extractTahunList(array $periodeList): array
    {
        $years = collect($periodeList)->map(function ($p) {
            preg_match('/(\d{4})/', $p, $m);
            return $m[1] ?? null;
        })->filter()->unique()->sortDesc()->values()->toArray();

        return $years ?: [(string) date('Y')];
    }

    private function buildChartData(int $tahunAwal, int $tahunAkhir): array
    {
        $years = range($tahunAwal, $tahunAkhir);

        $allApproved = collect();
        foreach ($this->models as $modelClass) {
            $rows = $modelClass::where('status', 'approved')
                ->whereNotNull('coe')
                ->select(['coe', 'judul', 'periode', 'realisasi'])
                ->get()
                ->map(function ($row) {
                    $row->indikator = $this->resolveIndikator($row->judul);
                    return $row;
                });
            $allApproved = $allApproved->merge($rows);
        }

        $coeNames = $allApproved->pluck('coe')->filter()->unique()->sort()->values();

        $palette = [
            '#7F77DD',
            '#D4537E',
            '#1D9E75',
            '#EF9F27',
            '#378ADD',
            '#D85A30',
            '#085041',
            '#633806',
            '#993556',
            '#185FA5',
        ];

        $indikatorKeys = array_keys($this->indikatorMap);

        $chartCoes = $coeNames->map(function ($coeName, $idx) use ($years, $indikatorKeys, $palette, $allApproved) {
            $dataForCoe = $allApproved->where('coe', $coeName);
            $data = [];

            foreach ($indikatorKeys as $key) {
                $rowsForIndikator = $dataForCoe->where('indikator', $key);
                $yearlyData = [];
                foreach ($years as $year) {
                    $total = $rowsForIndikator
                        ->filter(fn($row) => str_contains((string) $row->periode, (string) $year))
                        ->sum('realisasi');
                    $yearlyData[] = (int) $total;
                }
                $data[$key] = $yearlyData;
            }

            return [
                'name'  => $coeName,
                'color' => $palette[$idx % count($palette)],
                'data'  => $data,
            ];
        })->values()->toArray();

        return [
            'chartYears' => array_map('strval', $years),
            'chartCoes'  => $chartCoes,
            'coeList'    => collect($chartCoes)->map(fn($c) => [
                'name'  => $c['name'],
                'color' => $c['color'],
            ])->toArray(),
        ];
    }

    private function getAvailablePeriode(): array
    {
        $periodes = collect();
        foreach ($this->models as $modelClass) {
            $periodes = $periodes->merge(
                $modelClass::whereNotNull('periode')->distinct()->pluck('periode')
            );
        }
        return $periodes->unique()->sort()->values()->toArray();
    }

    private function getAvailableCoe(): array
    {
        $coes = collect();
        foreach ($this->models as $modelClass) {
            $coes = $coes->merge(
                $modelClass::whereNotNull('coe')
                    ->where('status', 'approved')
                    ->distinct()
                    ->pluck('coe')
            );
        }
        return $coes->unique()->sort()->values()->toArray();
    }

    public function dashboard(Request $request)
    {
        $periode         = $request->get('periode', '');
        $filterCoe       = $request->get('coe', '') ?: null;
        $filterIndikator = $request->get('indikator', '');
        $filterTahun     = $request->get('tahun', '') ?: (string) date('Y');
        $tahun           = (int) $filterTahun;
        $tahunAwal       = 2021;

        // Fetch annual approved data for the dashboard statistics
        $allDataTahunanGlobal = collect();
        foreach ($this->models as $kategori => $modelClass) {
            $rows = $modelClass::where('status', 'approved')
                ->where('periode', 'like', "%{$filterTahun}%")
                ->get()
                ->map(function ($row) use ($kategori) {
                    $row->kategori = $kategori;
                    $row->indikator = $this->resolveIndikator($row->judul);
                    $row->deviasi = $row->realisasi - $row->target;
                    return $row;
                });
            $allDataTahunanGlobal = $allDataTahunanGlobal->merge($rows);
        }

        $allDataTahunan = $filterCoe
            ? $allDataTahunanGlobal->where('coe', $filterCoe)->values()
            : $allDataTahunanGlobal;

        $allData = $this->collectAllData(
            $periode ?: null,
            $filterCoe ?: null,
            $filterIndikator ?: null,
            $filterTahun
        );

        $metrics   = $this->buildMetrics($allDataTahunan, $filterTahun);
        $triwulan  = $this->buildTriwulan($tahun, $filterCoe);
        $chartData = $this->buildChartData($tahunAwal, $tahun);

        $periodeList = $this->getAvailablePeriode();
        $coeFilterList = $this->getAvailableCoe();
        $tahunList = $this->extractTahunList($periodeList);

        $indikatorList = collect($this->indikatorMap)->map(fn($cfg, $key) => [
            'key'   => $key,
            'label' => $cfg['label'],
        ])->values();

        // Daftar indicator dengan gap (deviasi negatif) dari target overall
        $topGaps = collect();
        $grouped = $allData->groupBy(function($item) {
            return $item->periode . '|' . $item->kategori . '|' . $item->judul;
        });

        foreach ($grouped as $key => $rows) {
            $parts = explode('|', $key);
            $itemPeriode = $parts[0];
            $itemKategori = $parts[1];
            $itemJudul = $parts[2];

            $realisasiSum = $rows->sum('realisasi');
            
            $targetSum = (int) \App\Models\MasterTarget::where('periode', $itemPeriode)
                ->where('kategori', $itemKategori)
                ->where('judul', $itemJudul)
                ->value('target');

            $dev = $realisasiSum - $targetSum;

            if ($dev < 0) {
                $topGaps->push((object)[
                    'judul'     => $itemJudul,
                    'kategori'  => $itemKategori,
                    'periode'   => $itemPeriode,
                    'realisasi' => $realisasiSum,
                    'target'    => $targetSum,
                    'deviasi'   => $dev,
                ]);
            }
        }

        $topGaps = $topGaps->sortBy('deviasi')->values();
        $totalMasalah = $topGaps->count();

        $chartYears = $chartData['chartYears'];
        $chartCoes  = $chartData['chartCoes'];
        $coeList    = collect($chartData['coeList']);

        // Data ringkas untuk chart "Tren Capaian per Triwulan" (realisasi total lintas TW)
        $triwulanChartLabels    = array_map(fn($tw) => $tw['tw'], $triwulan); // TW1, TW2, TW3, TW4
        $triwulanChartRealisasi = array_map(fn($tw) => $tw['realisasi'], $triwulan);

        return view('asisten_manager.dashboard', compact(
            'metrics',
            'triwulan',
            'allData',
            'periodeList',
            'coeFilterList',
            'indikatorList',
            'coeList',
            'chartYears',
            'chartCoes',
            'periode',
            'filterCoe',
            'filterIndikator',
            'tahun',
            'tahunAwal',
            'topGaps',
            'totalMasalah',
            'triwulanChartLabels',
            'triwulanChartRealisasi',
            'filterTahun',
            'tahunList',
        ));
    }

    public function approve()
    {
        $stats = $this->getPendingStats();

        $approvals = collect();

        foreach ($this->models as $tipe => $modelClass) {
            $items = $modelClass::with('penginput')
                ->where('status', 'submitted')
                ->latest()
                ->get()
                ->map(fn($item) => $item->setAttribute('tipe', strtolower($tipe)));

            $approvals = $approvals->merge($items);
        }

        return view('asisten_manager.approve', array_merge($stats, compact('approvals')));
    }

    public function detail(string $tipe, $id)
    {
        $model    = $this->getModel($tipe);
        $approval = $model::with('penginput')->findOrFail((int) $id);

        if ($approval->status !== 'submitted') {
            return redirect()->route('asisten_manager.approve')
                ->with('error', 'Data sudah tidak menunggu review Asisten Manager.');
        }

        return view('asisten_manager.detail-approval', compact('approval', 'tipe'));
    }

    public function approveItem(Request $request, string $tipe, $id)
    {
        $model = $this->getModel($tipe);
        $id    = (int) $id;

        try {
            DB::transaction(function () use ($request, $model, $id) {
                $item = $model::lockForUpdate()->findOrFail($id);

                if ($item->status !== 'submitted') {
                    throw new \RuntimeException('Data sudah tidak berstatus submitted.');
                }

                $item->update([
                    'status'                      => 'reviewed',
                    'asisten_manager_approved_by' => $request->user()->id,
                    'asisten_manager_approved_at' => now(),
                    'manager_approved_by'         => null,
                    'manager_approved_at'         => null,
                    'catatan_reject'              => null,
                ]);
            });
        } catch (\RuntimeException $e) {
            return redirect()->route('asisten_manager.approve')
                ->with('error', $e->getMessage());
        } catch (Throwable $e) {
            Log::error('approveItem error', ['tipe' => $tipe, 'id' => $id, 'error' => $e->getMessage()]);
            return redirect()->route('asisten_manager.approve')
                ->with('error', 'Data gagal diteruskan kepada Manager. (' . $e->getMessage() . ')');
        }

        event(new ApproveSubmitted($request->user()));

        return redirect()->route('asisten_manager.approve')
            ->with('success', 'Data ' . ucfirst($tipe) . ' berhasil disetujui dan diteruskan kepada Manager.');
    }

    public function rejectItem(Request $request, string $tipe, $id)
    {
        $validated = $request->validate([
            'catatan_reject' => ['required', 'string', 'max:1000'],
        ], [
            'catatan_reject.required' => 'Alasan penolakan wajib diisi.',
            'catatan_reject.max'      => 'Alasan penolakan maksimal 1000 karakter.',
        ]);

        $model = $this->getModel($tipe);
        $id    = (int) $id;

        try {
            DB::transaction(function () use ($validated, $model, $id) {
                $item = $model::lockForUpdate()->findOrFail($id);

                if ($item->status !== 'submitted') {
                    throw new \RuntimeException('Data sudah tidak berstatus submitted.');
                }

                $item->update([
                    'status'                      => 'rejected_by_asman',
                    'catatan_reject'              => $validated['catatan_reject'],
                    'asisten_manager_approved_by' => null,
                    'asisten_manager_approved_at' => null,
                    'manager_approved_by'         => null,
                    'manager_approved_at'         => null,
                ]);
            });
        } catch (\RuntimeException $e) {
            return redirect()->route('asisten_manager.approve')
                ->with('error', $e->getMessage());
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return redirect()->route('asisten_manager.approve')
                ->with('error', 'Data tidak ditemukan.');
        } catch (Throwable $e) {
            Log::error('rejectItem error', ['tipe' => $tipe, 'id' => $id, 'error' => $e->getMessage()]);
            return redirect()->route('asisten_manager.approve')
                ->with('error', 'Data gagal ditolak. (' . $e->getMessage() . ')');
        }

        event(new RejectSubmitted($request->user()));

        return redirect()->route('asisten_manager.approve')
            ->with('success', 'Data ' . ucfirst($tipe) . ' ditolak dan dikembalikan kepada Staff.');
    }
}
