<?php

declare(strict_types=1);

namespace App\Http\Controllers\Kabag\ManajemenUser;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class ManajemenUserController extends Controller
{
    public function index(): View
    {
        return view('Kabag.ManajemenUser.index');
    }
}
