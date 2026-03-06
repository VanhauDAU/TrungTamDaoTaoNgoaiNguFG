<?php

namespace App\Exports;

use App\Models\Auth\TaiKhoan;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class HocViensExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping
{
    use Exportable;

    public function __construct(private readonly Builder $query)
    {
    }

    public function query(): Builder
    {
        return $this->query;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Tai khoan',
            'Ho ten',
            'Email',
            'So dien thoai',
            'Ngay sinh',
            'So lop da dang ky',
            'Dang nhap gan nhat',
            'Trang thai',
            'Ngay tao',
        ];
    }

    public function map($hocVien): array
    {
        /** @var TaiKhoan $hocVien */
        $profile = $hocVien->hoSoNguoiDung;

        return [
            $hocVien->taiKhoanId,
            $hocVien->taiKhoan,
            $profile?->hoTen ?? '',
            $hocVien->email,
            $profile?->soDienThoai ?? '',
            $profile?->ngaySinh ? \Carbon\Carbon::parse($profile->ngaySinh)->format('d/m/Y') : '',
            $hocVien->dang_ky_lop_hocs_count ?? 0,
            $hocVien->lastLogin ? \Carbon\Carbon::parse($hocVien->lastLogin)->format('d/m/Y H:i') : 'Chua dang nhap',
            $hocVien->trangThai ? 'Hoat dong' : 'Bi khoa',
            $hocVien->created_at ? $hocVien->created_at->format('d/m/Y H:i') : '',
        ];
    }
}
