<?php

declare(strict_types=1);

namespace App\Models\Tugas;

use App\Enums\TipePengumpulan;
use App\Models\ManajemenUser\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pengumpulan extends Model
{
    use HasFactory;

    protected $table = 'pengumpulan';

    protected $fillable = [
        'tugas_id',
        'tipe',
        'link',
        'teks',
        'dikumpulkan_at',
    ];

    protected function casts(): array
    {
        return [
            'tipe' => TipePengumpulan::class,
            'dikumpulkan_at' => 'datetime',
        ];
    }

    public function tugas(): BelongsTo
    {
        return $this->belongsTo(Tugas::class, 'tugas_id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(PengumpulanFile::class, 'pengumpulan_id');
    }

    public function editLogs(): HasMany
    {
        return $this->hasMany(PengumpulanEditLog::class, 'pengumpulan_id');
    }
}
