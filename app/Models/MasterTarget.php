<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MasterTarget extends Model
{
    protected $table = 'master_targets';

    protected $fillable = [
        'periode',
        'kategori',
        'judul',
        'target',
        'keterangan',
        'file_pendukung',
        'input_by',
    ];

    protected $casts = [
        'target' => 'integer',
    ];

    /**
     * Daftar judul/indikator per kategori (sama dengan form kinerja staff).
     */
    public static function judulOptions(): array
    {
        return [
            'Riset' => [
                'Pembicara undangan (invited/ keynote speaker) pada konferensi internasional',
                'Kunjungan lembaga internasional ke CoE',
                'Makalah Konferensi internasional',
                'Publikasi ilmiah dalam jurnal nasional S1 s.d. S4',
                'Publikasi jurnal internasional bereputasi selain Q1/ Q2',
                'Publikasi jurnal internasional bereputasi setara Q1/ Q2',
                'HKI',
                'Paten (minimal submit)',
                'Kontrak riset pada tingkat nasional',
                'Kontrak riset pada tingkat internasional',
            ],
            'Bisnis' => [
                'Kontrak bisnis untuk komersialisasi',
                'Keterlibatan dalam unit bisnis (LSP, start-up, dll.) yang melayani jasa sesuai kompetensi PUI-PT',
                'Pembinaan UMKM/ komunitas',
            ],
            'Akademik' => [
                'Bimbingan doktor dengan topik dari riset CoE',
                'Bimbingan magister dengan topik dari riset CoE',
                'Kapasitas magang mahasiswa',
                'Kegiatan riset tugas akhir D3/S1/S2',
                'Ide & inovasi untuk kompetisi mahasiswa',
                'Buku (buku ajar, monograf, referensi, dll.)',
            ],
            'Pengabdian' => [
                'Pengelolaan dan peningkatan/internasionalisasi seminar/konferensi internasional',
                'Kontrak non-riset (pelatihan, jasa konsultansi, industri, komunitas, pemerintah, dll.)',
                'Community services (kolaborasi, CSR, dll.)',
                'Proposal abdimas DRTPM',
                'Proposal abdimas yang berkaitan dengan SDGs',
                'Pengelolaan dan peningkatan/internasionalisasi akreditasi jurnal ilmiah',
            ],
            'Inovasi' => [
                'Produk berbasis sumber daya dalam negeri',
                'Produk yang dilisensikan',
            ],
        ];
    }

    public static function kategoriList(): array
    {
        return ['Riset', 'Bisnis', 'Akademik', 'Pengabdian', 'Inovasi'];
    }

    public function penginput(): BelongsTo
    {
        return $this->belongsTo(User::class, 'input_by')->withTrashed();
    }
}
