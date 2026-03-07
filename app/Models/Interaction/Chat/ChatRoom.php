<?php

namespace App\Models\Interaction\Chat;

use App\Models\Auth\TaiKhoan;
use App\Models\Education\LopHoc;
use Illuminate\Database\Eloquent\Model;

class ChatRoom extends Model
{
    public const TYPE_CLASS_GROUP = 'class_group';
    public const TYPE_DIRECT = 'direct';

    public const STATUS_INACTIVE = 0;
    public const STATUS_ACTIVE = 1;
    public const STATUS_ARCHIVED = 2;

    protected $table = 'chat_rooms';
    protected $primaryKey = 'chatRoomId';

    protected $fillable = [
        'loai',
        'tenPhong',
        'lopHocId',
        'matKhauHash',
        'taoBoiId',
        'lastMessageId',
        'trangThai',
    ];

    protected $casts = [
        'lopHocId' => 'integer',
        'taoBoiId' => 'integer',
        'lastMessageId' => 'integer',
        'trangThai' => 'integer',
    ];

    public function lopHoc()
    {
        return $this->belongsTo(LopHoc::class, 'lopHocId', 'lopHocId');
    }

    public function nguoiTao()
    {
        return $this->belongsTo(TaiKhoan::class, 'taoBoiId', 'taiKhoanId');
    }

    public function lastMessage()
    {
        return $this->belongsTo(ChatMessage::class, 'lastMessageId', 'chatMessageId');
    }

    public function members()
    {
        return $this->hasMany(ChatRoomMember::class, 'chatRoomId', 'chatRoomId')
            ->whereNull('roiAt');
    }

    public function allMembers()
    {
        return $this->hasMany(ChatRoomMember::class, 'chatRoomId', 'chatRoomId');
    }

    public function messages()
    {
        return $this->hasMany(ChatMessage::class, 'chatRoomId', 'chatRoomId')
            ->orderBy('chatMessageId');
    }

    public function scopeActive($query)
    {
        return $query->where('trangThai', self::STATUS_ACTIVE);
    }

    public function isClassGroup(): bool
    {
        return $this->loai === self::TYPE_CLASS_GROUP;
    }

    public function isDirect(): bool
    {
        return $this->loai === self::TYPE_DIRECT;
    }
}
