<?php

namespace App\Models\Interaction\Chat;

use App\Models\Auth\TaiKhoan;
use Illuminate\Database\Eloquent\Model;

class ChatMessageReaction extends Model
{
    protected $table = 'chat_message_reactions';
    protected $primaryKey = 'chatReactionId';

    protected $fillable = [
        'chatMessageId',
        'taiKhoanId',
        'emoji',
    ];

    protected $casts = [
        'chatMessageId' => 'integer',
        'taiKhoanId' => 'integer',
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
