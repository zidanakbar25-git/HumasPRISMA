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
        Schema::create('pengumpulan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tugas_id')->unique()->constrained('tugas')->cascadeOnDelete();
            $table->enum('tipe', ['file', 'link', 'teks']);
            $table->string('link', 500)->nullable();
            $table->longText('teks')->nullable();
            $table->dateTime('dikumpulkan_at');
            $table->timestamps();
        });

        Schema::create('pengumpulan_file', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengumpulan_id')->constrained('pengumpulan')->cascadeOnDelete();
            $table->string('path');
            $table->string('nama_asli');
            $table->string('mime');
            $table->unsignedBigInteger('ukuran');
            $table->timestamps();
        });

        Schema::create('pengumpulan_edit_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengumpulan_id')->constrained('pengumpulan')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('aksi', ['tambah_file', 'hapus_file', 'edit_link', 'edit_teks']);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengumpulan_edit_log');
        Schema::dropIfExists('pengumpulan_file');
        Schema::dropIfExists('pengumpulan');
    }
};
