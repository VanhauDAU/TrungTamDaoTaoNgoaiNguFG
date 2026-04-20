<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __invoke(Request $request)
    {
        return view('teacher.profile.show', [
            'user' => $request->user()->loadMissing(['nhanSu.coSoDaoTao', 'nhanSuHoSo']),
        ]);
    }
}
