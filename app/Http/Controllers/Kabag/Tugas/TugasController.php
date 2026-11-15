<?php

declare(strict_types=1);

namespace App\Http\Controllers\Kabag\Tugas;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class TugasController extends Controller
{
    public function index(): View
    {
        return view('Kabag.Tugas.index');
    }
}
