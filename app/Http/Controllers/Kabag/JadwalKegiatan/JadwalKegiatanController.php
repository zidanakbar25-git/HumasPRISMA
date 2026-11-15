<?php

declare(strict_types=1);

namespace App\Http\Controllers\Kabag\JadwalKegiatan;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class JadwalKegiatanController extends Controller
{
    public function index(): View
    {
        return view('Kabag.JadwalKegiatan.index');
    }
}
