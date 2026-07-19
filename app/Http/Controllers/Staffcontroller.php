<?php

namespace App\Http\Controllers;

use App\Models\Riset;
use App\Models\Bisnis;
use App\Models\Pengabdian;
use App\Models\Akademik;
use App\Models\Inovasi;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    public function dashboard()
    {
        $userId = auth()->id();

        // Semua model & label jenis datanya
        $models = [
            'Riset'      => Riset::class,
            'Bisnis'     => Bisnis::class,
            'Pengabdian' => Pengabdian::class,
            'Akademik'   => Akademik::class,
            'Inovasi'    => Inovasi::class,
        ];

        $dokumenSudahDiinput  = 0; // semua yang pernah diinput staff ini
        $totalDokumenApproved = 0; // sudah approved oleh manager (final)
        $dokumenTertunda      = 0; // submitted atau reviewed (belum final)
        $dokumenDiproses      = collect(); // untuk tabel

        foreach ($models as $jenis => $model) {
            $base = $model::where('input_by', $userId);

            // Total semua dokumen yang diinput
            $dokumenSudahDiinput += (clone $base)->count();

            // Approved final oleh manager
            $totalDokumenApproved += (clone $base)->where('status', 'approved')->count();

            // Tertunda = submitted (di asman) atau reviewed (di manager) — belum final
            $dokumenTertunda += (clone $base)
                ->whereIn('status', ['submitted', 'reviewed'])
                ->count();

            // Tabel: semua status kecuali approved, ambil 20 terbaru per jenis
            $items = (clone $base)
                ->whereNotIn('status', ['approved'])
                ->latest()
                ->limit(20)
                ->get()
                ->map(fn($item) => (object)[
                    'judul'      => $item->judul,
                    'status'     => $item->status,
                    'jenis_data' => $jenis,
                ]);

            $dokumenDiproses = $dokumenDiproses->merge($items);
        }

        // Urutkan terbaru di atas, batasi 30 baris
        $dokumenDiproses = $dokumenDiproses
            ->sortByDesc(fn($i) => $i->status === 'submitted' ? 2 : ($i->status === 'reviewed' ? 1 : 0))
            ->values()
            ->take(30);

        return view('staff.dashboard', compact(
            'dokumenSudahDiinput',
            'totalDokumenApproved',
            'dokumenTertunda',
            'dokumenDiproses'
        ));
    }
}
