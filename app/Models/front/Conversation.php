<?php

namespace App\Models\front;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function messages()
    {
        return $this->hasMany(Message::class, 'conversation_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function lastMessage()
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}
