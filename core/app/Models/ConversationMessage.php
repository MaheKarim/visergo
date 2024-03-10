<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConversationMessage extends Model
{
    protected $table = 'conversation_messages';

    protected $guarded = [];

    public function singleConversation()
    {
        return $this->belongsToMany(Conversation::class);
    }
}
