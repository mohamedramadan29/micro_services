<?php

namespace App\Models\admin;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TicketMessage extends Model
{
    use HasFactory;


    protected $fillable = [ 'user_id','ticket_id','content','file','sender_type' ] ;


    public function user(){
        return $this->belongsTo(User::class);
    }
}
