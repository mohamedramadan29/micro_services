<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** An image or background in the admin media library. */
class ContentMedia extends Model
{
    protected $table = 'content_media';

    protected $guarded = [];

    public const KINDS = [
        'image' => 'صورة',
        'background' => 'خلفية',
    ];

    public function url(): string
    {
        return asset($this->path);
    }
}
