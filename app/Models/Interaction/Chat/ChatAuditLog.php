<?php

namespace App\Models\Interaction\Chat;

use App\Models\Auth\TaiKhoan;
use Illuminate\Database\Eloquent\Model;

class ChatAuditLog extends Model
{
    protected $table = 'chat_audit_logs';
    protected $primaryKey = 'chatAuditLogId';
    public $timestamps = false;

    protected $fillable = [
        'chatRoomId',
        'chatMessageId',
        'taiKhoanId',
        'hanhDong',
        'duLieuCu',
        'duLieuMoi',
        'created_at',
    ];

    protected $casts = [
        'chatRoomId' => 'integer',
        'chatMessageId' => 'integer',
        'taiKhoanId' => 'integer',
        'duLieuCu' => 'array',
        'duLieuMoi' => 'array',
        'created_at' => 'datetime',
    ];

    public function room()
    {
        return $this->belongsTo(ChatRoom::class, 'chatRoomId', 'chatRoomId');
    }

    public function message()
    {
        return $this->belongsTo(ChatMessage::class, 'chatMessageId', 'chatMessageId');
    }

    public function taiKhoan()
    {
        return $this->belongsTo(TaiKhoan::class, 'taiKhoanId', 'taiKhoanId');
    }
}
