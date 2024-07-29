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
        return $this->hasMany(Message::class,'conversation_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
