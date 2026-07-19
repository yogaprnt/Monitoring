<?php

namespace App\Http\Controllers;

use App\Models\Inovasi;
use App\Events\InputDataSubmitted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class InovasiController extends Controller
{
    public function index()
    {
        $inovasi = Inovasi::where('input_by', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('staff.createinovasi', compact('inovasi'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'triwulan'       => 'required|in:TW1,TW2,TW3,TW4',
            'tahun'          => 'required|integer',
            'judul'          => 'required|string',
            'coe'            => 'required|string',
            'target'         => 'nullable|integer|min:0',
            'realisasi'      => 'required|integer|min:0',
            'file_pendukung' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:10240',
        ]);

        $filePath = null;
        if ($request->hasFile('file_pendukung')) {
            $filePath = $request->file('file_pendukung')->store('inovasi', 'public');
        }

        Inovasi::create([
            'input_by'       => Auth::id(),
            'periode'        => $request->triwulan . ' ' . $request->tahun,
            'judul'          => $request->judul,
            'coe'            => $request->coe,
            'target'         => $request->target ?? 0,
            'realisasi'      => $request->realisasi ?? 0,
            'file_pendukung' => $filePath,
            'status'         => 'submitted',
        ]);

        // ✅ Fire event agar aktivitas tercatat
        event(new InputDataSubmitted(Auth::user()));

        return redirect()->route('inovasi.index')
            ->with('success', 'Data inovasi berhasil disimpan dan dikirim untuk direview Asisten manager.');
    }

    public function edit($id)
    {
        $item = Inovasi::where('id', $id)
            ->where('input_by', Auth::id())
            ->firstOrFail();

        if (!$item->canBeModifiedByStaff()) {
            return redirect()->route('inovasi.index')
                ->with('error', 'Data ini tidak dapat diedit.');
        }

        $inovasi = Inovasi::where('input_by', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('staff.createinovasi', compact('item', 'inovasi'));
    }

    public function update(Request $request, $id)
    {
        $item = Inovasi::where('id', $id)
            ->where('input_by', Auth::id())
            ->firstOrFail();

        if (!$item->canBeModifiedByStaff()) {
            return redirect()->route('inovasi.index')
                ->with('error', 'Data ini tidak dapat diedit.');
        }

        $request->validate([
            'triwulan'       => 'required|in:TW1,TW2,TW3,TW4',
            'tahun'          => 'required|integer',
            'judul'          => 'required|string',
            'coe'            => 'required|string',
            'target'         => 'nullable|integer|min:0',
            'realisasi'      => 'required|integer|min:0',
            'file_pendukung' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:10240',
        ]);

        $filePath = $item->file_pendukung;
        if ($request->hasFile('file_pendukung')) {
            if ($filePath) {
                Storage::disk('public')->delete($filePath);
            }
            $filePath = $request->file('file_pendukung')->store('inovasi', 'public');
        }

        $item->update([
            'periode'        => $request->triwulan . ' ' . $request->tahun,
            'judul'          => $request->judul,
            'coe'            => $request->coe,
            'target'         => $request->target ?? 0,
            'realisasi'      => $request->realisasi ?? 0,
            'file_pendukung' => $filePath,
            'status'         => 'submitted',
            'catatan_reject' => null,
        ]);

        // ✅ Fire event saat update/kirim ulang
        event(new InputDataSubmitted(Auth::user()));

        return redirect()->route('inovasi.index')
            ->with('success', 'Data inovasi berhasil diperbarui dan dikirim ulang untuk direview asisten manager.');
    }

    public function destroy($id)
    {
        $item = Inovasi::where('id', $id)
            ->where('input_by', Auth::id())
            ->firstOrFail();

        if (!$item->canBeModifiedByStaff()) {
            return redirect()->route('inovasi.index')
                ->with('error', 'Data ini tidak dapat dihapus.');
        }

        if ($item->file_pendukung) {
            Storage::disk('public')->delete($item->file_pendukung);
        }

        $item->delete();

        return redirect()->route('inovasi.index')
            ->with('success', 'Data inovasi berhasil dihapus.');
    }
}
