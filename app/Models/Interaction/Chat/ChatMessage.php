<?php

namespace App\Models\Interaction\Chat;

use App\Models\Auth\TaiKhoan;
use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    public const TYPE_TEXT = 'text';
    public const TYPE_IMAGE = 'image';
    public const TYPE_FILE = 'file';
    public const TYPE_LOCATION = 'location';
    public const TYPE_SYSTEM = 'system';

    protected $table = 'chat_messages';
    protected $primaryKey = 'chatMessageId';

    protected $fillable = [
        'chatRoomId',
        'nguoiGuiId',
        'replyToMessageId',
        'loai',
        'noiDung',
        'metaJson',
        'guiLuc',
        'deadlineThuHoi',
        'thuHoiLuc',
        'xoaLuc',
    ];

    protected $casts = [
        'chatRoomId' => 'integer',
        'nguoiGuiId' => 'integer',
        'replyToMessageId' => 'integer',
        'metaJson' => 'array',
        'guiLuc' => 'datetime',
        'deadlineThuHoi' => 'datetime',
        'thuHoiLuc' => 'datetime',
        'xoaLuc' => 'datetime',
    ];

    public function room()
    {
        return $this->belongsTo(ChatRoom::class, 'chatRoomId', 'chatRoomId');
    }

    public function nguoiGui()
    {
        return $this->belongsTo(TaiKhoan::class, 'nguoiGuiId', 'taiKhoanId');
    }

    public function replyTo()
    {
        return $this->belongsTo(self::class, 'replyToMessageId', 'chatMessageId');
    }

    public function attachments()
    {
        return $this->hasMany(ChatMessageAttachment::class, 'chatMessageId', 'chatMessageId');
    }

    public function reactions()
    {
        return $this->hasMany(ChatMessageReaction::class, 'chatMessageId', 'chatMessageId');
    }

    public function deletes()
    {
        return $this->hasMany(ChatMessageDelete::class, 'chatMessageId', 'chatMessageId');
    }
}
