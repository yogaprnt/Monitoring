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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Throwable;

class ManagerController extends Controller
{
    private array $models = [
        'Akademik'   => Akademik::class,
        'Bisnis'     => Bisnis::class,
        'Inovasi'    => Inovasi::class,
        'Pengabdian' => Pengabdian::class,
        'Riset'      => Riset::class,
    ];

    /**
     * Jumlah baris per halaman untuk tabel "Detail Data Inputan Staff".
     */
    private const DETAIL_PER_PAGE = 5;

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

    private function getApprovalData(): array
    {
        $pendingRiset      = Riset::where('status', 'reviewed')->count();
        $pendingBisnis     = Bisnis::where('status', 'reviewed')->count();
        $pendingPengabdian = Pengabdian::where('status', 'reviewed')->count();
        $pendingAkademik   = Akademik::where('status', 'reviewed')->count();
        $pendingInovasi    = Inovasi::where('status', 'reviewed')->count();

        $jumlahMenunggu = $pendingRiset + $pendingBisnis + $pendingPengabdian + $pendingAkademik + $pendingInovasi;

        $categories = [
            ['label' => 'Riset',      'icon' => 'fas fa-flask',          'color' => 'bg-blue-50 text-blue-700',     'count' => $pendingRiset],
            ['label' => 'Bisnis',     'icon' => 'fas fa-briefcase',      'color' => 'bg-purple-50 text-purple-700', 'count' => $pendingBisnis],
            ['label' => 'Pengabdian', 'icon' => 'fas fa-hands-helping',  'color' => 'bg-green-50 text-green-700',   'count' => $pendingPengabdian],
            ['label' => 'Akademik',   'icon' => 'fas fa-graduation-cap', 'color' => 'bg-yellow-50 text-yellow-700', 'count' => $pendingAkademik],
            ['label' => 'Inovasi',    'icon' => 'fas fa-lightbulb',      'color' => 'bg-orange-50 text-orange-700', 'count' => $pendingInovasi],
        ];

        $recentReviewed = collect();
        foreach (
            [
                'riset'      => Riset::class,
                'bisnis'     => Bisnis::class,
                'pengabdian' => Pengabdian::class,
                'akademik'   => Akademik::class,
                'inovasi'    => Inovasi::class,
            ] as $tipe => $model
        ) {
            $items = $model::with('penginput')
                ->where('status', 'reviewed')
                ->latest()
                ->get()
                ->map(fn($item) => $item->setAttribute('kategori', $tipe));
            $recentReviewed = $recentReviewed->merge($items);
        }

        $recentReviewed = $recentReviewed->sortByDesc('updated_at');

        return compact(
            'categories',
            'jumlahMenunggu',
            'recentReviewed',
            'pendingRiset',
            'pendingBisnis',
            'pendingPengabdian',
            'pendingAkademik',
            'pendingInovasi'
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // DASHBOARD HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Kumpulkan semua data approved dari setiap model beserta relasi penginput.
     * Sudah support filter periode, coe, dan kategori.
     */
    private function collectAllData(?string $periode, ?string $filterCoe, ?string $filterKategori, ?string $filterTahun = null): Collection
    {
        $merged = collect();

        foreach ($this->models as $kategori => $modelClass) {
            if ($filterKategori && strtolower($filterKategori) !== strtolower($kategori)) {
                continue;
            }

            $query = $modelClass::query()
                ->with('penginput')
                ->where('status', 'approved')
                ->select(['id', 'coe', 'judul', 'periode', 'target', 'realisasi', 'status', 'input_by', 'created_at']);

            if ($periode) {
                $query->where('periode', $periode);
            } elseif ($filterTahun) {
                $query->where('periode', 'like', "%{$filterTahun}%");
            }

            if ($filterCoe) {
                $query->where('coe', $filterCoe);
            }

            $rows = $query->get()->map(function ($row) use ($kategori) {
                $row->kategori = $kategori;
                $row->deviasi  = $row->realisasi - $row->target;
                return $row;
            });

            $merged = $merged->merge($rows);
        }

        return $merged->sortBy('coe')->values();
    }

    private function buildCoeStats(Collection $allData): Collection
    {
        return $allData
            ->groupBy('coe')
            ->map(function (Collection $rows, string $coe) {
                $realisasi = $rows->sum('realisasi');

                return [
                    'coe'       => $coe ?: '(Tidak ada COE)',
                    'target'    => 0, // Target is overall now, not per COE
                    'realisasi' => $realisasi,
                    'deviasi'   => $realisasi, // Show realisasi as their contribution
                    'capaian'   => 0, // Calculated dynamically relative to overall target if needed
                    'entri'     => $rows->count(),
                ];
            })
            ->sortByDesc('realisasi')
            ->values();
    }

    private function buildMetrics(Collection $coeStats, Collection $allData, ?string $periode, ?string $filterKategori, ?string $filterTahun = null): array
    {
        $totalCoe = $coeStats->count();
        $best     = $coeStats->first(); // COE with highest realisasi contribution
        $worst    = $coeStats->last();

        // Hitung Overall Target dari master_targets
        $queryTarget = \App\Models\MasterTarget::query();
        if ($periode) {
            $queryTarget->where('periode', $periode);
        } elseif ($filterTahun) {
            $queryTarget->where('periode', 'like', "%{$filterTahun}%");
        }
        if ($filterKategori) {
            $queryTarget->where('kategori', ucfirst($filterKategori));
        }
        $overallTarget    = (int) $queryTarget->sum('target');
        $overallRealisasi = (int) $allData->sum('realisasi');
        $overallDeviasi   = $overallRealisasi - $overallTarget;
        $overallCapaian   = $overallTarget > 0 ? round(($overallRealisasi / $overallTarget) * 100, 1) : 0;

        return [
            'total_coe'         => $totalCoe,
            'overall_target'    => $overallTarget,
            'overall_realisasi' => $overallRealisasi,
            'overall_deviasi'   => $overallDeviasi,
            'overall_capaian'   => $overallCapaian,
            'best_coe'          => $best,
            'worst_coe'         => $worst,
            'total_entri'       => $allData->count(),
            'total_submitted'   => $this->countByStatus('submitted'),
            'total_approved'    => $this->countByStatus('approved'),
            'total_rejected'    => $this->countByStatus('rejected'),
            'total_pending'     => $this->countByStatus('reviewed'),
        ];
    }

    /**
     * Bangun data chart stacked bar:
     * - Sumbu X  : kategori kegiatan (Akademik, Bisnis, Inovasi, Pengabdian, Riset)
     * - Stack    : setiap COE yang ada di $allData (dinamis, tidak hardcode)
     * - Nilai    : deviasi (realisasi − target) per COE per kategori
     */
    private function buildChartData(Collection $coeStats, Collection $allData, ?string $periode, ?string $filterTahun = null): array
    {
        $categories = ['Akademik', 'Bisnis', 'Inovasi', 'Pengabdian', 'Riset'];
        $coeList    = $coeStats->pluck('coe')->toArray();

        $perKategoriCoe = [];
        foreach ($coeList as $coe) {
            $perKategoriCoe[$coe] = [];
            foreach ($categories as $kat) {
                $rows    = $allData->where('coe', $coe)->where('kategori', $kat);
                $real    = $rows->sum('realisasi');
                $perKategoriCoe[$coe][] = $real; // stack realisasinya
            }
        }

        // Ambil target overall per kategori dari MasterTarget
        $overallTargets = [];
        $overallRealisasis = [];
        foreach ($categories as $kat) {
            $qTarget = \App\Models\MasterTarget::where('kategori', $kat);
            if ($periode) {
                $qTarget->where('periode', $periode);
            } elseif ($filterTahun) {
                $qTarget->where('periode', 'like', "%{$filterTahun}%");
            }
            $overallTargets[] = (int) $qTarget->sum('target');
            $overallRealisasis[] = (int) $allData->where('kategori', $kat)->sum('realisasi');
        }

        return [
            'labels'           => $categories,
            'target'           => $overallTargets,
            'realisasi'        => $overallRealisasis,
            'per_kategori_coe' => $perKategoriCoe,
            'coe_list'         => $coeList,
        ];
    }

    private function countByStatus(string $status): int
    {
        $total = 0;
        foreach ($this->models as $modelClass) {
            $total += $modelClass::where('status', $status)->count();
        }
        return $total;
    }

    private function getAvailablePeriode(): array
    {
        $periodes = collect();
        foreach ($this->models as $modelClass) {
            $periodes = $periodes->merge(
                $modelClass::query()->whereNotNull('periode')->distinct()->pluck('periode')
            );
        }
        return $periodes->unique()->sort()->values()->toArray();
    }

    private function getAvailableCoe(): array
    {
        $coes = collect();
        foreach ($this->models as $modelClass) {
            $coes = $coes->merge(
                $modelClass::query()
                    ->whereNotNull('coe')
                    ->where('status', 'approved')
                    ->distinct()
                    ->pluck('coe')
            );
        }
        return $coes->unique()->sort()->values()->toArray();
    }

    private function collectAllApproved(?string $filterTw, ?string $filterTahun, ?string $filterKategori): Collection
    {
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
                $row->kategori = $kategori;
                $row->deviasi  = $row->realisasi - $row->target;
                return $row;
            });

            $merged = $merged->merge($rows);
        }

        return $merged->values();
    }

    private function extractTwList(array $periodeList): array
    {
        $tws = collect($periodeList)->map(function ($p) {
            preg_match('/(TW\d)/i', $p, $m);
            return $m[1] ?? null;
        })->filter()->unique()->sort()->values()->toArray();

        return $tws ?: ['TW1', 'TW2', 'TW3', 'TW4'];
    }

    private function extractTahunList(array $periodeList): array
    {
        $years = collect($periodeList)->map(function ($p) {
            preg_match('/(\d{4})/', $p, $m);
            return $m[1] ?? null;
        })->filter()->unique()->sortDesc()->values()->toArray();

        return $years ?: [(string) date('Y')];
    }

    /**
     * Bangun paginator manual untuk tabel "Detail Data Inputan Staff".
     */
    private function buildDetailPagination(Collection $allData, Request $request): LengthAwarePaginator
    {
        $sorted = $allData->sortByDesc(function ($row) {
            return $row->created_at;
        })->values();

        $perPage     = self::DETAIL_PER_PAGE;
        $currentPage = LengthAwarePaginator::resolveCurrentPage('page');
        $currentPage = max(1, (int) $currentPage);

        $items = $sorted->slice(($currentPage - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator(
            $items,
            $sorted->count(),
            $perPage,
            $currentPage,
            [
                'path'     => $request->url(),
                'query'    => $request->query(),
                'pageName' => 'page',
            ]
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PUBLIC ACTIONS
    // ─────────────────────────────────────────────────────────────────────────

    public function dashboard(Request $request)
    {
        $periode        = $request->get('periode', '');
        $filterCoe      = $request->get('coe', '');
        $filterKategori = $request->get('kategori', '');
        $filterTahun    = (string) date('Y');

        $allData   = $this->collectAllData($periode ?: null, $filterCoe ?: null, $filterKategori ?: null, $filterTahun);
        $coeStats  = $this->buildCoeStats($allData);
        $metrics   = $this->buildMetrics($coeStats, $allData, $periode ?: null, $filterKategori ?: null, $filterTahun);

        // ← $allData ikut dikirim sebagai argumen kedua
        $chartData = $this->buildChartData($coeStats, $allData, $periode ?: null, $filterTahun);

        $detailData = $this->buildDetailPagination($allData, $request);

        $periodeList  = $this->getAvailablePeriode();
        $coeList      = $this->getAvailableCoe();
        $kategoriList = array_keys($this->models);

        // ── 5 Tabel Kategori untuk Analisis Realisasi vs Target ──
        $categoriesList = ['Riset', 'Bisnis', 'Pengabdian', 'Akademik', 'Inovasi'];
        $categoryTables = [];
        $rawOptions = \App\Models\MasterTarget::judulOptions();

        foreach ($categoriesList as $kategori) {
            $standardOptions = $rawOptions[$kategori] ?? [];

            $qTargets = \App\Models\MasterTarget::where('kategori', $kategori);
            if ($periode) {
                $qTargets->where('periode', $periode);
            } elseif ($filterTahun) {
                $qTargets->where('periode', 'like', "%{$filterTahun}%");
            }
            $dbTargets = $qTargets->distinct()->pluck('judul')->toArray();

            $staffJuduls = $allData->where('kategori', $kategori)->pluck('judul')->unique()->toArray();
            
            $allJuduls = array_unique(array_merge($standardOptions, $dbTargets, $staffJuduls));

            $indicatorRows = [];
            foreach ($allJuduls as $judul) {
                $qTargetSum = \App\Models\MasterTarget::where('kategori', $kategori)
                    ->where('judul', $judul);
                if ($periode) {
                    $qTargetSum->where('periode', $periode);
                } elseif ($filterTahun) {
                    $qTargetSum->where('periode', 'like', "%{$filterTahun}%");
                }
                $targetSum = (int) $qTargetSum->sum('target');

                $realisasiSum = (int) $allData
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

        return view('manager.dashboard', compact(
            'metrics',
            'coeStats',
            'chartData',
            'allData',
            'detailData',
            'periodeList',
            'coeList',
            'kategoriList',
            'periode',
            'filterCoe',
            'filterKategori',
            'categoryTables',
            'filterTahun',
        ));
    }

    public function approve()
    {
        return view('manager.approve', $this->getApprovalData());
    }
    public function detail(string $tipe, $id)
    {
        $model    = $this->getModel($tipe);
        $approval = $model::with('penginput')->findOrFail($id);

        if ($approval->status !== 'reviewed') {
            return redirect()->route('manager.approve')
                ->with('error', 'Data sudah tidak menunggu review Manager.');
        }

        return view('manager.detail-approval', compact('approval', 'tipe'));
    }

    public function approveData(Request $request, string $tipe, $id)
    {
        $model = $this->getModel($tipe);

        try {
            DB::transaction(function () use ($request, $model, $id) {
                $item = $model::where('status', 'reviewed')
                    ->lockForUpdate()
                    ->findOrFail($id);

                $item->update([
                    'status'              => 'approved',
                    'manager_approved_by' => $request->user()->id,
                    'manager_approved_at' => now(),
                    'catatan_reject'      => null,
                ]);
            });
        } catch (Throwable $e) {
            report($e);
            return redirect()->route('manager.approve')
                ->with('error', 'Data gagal disetujui secara final.');
        }

        event(new ApproveSubmitted($request->user()));

        return redirect()->route('manager.approve')
            ->with('success', 'Data ' . ucfirst($tipe) . ' berhasil disetujui dan ditampilkan pada Dashboard.');
    }

    public function rejectData(Request $request, string $tipe, $id)
    {
        $validated = $request->validate([
            'catatan_reject' => ['required', 'string', 'max:1000'],
        ], [
            'catatan_reject.required' => 'Alasan penolakan wajib diisi.',
            'catatan_reject.max'      => 'Alasan penolakan maksimal 1000 karakter.',
        ]);

        $model = $this->getModel($tipe);

        try {
            DB::transaction(function () use ($validated, $model, $id) {
                $item = $model::where('status', 'reviewed')
                    ->lockForUpdate()
                    ->findOrFail($id);

                $item->update([
                    'status'              => 'rejected_by_manager',
                    'catatan_reject'      => $validated['catatan_reject'],
                    'manager_approved_by' => null,
                    'manager_approved_at' => null,
                ]);
            });
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return redirect()->route('manager.approve')
                ->with('error', 'Data tidak ditemukan atau sudah tidak berstatus reviewed.');
        } catch (Throwable $e) {
            report($e);
            return redirect()->route('manager.approve')
                ->with('error', 'Data gagal ditolak oleh Manager.');
        }

        event(new RejectSubmitted($request->user()));

        return redirect()->route('manager.approve')
            ->with('success', 'Data ' . ucfirst($tipe) . ' ditolak dan dikembalikan kepada Staff.');
    }
}
