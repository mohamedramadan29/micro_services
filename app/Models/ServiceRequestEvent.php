<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/** One line of a request's history: a status change or an admin action. */
class ServiceRequestEvent extends Model
{
    public const TYPE_CREATED = 'created';
    public const TYPE_STATUS = 'status';
    public const TYPE_FOLLOWUP = 'followup';
    public const TYPE_INVITED = 'invited';
    public const TYPE_NOTE = 'note';
    public const TYPE_EDITED = 'edited';   // the client edited the request before choosing an offer
    public const TYPE_CHANGE = 'change';   // a change request after work started (note = what happened)
    public const TYPE_AGREEMENT = 'agreement'; // service 3: agreement proposed / changes asked / approved

    public const UPDATED_AT = null;

    protected $guarded = [];

    protected $casts = ['created_at' => 'datetime'];

    /** Record an event done by the logged-in user (or the system when nobody is). */
    public static function record(ServiceRequest $request, string $type, array $data = []): self
    {
        return self::create($data + [
            'service_request_id' => $request->id,
            'user_id' => Auth::id(),
            'type' => $type,
        ]);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function freelancer()
    {
        return $this->belongsTo(User::class, 'freelancer_id');
    }

    public function description(): string
    {
        $status = fn ($key) => ServiceRequest::STATUSES[$key] ?? $key;

        return match ($this->type) {
            self::TYPE_CREATED => 'أُرسل الطلب بحالة «' . $status($this->to_status) . '»',
            self::TYPE_STATUS => 'تغيّرت الحالة من «' . $status($this->from_status) . '» إلى «' . $status($this->to_status) . '»',
            self::TYPE_FOLLOWUP => 'تحديث متابعة «لا عروض بعد ساعة»: ' . (ServiceRequest::FOLLOWUP_STATUSES[$this->to_status] ?? $this->to_status),
            self::TYPE_INVITED => 'دعوة المستقل ' . ($this->freelancer?->name ?? '—') . ' لإرسال عرض',
            self::TYPE_EDITED => 'عدّل العميل الطلب قبل اختيار عرض',
            self::TYPE_CHANGE => 'طلب تعديل بعد البدء',
            self::TYPE_AGREEMENT => 'الاتفاق والمراحل',
            default => 'ملاحظة',
        };
    }
}
