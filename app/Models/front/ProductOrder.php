<?php

namespace App\Models\front;

use App\Models\admin\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductOrder extends Model
{
    use HasFactory;
    protected $guarded = [];


    public function product(){
        return $this->belongsTo(Product::class,"product_id");
    }

}
