<?php

declare(strict_types=1);

namespace App\Models\Tugas;

use App\Models\ManajemenUser\User;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RevisiCatatan extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'revisi_catatan';

    protected $fillable = [
        'tugas_id',
        'dari_user_id',
        'ke_user_id',
        'catatan',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function tugas(): BelongsTo
    {
        return $this->belongsTo(Tugas::class, 'tugas_id');
    }

    public function dariUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dari_user_id');
    }

    public function keUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ke_user_id');
    }

    /**
     * Dapatkan daftar poin revisi sebagai array baris.
     *
     * @return Attribute<array<int, string>, never>
     */
    protected function poinList(): Attribute
    {
        return Attribute::make(
            get: function (): array {
                if (empty($this->catatan)) {
                    return [];
                }

                $lines = preg_split('/\r\n|\r|\n/', (string) $this->catatan);
                if ($lines === false) {
                    return [];
                }

                return array_values(array_filter(array_map('trim', $lines), fn ($line) => $line !== ''));
            }
        );
    }
}
