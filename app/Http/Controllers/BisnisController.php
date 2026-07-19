<?php

namespace App\Http\Controllers;

use App\Models\Bisnis;
use App\Events\InputDataSubmitted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class BisnisController extends Controller
{
    public function index()
    {
        $user   = auth()->user();
        $bisnis = Bisnis::where('input_by', $user->id)->latest()->get();
        return view('staff.createbisnis', compact('bisnis', 'user'));
    }

    public function store(Request $request)
    {
        $validated     = $this->validateBisnis($request);
        $filePendukung = null;

        try {
            if ($request->hasFile('file_pendukung')) {
                $filePendukung = $request->file('file_pendukung')
                    ->store('file-pendukung-bisnis', 'public');
            }

            DB::transaction(function () use ($validated, $filePendukung) {
                Bisnis::create([
                    'periode'                     => $validated['triwulan'] . ' ' . $validated['tahun'],
                    'judul'                       => $validated['judul'],
                    'coe'                         => $validated['coe'] ?? null,
                    'target'                      => $validated['target'] ?? 0,
                    'realisasi'                   => $validated['realisasi'] ?? 0,
                    'keterangan'                  => $validated['keterangan'] ?? null,
                    'file_pendukung'              => $filePendukung,
                    'status'                      => 'submitted',
                    'input_by'                    => auth()->id(),
                    'catatan_reject'              => null,
                    'asisten_manager_approved_by' => null,
                    'asisten_manager_approved_at' => null,
                    'manager_approved_by'         => null,
                    'manager_approved_at'         => null,
                ]);
            });
        } catch (Throwable $exception) {
            if ($filePendukung && Storage::disk('public')->exists($filePendukung)) {
                Storage::disk('public')->delete($filePendukung);
            }
            report($exception);
            return back()->withInput()->with('error', 'Data bisnis gagal disimpan. Silakan coba kembali.');
        }

        // ✅ Fire event aktivitas input data
        event(new InputDataSubmitted(auth()->user()));

        return redirect()->route('bisnis.index')
            ->with('success', 'Data bisnis berhasil disubmit dan menunggu review Asisten Manager.');
    }

    public function edit($id)
    {
        $user = auth()->user();
        $item = Bisnis::where('input_by', $user->id)->findOrFail($id);

        if (!$item->canBeModifiedByStaff()) {
            return redirect()->route('bisnis.index')
                ->with('error', 'Data sedang diproses atau sudah final sehingga tidak dapat diedit.');
        }

        $bisnis = Bisnis::where('input_by', $user->id)->latest()->get();
        return view('staff.createbisnis', compact('bisnis', 'user', 'item'));
    }

    public function update(Request $request, $id)
    {
        $item = Bisnis::where('input_by', auth()->id())->findOrFail($id);

        if (!$item->canBeModifiedByStaff()) {
            return redirect()->route('bisnis.index')
                ->with('error', 'Data sedang diproses atau sudah final sehingga tidak dapat diedit.');
        }

        $validated = $this->validateBisnis($request);
        $fileLama  = $item->file_pendukung;
        $fileBaru  = null;

        try {
            if ($request->hasFile('file_pendukung')) {
                $fileBaru = $request->file('file_pendukung')
                    ->store('file-pendukung-bisnis', 'public');
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

            if ($fileBaru !== null && $fileLama && Storage::disk('public')->exists($fileLama)) {
                Storage::disk('public')->delete($fileLama);
            }
        } catch (Throwable $exception) {
            if ($fileBaru && Storage::disk('public')->exists($fileBaru)) {
                Storage::disk('public')->delete($fileBaru);
            }
            report($exception);
            return back()->withInput()->with('error', 'Data bisnis gagal diperbarui. Silakan coba kembali.');
        }

        // ✅ Fire event aktivitas input data
        event(new InputDataSubmitted(auth()->user()));

        return redirect()->route('bisnis.index')
            ->with('success', 'Data bisnis berhasil diperbaiki dan dikirim kembali kepada Asisten Manager.');
    }

    public function destroy($id)
    {
        $item = Bisnis::where('input_by', auth()->id())->findOrFail($id);

        if (!$item->canBeModifiedByStaff()) {
            return redirect()->route('bisnis.index')
                ->with('error', 'Data sedang diproses atau sudah final sehingga tidak dapat dihapus.');
        }

        $filePendukung = $item->file_pendukung;

        try {
            DB::transaction(fn() => $item->delete());
            if ($filePendukung && Storage::disk('public')->exists($filePendukung)) {
                Storage::disk('public')->delete($filePendukung);
            }
        } catch (Throwable $exception) {
            report($exception);
            return redirect()->route('bisnis.index')->with('error', 'Data bisnis gagal dihapus. Silakan coba kembali.');
        }

        return redirect()->route('bisnis.index')->with('success', 'Data bisnis berhasil dihapus.');
    }

    private function validateBisnis(Request $request): array
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
        ], [
            'triwulan.required'    => 'Triwulan wajib dipilih.',
            'triwulan.in'          => 'Triwulan tidak valid.',
            'tahun.required'       => 'Tahun wajib dipilih.',
            'tahun.integer'        => 'Tahun harus berupa angka.',
            'judul.required'       => 'Judul bisnis wajib dipilih.',
            'target.integer'       => 'Target harus berupa angka.',
            'target.min'           => 'Target minimal 0.',
            'realisasi.integer'    => 'Realisasi harus berupa angka.',
            'realisasi.min'        => 'Realisasi minimal 0.',
            'file_pendukung.mimes' => 'File harus berformat PDF, DOC, DOCX, XLS, XLSX, JPG, JPEG, atau PNG.',
            'file_pendukung.max'   => 'Ukuran file maksimal 5 MB.',
        ]);
    }
}
