<?php

namespace App\Models\front;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class WithDraw extends Model
{
    protected $guarded = [];

    /** How the money is sent to the user (method column). */
    public const METHODS = [
        'bank' => 'تحويل بنكي',
        'wallet' => 'محفظة إلكترونية (فودافون كاش وغيرها)',
        'instapay' => 'إنستاباي',
    ];

    /** status column: 0 pending, 1 sent, 2 rejected (money back to the balance). */
    public const STATUSES = [0 => 'بانتظار التحويل', 1 => 'تم التحويل', 2 => 'مرفوض (أُعيد المبلغ لرصيدك)'];

    public function methodLabel(): string
    {
        return self::METHODS[$this->method] ?? (string) $this->method;
    }

    public function user(){
        return $this->belongsTo(User::class,'user_id');
    }
}
