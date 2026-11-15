<?php

declare(strict_types=1);

namespace App\Models\JadwalKegiatan;

use App\Enums\SumberKegiatan;
use App\Models\ManajemenUser\User;
use App\Models\Tugas\Tugas;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kegiatan extends Model
{
    use HasFactory;

    protected $table = 'kegiatan';

    protected $fillable = [
        'judul',
        'deskripsi',
        'tanggal',
        'waktu_mulai',
        'waktu_selesai',
        'tempat',
        'sumber',
        'google_event_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date:Y-m-d',
            'sumber' => SumberKegiatan::class,
        ];
    }

    public function pics(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'kegiatan_pic', 'kegiatan_id', 'user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function views(): HasMany
    {
        return $this->hasMany(KegiatanDilihat::class, 'kegiatan_id');
    }

    public function tugas(): HasMany
    {
        return $this->hasMany(Tugas::class, 'kegiatan_id');
    }

    /**
     * Format tampilan waktu kegiatan (WIB).
     */
    protected function waktuFormatted(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                if (empty($this->waktu_mulai)) {
                    return 'Seharian';
                }

                $mulai = substr((string) $this->waktu_mulai, 0, 5);

                if (! empty($this->waktu_selesai)) {
                    $selesai = substr((string) $this->waktu_selesai, 0, 5);
                    return "{$mulai} - {$selesai} WIB";
                }

                return "{$mulai} WIB";
            }
        );
    }
}
