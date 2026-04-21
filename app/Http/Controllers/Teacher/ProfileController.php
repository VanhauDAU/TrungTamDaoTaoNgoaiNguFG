<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user()->loadMissing([
            'hoSoNguoiDung',
            'nhanSu.coSoDaoTao',
            'nhanSuHoSo',
        ]);

        return view('teacher.profile.show', compact('user'));
    }
}
