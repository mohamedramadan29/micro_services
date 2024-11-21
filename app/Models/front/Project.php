<?php

namespace App\Models\front;

use App\Models\front\ProjectOffer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function files()
    {
        return $this->hasMany(ProjectFiles::class, 'project_id');
    }
    public function User()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function Offers()
    {
        return $this->hasMany(ProjectOffer::class, 'project_id');
    }
}
