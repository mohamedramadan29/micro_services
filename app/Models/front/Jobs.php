<?php

namespace App\Models\front;

use App\Models\admin\SubCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Jobs extends Model
{
    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'description',
        'experience',
        'sex',
        'city',
        'country',
        'address',
        'salary',
        'employs_accepted',
        'status',
        'type',
        'end_time'
    ];

    public function category()
    {
        return $this->belongsTo(SubCategory::class, 'category_id');
    }
    public function User()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function Employe()
    {
        return $this->belongsTo(User::class, 'employs_accepted');
    }

    public function offers()
    {
        return $this->hasMany(JobOffer::class, 'job_id');
    }

}
