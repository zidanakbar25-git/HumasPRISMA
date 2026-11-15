<?php

declare(strict_types=1);

namespace App\Http\Controllers\Staff\JadwalKegiatan;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class JadwalKegiatanController extends Controller
{
    public function index(): View
    {
        return view('Staff.JadwalKegiatan.index');
    }
}
