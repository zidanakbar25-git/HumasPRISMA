<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tugas', function (Blueprint $table) {
            $table->id();
            $table->string('judul', 150);
            $table->dateTime('deadline');
            $table->enum('prioritas', ['rendah', 'sedang', 'tinggi']);
            $table->foreignId('pic_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('kegiatan_id')->nullable()->constrained('kegiatan')->nullOnDelete();
            $table->enum('status', ['belum_dikerjakan', 'sedang_dikerjakan', 'selesai'])->default('belum_dikerjakan');
            $table->enum('tahap', ['belum_dikumpulkan', 'menunggu_staff', 'menunggu_kabag', 'revisi', 'diterima'])->default('belum_dikumpulkan')->index();
            $table->foreignId('pemegang_id')->nullable()->constrained('users')->nullOnDelete()->index();
            $table->dateTime('dilihat_pemegang_at')->nullable();
            $table->foreignId('staff_penerima_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('selesai_at')->nullable();
            $table->dateTime('diterima_at')->nullable();
            $table->timestamps();

            $table->index(['pic_id', 'tahap']);
            $table->index(['pemegang_id', 'tahap']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tugas');
    }
};
