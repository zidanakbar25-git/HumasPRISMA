<?php

declare(strict_types=1);

namespace App\Models\Tugas;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengumpulanFile extends Model
{
    use HasFactory;

    protected $table = 'pengumpulan_file';

    protected $fillable = [
        'pengumpulan_id',
        'path',
        'nama_asli',
        'mime',
        'ukuran',
    ];

    public function pengumpulan(): BelongsTo
    {
        return $this->belongsTo(Pengumpulan::class, 'pengumpulan_id');
    }

    public function getUkuranFormattedAttribute(): string
    {
        $bytes = (int) $this->ukuran;
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 1) . ' KB';
        }
        return $bytes . ' B';
    }
}
