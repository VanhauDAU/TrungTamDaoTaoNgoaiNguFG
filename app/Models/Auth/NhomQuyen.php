<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Model;

class NhomQuyen extends Model
{
    protected $table      = 'nhomQuyen';
    protected $primaryKey = 'nhomQuyenId';

    protected $fillable = [
        'tenNhom',
        'moTa',
    ];

    /** Quyền CRUD của nhóm này */
    public function phanQuyens()
    {
        return $this->hasMany(PhanQuyen::class, 'nhomQuyenId', 'nhomQuyenId');
    }

    /** Các tài khoản thuộc nhóm này */
    public function taiKhoans()
    {
        return $this->hasMany(TaiKhoan::class, 'nhomQuyenId', 'nhomQuyenId');
    }

    /**
     * Trả về map quyền dạng: ['khoa_hoc' => ['xem'=>T, 'them'=>F, ...], ...]
     */
    public function getPermissionsMap(): array
    {
        $map = [];
        foreach ($this->phanQuyens as $pq) {
            $map[$pq->tinhNang] = [
                'xem'  => (bool) $pq->coXem,
                'them' => (bool) $pq->coThem,
                'sua'  => (bool) $pq->coSua,
                'xoa'  => (bool) $pq->coXoa,
            ];
        }
        return $map;
    }
}
