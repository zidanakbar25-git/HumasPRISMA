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
        Schema::create('kegiatan', function (Blueprint $table) {
            $table->id();
            $table->string('judul', 150);
            $table->text('deskripsi')->nullable();
            $table->date('tanggal')->index();
            $table->time('waktu_mulai')->nullable(); // null = seharian
            $table->time('waktu_selesai')->nullable();
            $table->string('tempat', 150);
            $table->enum('sumber', ['manual', 'google_calendar'])->default('manual');
            $table->string('google_event_id')->nullable()->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('kegiatan_pic', function (Blueprint $table) {
            $table->foreignId('kegiatan_id')->constrained('kegiatan')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->primary(['kegiatan_id', 'user_id']);
        });

        Schema::create('kegiatan_dilihat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kegiatan_id')->constrained('kegiatan')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->dateTime('dilihat_at');
            $table->unique(['kegiatan_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kegiatan_dilihat');
        Schema::dropIfExists('kegiatan_pic');
        Schema::dropIfExists('kegiatan');
    }
};
