<?php

namespace App\Models\Interaction\Chat;

use App\Models\Auth\TaiKhoan;
use Illuminate\Database\Eloquent\Model;

class ChatMessageDelete extends Model
{
    protected $table = 'chat_message_deletes';
    protected $primaryKey = 'chatMessageDeleteId';
    public $timestamps = false;

    protected $fillable = [
        'chatMessageId',
        'taiKhoanId',
        'deletedAt',
        'created_at',
    ];

    protected $casts = [
        'chatMessageId' => 'integer',
        'taiKhoanId' => 'integer',
        'deletedAt' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function message()
    {
        return $this->belongsTo(ChatMessage::class, 'chatMessageId', 'chatMessageId');
    }

    public function taiKhoan()
    {
        return $this->belongsTo(TaiKhoan::class, 'taiKhoanId', 'taiKhoanId');
    }
}
