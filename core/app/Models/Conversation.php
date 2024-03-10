<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
   protected $guarded = [];

   protected $table = 'conversations';

    public function ride()
    {
        return $this->belongsTo(Ride::class);
    }

    public function messages()
    {
        return $this->hasMany(ConversationMessage::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }
}
