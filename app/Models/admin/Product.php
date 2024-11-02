<?php

namespace App\Models\admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $guarded = [];

    // علاقة مع الجاليري
    public function gallary()
    {
        return $this->hasMany(productGallary::class, 'product_id');
    }
}
