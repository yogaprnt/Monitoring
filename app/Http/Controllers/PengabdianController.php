<?php

namespace App\Http\Controllers;

use App\Models\Pengabdian;
use App\Events\InputDataSubmitted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class PengabdianController extends Controller
{
    public function index()
    {
        $user       = auth()->user();
        $pengabdian = Pengabdian::where('input_by', $user->id)->latest()->get();
        return view('staff.createpengabdian', compact('pengabdian', 'user'));
    }

    public function store(Request $request)
    {
        $validated     = $this->validateForm($request);
        $filePendukung = null;

        try {
            if ($request->hasFile('file_pendukung')) {
                $filePendukung = $request->file('file_pendukung')
                    ->store('file-pendukung-pengabdian', 'public');
            }

            DB::transaction(function () use ($validated, $filePendukung) {
                Pengabdian::create([
                    'periode'        => $validated['triwulan'] . ' ' . $validated['tahun'],
                    'judul'          => $validated['judul'],
                    'coe'            => $validated['coe'] ?? null,
                    'target'         => $validated['target'] ?? 0,
                    'realisasi'      => $validated['realisasi'] ?? 0,
                    'keterangan'     => $validated['keterangan'] ?? null,
                    'file_pendukung' => $filePendukung,
                    'status'         => 'submitted',
                    'input_by'       => auth()->id(),
                    'catatan_reject' => null,
                ]);
            });
        } catch (Throwable $e) {
            if ($filePendukung && Storage::disk('public')->exists($filePendukung)) {
                Storage::disk('public')->delete($filePendukung);
            }
            report($e);
            return back()->withInput()->with('error', 'Data pengabdian gagal disimpan.');
        }

        // ✅ Fire event aktivitas input data
        event(new InputDataSubmitted(auth()->user()));

        return redirect()->route('pengabdian.index')
            ->with('success', 'Data pengabdian berhasil disubmit dan menunggu review Asisten Manager.');
    }

    public function edit($id)
    {
        $user = auth()->user();
        $item = Pengabdian::where('input_by', $user->id)->findOrFail($id);

        if (!$item->canBeModifiedByStaff()) {
            return redirect()->route('pengabdian.index')
                ->with('error', 'Data sedang diproses atau sudah final.');
        }

        $pengabdian = Pengabdian::where('input_by', $user->id)->latest()->get();
        return view('staff.createpengabdian', compact('pengabdian', 'user', 'item'));
    }

    public function update(Request $request, $id)
    {
        $item = Pengabdian::where('input_by', auth()->id())->findOrFail($id);

        if (!$item->canBeModifiedByStaff()) {
            return redirect()->route('pengabdian.index')
                ->with('error', 'Data sedang diproses atau sudah final.');
        }

        $validated = $this->validateForm($request);
        $fileLama  = $item->file_pendukung;
        $fileBaru  = null;

        try {
            if ($request->hasFile('file_pendukung')) {
                $fileBaru = $request->file('file_pendukung')
                    ->store('file-pendukung-pengabdian', 'public');
            }

            DB::transaction(function () use ($item, $validated, $fileBaru) {
                $data = [
                    'periode'                     => $validated['triwulan'] . ' ' . $validated['tahun'],
                    'judul'                       => $validated['judul'],
                    'coe'                         => $validated['coe'] ?? null,
                    'target'                      => $validated['target'] ?? 0,
                    'realisasi'                   => $validated['realisasi'] ?? 0,
                    'keterangan'                  => $validated['keterangan'] ?? null,
                    'status'                      => 'submitted',
                    'catatan_reject'              => null,
                    'asisten_manager_approved_by' => null,
                    'asisten_manager_approved_at' => null,
                    'manager_approved_by'         => null,
                    'manager_approved_at'         => null,
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
            return back()->withInput()->with('error', 'Data pengabdian gagal diperbarui.');
        }

        // ✅ Fire event aktivitas input data
        event(new InputDataSubmitted(auth()->user()));

        return redirect()->route('pengabdian.index')
            ->with('success', 'Data pengabdian berhasil diperbaiki dan dikirim kembali.');
    }

    public function destroy($id)
    {
        $item = Pengabdian::where('input_by', auth()->id())->findOrFail($id);

        if (!$item->canBeModifiedByStaff()) {
            return redirect()->route('pengabdian.index')
                ->with('error', 'Data sedang diproses atau sudah final.');
        }

        $file = $item->file_pendukung;

        try {
            DB::transaction(fn() => $item->delete());
            if ($file && Storage::disk('public')->exists($file)) {
                Storage::disk('public')->delete($file);
            }
        } catch (Throwable $e) {
            report($e);
            return redirect()->route('pengabdian.index')->with('error', 'Data gagal dihapus.');
        }

        return redirect()->route('pengabdian.index')->with('success', 'Data pengabdian berhasil dihapus.');
    }

    private function validateForm(Request $request): array
    {
        $isUpdate = $request->isMethod('put') || $request->isMethod('patch');
        return $request->validate([
            'triwulan'       => ['required', 'in:TW1,TW2,TW3,TW4'],
            'tahun'          => ['required', 'integer', 'min:2020', 'max:2100'],
            'judul'          => ['required', 'string', 'max:255'],
            'coe'            => ['required', 'string', 'max:100'],
            'target'         => ['nullable', 'integer', 'min:0'],
            'realisasi'      => ['required', 'integer', 'min:0'],
            'keterangan'     => ['nullable', 'string'],
            'file_pendukung' => [$isUpdate ? 'nullable' : 'required', 'file', 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png', 'max:5120'],
        ]);
    }
}
