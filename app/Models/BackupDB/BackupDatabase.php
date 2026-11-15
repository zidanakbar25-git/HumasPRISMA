<?php

declare(strict_types=1);

namespace App\Models\BackupDB;

use App\Models\ManajemenUser\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BackupDatabase extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $table = 'backup_database';

    protected $fillable = [
        'nama_file',
        'ukuran',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'ukuran' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Format tampilan tanggal backup (WIB).
     */
    protected function tanggalFormatted(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                if (! $this->created_at) {
                    return '-';
                }

                return Carbon::parse($this->created_at)
                    ->setTimezone('Asia/Jakarta')
                    ->translatedFormat('d F Y, H:i:s') . ' WIB';
            }
        );
    }

    /**
     * Format tampilan ukuran file.
     */
    protected function ukuranFormatted(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                $bytes = (int) $this->ukuran;

                if ($bytes < 1024) {
                    return $bytes . ' B';
                }

                if ($bytes < 1048576) {
                    return round($bytes / 1024, 1) . ' KB';
                }

                return round($bytes / 1048576, 2) . ' MB';
            }
        );
    }
}
