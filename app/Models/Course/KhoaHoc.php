<?php

namespace App\Models\Course;

use Illuminate\Database\Eloquent\Model;

class KhoaHoc extends Model
{
    //
    protected $table = 'khoahoc'; // tên bảng trong database
    protected $primaryKey = 'khoaHocId'; // khóa chính của bảng
}
