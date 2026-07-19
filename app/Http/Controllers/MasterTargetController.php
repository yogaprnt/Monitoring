<?php

namespace App\Http\Controllers;

use App\Models\MasterTarget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class MasterTargetController extends Controller
{
    public function index()
    {
        $user      = auth()->user();
        $items     = MasterTarget::with('penginput')->latest()->get();
        $judulAll  = MasterTarget::judulOptions();
        $kategoriList = MasterTarget::kategoriList();

        return view('staff.master-target', compact('user', 'items', 'judulAll', 'kategoriList'));
    }

    public function edit($id)
    {
        $user     = auth()->user();
        $item     = MasterTarget::findOrFail($id);
        $items    = MasterTarget::with('penginput')->latest()->get();
        $judulAll = MasterTarget::judulOptions();
        $kategoriList = MasterTarget::kategoriList();

        return view('staff.master-target', compact('user', 'item', 'items', 'judulAll', 'kategoriList'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateData($request);
        $periode = $validated['triwulan'] . ' ' . $validated['tahun'];

        // Cek duplikasi
        $exists = MasterTarget::where('periode', $periode)
            ->where('kategori', $validated['kategori'])
            ->where('judul', $validated['judul'])
            ->exists();

        if ($exists) {
            return back()->withInput()->with('error', 'Master target untuk periode, kategori, dan indikator tersebut sudah terdaftar.');
        }

        $filePendukung = null;

        try {
            if ($request->hasFile('file_pendukung')) {
                $filePendukung = $request->file('file_pendukung')
                    ->store('file-master-target', 'public');
            }

            DB::transaction(function () use ($validated, $filePendukung) {
                MasterTarget::create([
                    'periode'        => $validated['triwulan'] . ' ' . $validated['tahun'],
                    'kategori'       => $validated['kategori'],
                    'judul'          => $validated['judul'],
                    'target'         => $validated['target'],
                    'keterangan'     => $validated['keterangan'] ?? null,
                    'file_pendukung' => $filePendukung,
                    'input_by'       => auth()->id(),
                ]);
            });
        } catch (Throwable $e) {
            if ($filePendukung && Storage::disk('public')->exists($filePendukung)) {
                Storage::disk('public')->delete($filePendukung);
            }
            report($e);
            return back()->withInput()->with('error', 'Master target gagal disimpan. Silakan coba kembali.');
        }

        return redirect()->route('master-target.index')
            ->with('success', 'Master target berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $item      = MasterTarget::findOrFail($id);
        $validated = $this->validateData($request, true);
        $periode = $validated['triwulan'] . ' ' . $validated['tahun'];

        // Cek duplikasi (kecuali item saat ini)
        $exists = MasterTarget::where('periode', $periode)
            ->where('kategori', $validated['kategori'])
            ->where('judul', $validated['judul'])
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return back()->withInput()->with('error', 'Master target untuk periode, kategori, dan indikator tersebut sudah terdaftar.');
        }

        $fileLama  = $item->file_pendukung;
        $fileBaru  = null;

        try {
            if ($request->hasFile('file_pendukung')) {
                $fileBaru = $request->file('file_pendukung')
                    ->store('file-master-target', 'public');
            }

            DB::transaction(function () use ($item, $validated, $fileBaru) {
                $data = [
                    'periode'    => $validated['triwulan'] . ' ' . $validated['tahun'],
                    'kategori'   => $validated['kategori'],
                    'judul'      => $validated['judul'],
                    'target'     => $validated['target'],
                    'keterangan' => $validated['keterangan'] ?? null,
                ];
                if ($fileBaru !== null) $data['file_pendukung'] = $fileBaru;
                $item->update($data);
            });

            if ($fileBaru && $fileLama && Storage::disk('public')->exists($fileLama)) {
                Storage::disk('public')->delete($fileLama);
            }
        } catch (Throwable $e) {
            if ($fileBaru && Storage::disk('public')->exists($fileBaru)) {
                Storage::disk('public')->delete($fileBaru);
            }
            report($e);
            return back()->withInput()->with('error', 'Master target gagal diperbarui.');
        }

        return redirect()->route('master-target.index')
            ->with('success', 'Master target berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $item = MasterTarget::findOrFail($id);
        $file = $item->file_pendukung;

        try {
            DB::transaction(fn() => $item->delete());
            if ($file && Storage::disk('public')->exists($file)) {
                Storage::disk('public')->delete($file);
            }
        } catch (Throwable $e) {
            report($e);
            return redirect()->route('master-target.index')
                ->with('error', 'Master target gagal dihapus.');
        }

        return redirect()->route('master-target.index')
            ->with('success', 'Master target berhasil dihapus.');
    }

    private function validateData(Request $request, bool $isUpdate = false): array
    {
        return $request->validate([
            'triwulan'       => ['required', 'in:TW1,TW2,TW3,TW4'],
            'tahun'          => ['required', 'integer', 'min:2020', 'max:2100'],
            'kategori'       => ['required', 'in:Riset,Bisnis,Akademik,Pengabdian,Inovasi'],
            'judul'          => ['required', 'string', 'max:500'],
            'target'         => ['required', 'integer', 'min:0'],
            'keterangan'     => ['nullable', 'string', 'max:1000'],
            'file_pendukung' => [$isUpdate ? 'nullable' : 'nullable', 'file',
                'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png', 'max:5120'],
        ], [
            'triwulan.required'  => 'Triwulan wajib dipilih.',
            'tahun.required'     => 'Tahun wajib dipilih.',
            'kategori.required'  => 'Kategori wajib dipilih.',
            'judul.required'     => 'Judul/Indikator wajib dipilih.',
            'target.required'    => 'Target wajib diisi.',
            'target.integer'     => 'Target harus berupa angka.',
            'target.min'         => 'Target minimal 0.',
        ]);
    }
}
