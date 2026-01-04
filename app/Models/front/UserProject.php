<?php

namespace App\Models\front;

use Illuminate\Database\Eloquent\Model;

class UserProject extends Model
{
    protected $table = 'user_projects';
    protected $fillable = [
        'id',
        'user_id',
        'title',
        'description',
        'link',
        'image',
        'more_images',
        'tools',
        'category_id',
        'status'
    ];
}
