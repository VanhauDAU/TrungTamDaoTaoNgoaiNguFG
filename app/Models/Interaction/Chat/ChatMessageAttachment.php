<?php

namespace App\Models\Interaction\Chat;

use Illuminate\Database\Eloquent\Model;

class ChatMessageAttachment extends Model
{
    protected $table = 'chat_message_attachments';
    protected $primaryKey = 'chatAttachmentId';

    protected $fillable = [
        'chatMessageId',
        'disk',
        'path',
        'thumbnailPath',
        'tenGoc',
        'mime',
        'size',
        'width',
        'height',
    ];

    protected $casts = [
        'chatMessageId' => 'integer',
        'size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
    ];

    public function message()
    {
        return $this->belongsTo(ChatMessage::class, 'chatMessageId', 'chatMessageId');
    }
}
