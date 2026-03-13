<?php

namespace App\Contracts\Client;

use App\Models\Auth\TaiKhoan;
use Illuminate\Http\Request;

interface StudentServiceInterface
{
    public function updateProfile(Request $request, TaiKhoan $user): void;

    public function updateAvatar(Request $request, TaiKhoan $user): void;

    public function updatePassword(Request $request, TaiKhoan $user): void;

    public function getInvoices(TaiKhoan $user): array;

    public function getInvoiceDetail(TaiKhoan $user, int $id): array;

    public function getMyClasses(TaiKhoan $user): array;

    public function getSchedule(Request $request, TaiKhoan $user): array;
}
