<?php

declare(strict_types=1);

namespace App\Http\Controllers\Staff\ManajemenAkun;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class ManajemenAkunController extends Controller
{
    public function index(): View
    {
        return view('Staff.ManajemenAkun.index');
    }
}
