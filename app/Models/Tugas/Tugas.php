<?php

declare(strict_types=1);

namespace App\Models\Tugas;

use App\Enums\Prioritas;
use App\Enums\StatusTugas;
use App\Enums\TahapTugas;
use App\Models\JadwalKegiatan\Kegiatan;
use App\Models\ManajemenUser\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Tugas extends Model
{
    use HasFactory;

    protected $table = 'tugas';

    protected $fillable = [
        'judul',
        'deadline',
        'prioritas',
        'pic_id',
        'kegiatan_id',
        'status',
        'tahap',
        'pemegang_id',
        'dilihat_pemegang_at',
        'staff_penerima_id',
        'created_by',
        'selesai_at',
        'diterima_at',
    ];

    protected function casts(): array
    {
        return [
            'deadline' => 'datetime',
            'prioritas' => Prioritas::class,
            'status' => StatusTugas::class,
            'tahap' => TahapTugas::class,
            'dilihat_pemegang_at' => 'datetime',
            'selesai_at' => 'datetime',
            'diterima_at' => 'datetime',
        ];
    }

    public function pic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pic_id');
    }

    public function pemegang(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pemegang_id');
    }

    public function staffPenerima(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_penerima_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function kegiatan(): BelongsTo
    {
        return $this->belongsTo(Kegiatan::class, 'kegiatan_id');
    }

    public function pengumpulan(): HasOne
    {
        return $this->hasOne(Pengumpulan::class, 'tugas_id');
    }

    public function revisiCatatan(): HasMany
    {
        return $this->hasMany(RevisiCatatan::class, 'tugas_id')->orderBy('created_at', 'desc');
    }

    public function revisiTerbaru(): HasOne
    {
        return $this->hasOne(RevisiCatatan::class, 'tugas_id')->latestOfMany('created_at');
    }

    /**
     * Tampilan deadline terformat (WIB).
     */
    protected function deadlineFormatted(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                if (! $this->deadline) {
                    return '-';
                }

                return Carbon::parse($this->deadline)->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') . ' WIB';
            }
        );
    }

    /**
     * Apakah tugas ini berstatus "Baru" bagi user tertentu (belum dilihat oleh pemegang).
     */
    public function isBaruUntuk(int|User $user): bool
    {
        $userId = $user instanceof User ? $user->id : $user;

        return $this->pemegang_id === $userId && $this->dilihat_pemegang_at === null;
    }
}
