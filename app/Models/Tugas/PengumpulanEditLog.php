<?php

declare(strict_types=1);

namespace App\Models\Tugas;

use App\Models\ManajemenUser\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengumpulanEditLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'pengumpulan_edit_log';

    protected $fillable = [
        'pengumpulan_id',
        'user_id',
        'aksi',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function pengumpulan(): BelongsTo
    {
        return $this->belongsTo(Pengumpulan::class, 'pengumpulan_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
