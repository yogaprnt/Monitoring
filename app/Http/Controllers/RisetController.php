<?php

namespace App\Http\Controllers;

use App\Models\Riset;
use App\Events\InputDataSubmitted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class RisetController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $riset = Riset::where('input_by', $user->id)->latest()->get();
        return view('staff.createriset', compact('riset', 'user'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateRiset($request);
        $filePendukung = null;

        try {
            if ($request->hasFile('file_pendukung')) {
                $filePendukung = $request->file('file_pendukung')
                    ->store('file-pendukung-riset', 'public');
            }

            DB::transaction(function () use ($validated, $filePendukung) {
                Riset::create([
                    'periode'                     => $validated['triwulan'] . ' ' . $validated['tahun'],
                    'judul'                       => $validated['judul'],
                    'coe'                         => $validated['coe'] ?? null,
                    'target'                      => $validated['target'] ?? 0,
                    'realisasi'                   => $validated['realisasi'] ?? 0,
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
            return back()->withInput()->with('error', 'Riset gagal disimpan. Silakan coba kembali.');
        }

        // ✅ Fire event aktivitas input data
        event(new InputDataSubmitted(auth()->user()));

        return redirect()->route('riset.index')
            ->with('success', 'Riset berhasil disubmit dan menunggu review Asisten Manager.');
    }

    public function edit($id)
    {
        $user = auth()->user();
        $item = Riset::where('input_by', $user->id)->findOrFail($id);

        if (!$item->canBeModifiedByStaff()) {
            return redirect()->route('riset.index')
                ->with('error', 'Data sedang diproses atau sudah final sehingga tidak dapat diedit.');
        }

        $riset = Riset::where('input_by', $user->id)->latest()->get();
        return view('staff.createriset', compact('riset', 'user', 'item'));
    }

    public function update(Request $request, $id)
    {
        $item = Riset::where('input_by', auth()->id())->findOrFail($id);

        if (!$item->canBeModifiedByStaff()) {
            return redirect()->route('riset.index')
                ->with('error', 'Data sedang diproses atau sudah final sehingga tidak dapat diedit.');
        }

        $validated = $this->validateRiset($request);
        $fileLama  = $item->file_pendukung;
        $fileBaru  = null;

        try {
            if ($request->hasFile('file_pendukung')) {
                $fileBaru = $request->file('file_pendukung')
                    ->store('file-pendukung-riset', 'public');
            }

            DB::transaction(function () use ($item, $validated, $fileBaru) {
                $data = [
                    'periode'                     => $validated['triwulan'] . ' ' . $validated['tahun'],
                    'judul'                       => $validated['judul'],
                    'coe'                         => $validated['coe'] ?? null,
                    'target'                      => $validated['target'] ?? 0,
                    'realisasi'                   => $validated['realisasi'] ?? 0,
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
            return back()->withInput()->with('error', 'Riset gagal diperbarui. Silakan coba kembali.');
        }

        // ✅ Fire event aktivitas input data
        event(new InputDataSubmitted(auth()->user()));

        return redirect()->route('riset.index')
            ->with('success', 'Riset berhasil diperbaiki dan dikirim kembali kepada Asisten Manager.');
    }

    public function destroy($id)
    {
        $item = Riset::where('input_by', auth()->id())->findOrFail($id);

        if (!$item->canBeModifiedByStaff()) {
            return redirect()->route('riset.index')
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
            return redirect()->route('riset.index')->with('error', 'Riset gagal dihapus. Silakan coba kembali.');
        }

        return redirect()->route('riset.index')->with('success', 'Riset berhasil dihapus.');
    }

    private function validateRiset(Request $request): array
    {
        $isUpdate = $request->isMethod('put') || $request->isMethod('patch');
        return $request->validate([
            'triwulan'       => ['required', 'in:TW1,TW2,TW3,TW4'],
            'tahun'          => ['required', 'integer', 'min:2020', 'max:2100'],
            'judul'          => ['required', 'string', 'max:255'],
            'coe'            => ['required', 'string', 'max:100'],
            'target'         => ['nullable', 'integer', 'min:0'],
            'realisasi'      => ['required', 'integer', 'min:0'],
            'file_pendukung' => [$isUpdate ? 'nullable' : 'required', 'file', 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png', 'max:5120'],
        ], [
            'triwulan.required'    => 'Triwulan wajib dipilih.',
            'triwulan.in'          => 'Triwulan tidak valid.',
            'tahun.required'       => 'Tahun wajib dipilih.',
            'tahun.integer'        => 'Tahun harus berupa angka.',
            'judul.required'       => 'Judul riset wajib dipilih.',
            'target.integer'       => 'Target harus berupa angka.',
            'target.min'           => 'Target minimal 0.',
            'realisasi.integer'    => 'Realisasi harus berupa angka.',
            'realisasi.min'        => 'Realisasi minimal 0.',
            'file_pendukung.mimes' => 'File harus berformat PDF, DOC, DOCX, XLS, XLSX, JPG, JPEG, atau PNG.',
            'file_pendukung.max'   => 'Ukuran file maksimal 5 MB.',
        ]);
    }
}
