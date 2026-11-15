<?php

declare(strict_types=1);

namespace App\Models\JadwalKegiatan;

use App\Models\ManajemenUser\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KegiatanDilihat extends Model
{
    protected $table = 'kegiatan_dilihat';

    public $timestamps = false;

    protected $fillable = [
        'kegiatan_id',
        'user_id',
        'dilihat_at',
    ];

    protected function casts(): array
    {
        return [
            'dilihat_at' => 'datetime',
        ];
    }

    public function kegiatan(): BelongsTo
    {
        return $this->belongsTo(Kegiatan::class, 'kegiatan_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
