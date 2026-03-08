<?php

namespace App\Models\Interaction\Chat;

use App\Models\Auth\TaiKhoan;
use Illuminate\Database\Eloquent\Model;

class ChatRoomMember extends Model
{
    public const ROLE_MEMBER = 'member';
    public const ROLE_TEACHER = 'teacher';
    public const ROLE_OWNER = 'owner';

    protected $table = 'chat_room_members';
    protected $primaryKey = 'chatRoomMemberId';

    protected $fillable = [
        'chatRoomId',
        'taiKhoanId',
        'vaiTro',
        'joinedAt',
        'joinedByPasswordAt',
        'lastReadMessageId',
        'lastSeenAt',
        'isMuted',
        'roiAt',
    ];

    protected $casts = [
        'chatRoomId' => 'integer',
        'taiKhoanId' => 'integer',
        'lastReadMessageId' => 'integer',
        'joinedAt' => 'datetime',
        'joinedByPasswordAt' => 'datetime',
        'lastSeenAt' => 'datetime',
        'roiAt' => 'datetime',
        'isMuted' => 'boolean',
    ];

    public function room()
    {
        return $this->belongsTo(ChatRoom::class, 'chatRoomId', 'chatRoomId');
    }

    public function taiKhoan()
    {
        return $this->belongsTo(TaiKhoan::class, 'taiKhoanId', 'taiKhoanId');
    }

    public function lastReadMessage()
    {
        return $this->belongsTo(ChatMessage::class, 'lastReadMessageId', 'chatMessageId');
    }
}
