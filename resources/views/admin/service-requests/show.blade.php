@extends('admin.layouts.master')
@php
    use App\Models\ServiceRequest;
    use App\Models\RequestPayment;
    $r = $serviceRequest;
@endphp
@section('title')
    {{ $r->number }} — {{ $r->service_name }}
@endsection
@section('css')
    <style>
        .rf-answers { display: grid; grid-template-columns: minmax(140px, 35%) 1fr; gap: 6px 16px; margin: 0; }
        .rf-answers dt { font-weight: 600; color: #4a5562; }
        .rf-answers dd { margin: 0; overflow-wrap: anywhere; }
        .rf-answers-section { font-size: 15px; font-weight: 700; margin: 16px 0 8px; color: #1f7a62; }
        .rf-problem { white-space: pre-line; margin: 0; }
        .rf-files { list-style: none; padding: 0; margin: 0; }
        .rf-muted, .rq-muted { color: #6b7785; font-size: 12px; }
        .rq-message { padding: 8px 10px; border-radius: 6px; background: #f5f7fa; margin-bottom: 6px; }
        .rq-message.rq-client { background: #eef7f3; }
        .rq-message p { margin: 2px 0; white-space: pre-line; }
    </style>
@endsection
@section('content')
    <div class="breadcrumb-header justify-content-between">
        <div class="my-auto">
            <div class="d-flex">
                <h4 class="content-title mb-0 my-auto">طلبات الخدمات</h4><span class="text-muted mt-1 tx-13 mr-2 mb-0">/ {{ $r->number }}</span>
            </div>
        </div>
        <div class="my-auto">
            <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('admin.requests.index') }}" class="btn btn-light"><i class="fas fa-arrow-right"></i> رجوع</a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
    @endif

    <div class="row row-sm">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between flex-wrap" style="gap: 8px">
                        <div>
                            <span class="rq-muted">{{ $r->category_name }} ← {{ $r->service_name }} ← {{ $r->number }}</span>
                            <h4 class="mb-1">{{ $r->project_title ?: $r->service_name }}
                                @if ($r->isStaged())<span class="badge badge-warning">مشروع بمراحل</span>@endif
                                <span class="badge badge-light">المصدر: {{ \App\Models\ServiceRequest::SOURCES[$r->source] ?? $r->source }}</span>
                                @if ($r->contract)<span class="badge badge-info">{{ $r->contract->number }} · {{ $r->contract->status === \App\Models\Contract::STATUS_AGREEMENT ? 'الاتفاق قيد الإعداد' : $r->contract->status }}</span>@endif
                            </h4>
                            <span class="rq-muted">
                                أُرسل {{ $r->created_at->format('Y-m-d H:i') }}
                                @if ($r->published_at) · نُشر {{ $r->published_at->format('Y-m-d H:i') }} @endif
                                · المسار: {{ $r->path === ServiceRequest::PATH_EXECUTE ? 'أريد التنفيذ' : 'استكشاف الأسعار' }}
                            </span>
                        </div>
                        <div class="text-left">
                            <span class="badge badge-primary" style="font-size: 14px">{{ $r->statusLabel() }}</span>
                            @if ($r->closed_reason)
                                <div class="rq-muted mt-1">{{ $r->closeReasonLabel() }} · {{ $r->closed_at?->format('Y-m-d') }} · {{ $r->closedBy?->name }}</div>
                            @endif
                        </div>
                    </div>
                    @if ($r->admin_note)
                        <div class="alert alert-light mt-3 mb-0" style="white-space: pre-line">{{ $r->admin_note }}</div>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h6 class="mb-0">العميل وبيانات التواصل</h6></div>
                <div class="card-body">
                    <dl class="rf-answers">
                        <dt>الاسم</dt><dd>{{ $r->contact_name }}</dd>
                        <dt>الهاتف</dt><dd><span dir="ltr">{{ $r->contact_phone }}</span></dd>
                        <dt>البريد</dt><dd><span dir="ltr">{{ $r->contact_email }}</span></dd>
                        @if ($r->contact_method)
                            <dt>يفضّل التواصل عبر</dt><dd>{{ ServiceRequest::CONTACT_METHODS[$r->contact_method] ?? $r->contact_method }}</dd>
                        @endif
                        <dt>الحساب</dt><dd><a href="{{ route('admin.clients.show', $r->user_id) }}">{{ $r->user?->user_name }} · {{ $clientRequests }} طلب · ملف العميل</a></dd>
                    </dl>
                </div>
            </div>

            <div class="card" id="messages">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">رسائل العميل مع الفريق</h6>
                    @php($last = $r->adminMessages->last())
                    @if ($last)
                        <span class="badge {{ $last->from_admin ? 'badge-info' : 'badge-warning' }}">{{ $last->from_admin ? 'بانتظار رد العميل' : 'بانتظار ردنا' }}</span>
                    @endif
                </div>
                <div class="card-body">
                    @forelse ($r->adminMessages as $message)
                        <div class="rq-message {{ $message->from_admin ? '' : 'rq-client' }}">
                            <span class="rq-muted">{{ $message->from_admin ? 'الفريق (' . ($message->sender?->name ?? '') . ')' : 'العميل' }} · {{ $message->created_at->format('Y-m-d H:i') }}</span>
                            <p>{{ $message->body }}</p>
                        </div>
                    @empty
                        <p class="text-muted">لا توجد رسائل بعد. يمكنك سؤال العميل عن أي تفصيل.</p>
                    @endforelse
                    <form method="POST" action="{{ route('admin.requests.messages.store', $r) }}" class="mt-2">
                        @csrf
                        <textarea name="body" rows="3" class="form-control" required maxlength="3000" placeholder="اكتب سؤالك أو ردك للعميل…"></textarea>
                        <button class="btn btn-primary mt-2">إرسال للعميل</button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h6 class="mb-0">تفاصيل الطلب</h6></div>
                <div class="card-body">
                    @include('website.service-requests.partials.request-details', ['serviceRequest' => $r, 'fileRoute' => 'requests.file'])
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h6 class="mb-0">العروض ({{ $r->offers->count() }})</h6></div>
                <div class="card-body">
                    @forelse ($r->offers as $offer)
                        <div class="border rounded p-3 mb-3 {{ $offer->id === $r->accepted_offer_id ? 'border-success' : '' }}">
                            <div class="d-flex justify-content-between flex-wrap">
                                <div>
                                    <strong>{{ $offer->freelancer?->name }}</strong> <span class="rq-muted">{{ $offer->freelancer?->email }}</span><br>
                                    <span>{{ \App\Support\Money::format($offer->price) }} · {{ $offer->duration_days }} يوم</span>
                                </div>
                                <span class="badge badge-light">{{ $offer->statusLabel() }}</span>
                            </div>
                            <p class="rf-problem mt-2 mb-1">{{ $offer->scope }}</p>
                            <p class="rq-muted rf-problem mb-1">{{ $offer->message }}</p>
                            @foreach ($offer->files as $file)
                                <a href="{{ route('admin.requests.offer-file', [$offer, $file]) }}" class="d-block"><i class="fas fa-paperclip"></i> {{ $file->original_name }}</a>
                            @endforeach
                            @if ($offer->messages->isNotEmpty())
                                <details class="mt-2">
                                    <summary>المحادثة ({{ $offer->messages->count() }})</summary>
                                    <div class="mt-2">
                                        @foreach ($offer->messages as $message)
                                            <div class="rq-message {{ (int) $message->sender_id === (int) $r->user_id ? 'rq-client' : '' }}">
                                                <span class="rq-muted">{{ (int) $message->sender_id === (int) $r->user_id ? 'العميل' : 'المستقل' }} · {{ $message->created_at->format('Y-m-d H:i') }}</span>
                                                <p>{{ $message->body }}</p>
                                            </div>
                                        @endforeach
                                    </div>
                                </details>
                            @endif
                        </div>
                    @empty
                        <p class="text-muted mb-0">لا توجد عروض.</p>
                    @endforelse
                </div>
            </div>

            @if ($r->payments->isNotEmpty())
                <div class="card">
                    <div class="card-header"><h6 class="mb-0">المدفوعات والحركات</h6></div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead><tr><th>#</th><th>المستقل</th><th>المبلغ</th><th>العمولة</th><th>للمستقل</th><th>الحالة</th><th></th></tr></thead>
                                <tbody>
                                    @foreach ($r->payments as $payment)
                                        <tr>
                                            <td>{{ $payment->id }}</td>
                                            <td>{{ $payment->freelancer?->name }}</td>
                                            <td>{{ \App\Support\Money::format($payment->amount) }}
                                                @if ($payment->service_request_change_id)<div class="small text-muted">فرق تعديل{{ $payment->gateway === 'balance' ? ' · من الرصيد' : '' }}</div>@endif
                                                @if ((float) $payment->refunded_amount > 0)<div class="small text-danger">أُعيد للرصيد {{ \App\Support\Money::format($payment->refunded_amount) }}</div>@endif
                                            </td>
                                            <td>{{ $payment->commission_amount !== null ? (float) $payment->commission_amount . ' (' . (float) $payment->commission_rate . '%)' : '—' }}</td>
                                            <td>{{ $payment->freelancer_amount !== null ? (float) $payment->freelancer_amount : '—' }}</td>
                                            <td>{{ $payment->statusLabel() }}</td>
                                            <td>
                                                @if ($payment->status === RequestPayment::STATUS_CONFLICT)
                                                    <form method="POST" action="{{ route('admin.requests.refund', $payment) }}" onsubmit="return confirm('هل استرجعت المبلغ للعميل من لوحة EasyKash؟ سيُسجَّل الاسترجاع هنا.')">
                                                        @csrf
                                                        <button class="btn btn-sm btn-danger">تأكيد الاسترجاع</button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if ($ledger->isNotEmpty())
                            <h6 class="mt-3">سجل الحركات</h6>
                            <ul class="mb-0">
                                @foreach ($ledger as $entry)
                                    <li>{{ $entry->created_at->format('Y-m-d H:i') }} · {{ $entry->description }} · <strong>{{ (float) $entry->amount }}</strong> · {{ $entry->user?->name ?? 'الموقع' }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            @endif
            @php($changes = $r->changes()->get())
            @if ($changes->isNotEmpty())
                <div class="card" id="changes">
                    <div class="card-header"><h6 class="mb-0">طلبات التعديل بعد البدء ({{ $changes->count() }}) · السعر الحالي {{ \App\Support\Money::format($r->currentPrice()) }}</h6></div>
                    <div class="card-body">
                        @foreach ($changes as $change)
                            <div class="border rounded p-2 mb-2">
                                <div class="d-flex justify-content-between"><strong>{{ $change->statusLabel() }}</strong><small class="text-muted">{{ $change->created_at->format('Y-m-d H:i') }}</small></div>
                                <div style="white-space: pre-line">{{ $change->description }}</div>
                                <small class="text-muted">السعر {{ \App\Support\Money::format($change->old_price) }} ← {{ \App\Support\Money::format($change->new_price) }}
                                    @if ($change->new_deadline) · الموعد {{ $change->new_deadline->format('Y-m-d') }}@endif
                                    @if ($change->reason) · السبب: {{ $change->reason }}@endif
                                    @if ($change->response_note) · رد المستقل: {{ $change->response_note }}@endif</small>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
            <div class="card" id="history">
                <div class="card-header"><h6 class="mb-0">سجل الطلب</h6></div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        @forelse ($r->events as $event)
                            <li class="border-bottom py-2">
                                <div>{{ $event->description() }}</div>
                                @if ($event->note)
                                    <div class="rq-muted" style="white-space: pre-line">{{ $event->note }}</div>
                                @endif
                                <small class="rq-muted">{{ $event->created_at?->format('Y-m-d H:i') }} · {{ $event->user?->name ?? 'النظام' }}</small>
                            </li>
                        @empty
                            <li class="rq-muted">لا يوجد سجل بعد.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            @if ($r->isOpen())
                <div class="card" id="freelancers">
                    <div class="card-header"><h6 class="mb-0">المستقلون المطابقون للخدمة ({{ $matchingFreelancers->count() }})</h6></div>
                    <div class="card-body">
                        @forelse ($matchingFreelancers as $freelancer)
                            <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                                <div>
                                    <a href="{{ url('user/' . $freelancer->user_name) }}" target="_blank">{{ $freelancer->name }}</a>
                                    @if ($freelancer->has_offered)
                                        <span class="badge badge-success">أرسل عرضًا</span>
                                    @elseif ($freelancer->was_invited)
                                        <span class="badge badge-info">تمت دعوته</span>
                                    @endif
                                </div>
                                @unless ($freelancer->has_offered || $freelancer->was_invited)
                                    <form method="POST" action="{{ route('admin.requests.invite', [$r, $freelancer->id]) }}">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-primary">دعوة لإرسال عرض</button>
                                    </form>
                                @endunless
                            </div>
                        @empty
                            <p class="rq-muted mb-0">لا يوجد مستقلون مسجلون في هذه الخدمة بعد.</p>
                        @endforelse
                    </div>
                </div>
            @endif

            @if ($r->no_offer_alerted_at || $r->isInquiry())
                <div class="card border-warning">
                    <div class="card-header"><h6 class="mb-0"><i class="fas fa-bell text-warning"></i> {{ $r->no_offer_alerted_at ? 'متابعة: لا عروض بعد ساعة' : 'متابعة استفسار السعر' }}</h6></div>
                    <div class="card-body">
                        <p class="rq-muted">
                            @if ($r->no_offer_alerted_at)أُرسل التنبيه {{ $r->no_offer_alerted_at->format('Y-m-d H:i') }} · @endif
                            الحالة: <strong>{{ $r->followupLabel() }}</strong>
                            @if ($r->followup_updated_at) · آخر تحديث {{ $r->followup_updated_at->format('Y-m-d H:i') }}@endif
                        </p>
                        <form method="POST" action="{{ route('admin.requests.followup', $r) }}">
                            @csrf
                            <div class="form-group">
                                <select name="followup_status" class="form-control">
                                    @foreach (ServiceRequest::FOLLOWUP_STATUSES as $key => $label)
                                        <option value="{{ $key }}" @selected(($r->followup_status ?: ServiceRequest::FOLLOWUP_NEW) === $key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <textarea name="followup_note" rows="3" class="form-control" placeholder="{{ $r->isInquiry() && ! $r->no_offer_alerted_at ? 'ملاحظة داخلية: تواصلت مع العميل؟ ماذا قال عن النشر؟' : 'ماذا فعلت؟ (تواصلت مع مستقل، دعوته لإرسال عرض…)' }}">{{ $r->followup_note }}</textarea>
                            </div>
                            <button class="btn btn-primary btn-block">حفظ المتابعة</button>
                        </form>
                    </div>
                </div>
            @endif

            @if ($r->isInquiry())
                <div class="card">
                    <div class="card-header"><h6 class="mb-0">نشر الاستفسار بموافقة العميل</h6></div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.requests.publish', $r) }}">
                            @csrf
                            <div class="custom-control custom-checkbox mb-2">
                                <input type="checkbox" class="custom-control-input" id="client_agreed" name="client_agreed" value="1">
                                <label class="custom-control-label" for="client_agreed">العميل وافق على نشر طلبه للمستقلين</label>
                            </div>
                            <button class="btn btn-success btn-block">نشر الطلب بنفس البيانات</button>
                        </form>
                    </div>
                </div>
            @endif

            @if ($r->isInProgress())
                @if (! $r->isStaged())
                <div class="card">
                    <div class="card-header"><h6 class="mb-0">اعتماد التنفيذ نيابة عن العميل</h6></div>
                    <div class="card-body">
                        <p class="rq-muted">
                            {{ $r->delivered_at ? 'سلّم المستقل العمل ' . $r->delivered_at->format('Y-m-d H:i') . '.' : 'لم يُعلن المستقل التسليم بعد.' }}
                            يُحوّل المبلغ للمستقل ولا يمكن التراجع.
                        </p>
                        <form method="POST" action="{{ route('admin.requests.complete', $r) }}" onsubmit="return confirm('اعتماد التنفيذ وتحويل المستحقات للمستقل؟')">
                            @csrf
                            <div class="form-group">
                                <textarea name="note" rows="2" class="form-control" required placeholder="وثّق سبب الاعتماد (مثال: أكّد العميل هاتفيًا)"></textarea>
                            </div>
                            <button class="btn btn-success btn-block">اعتماد التنفيذ</button>
                        </form>
                    </div>
                </div>
                @endif
            @endif

            @if (! in_array($r->status, [ServiceRequest::STATUS_COMPLETED, ServiceRequest::STATUS_NOT_COMPLETED], true))
                <div class="card">
                    <div class="card-header"><h6 class="mb-0">نقل إلى «لم يتم التنفيذ»</h6></div>
                    <div class="card-body">
                        @if ($r->isInProgress())
                            <p class="rq-muted"><strong>يوجد مبلغ محجوز:</strong> استرجعه للعميل من لوحة EasyKash أولًا، ثم أغلق الطلب ليُسجَّل الاسترجاع.</p>
                        @endif
                        <form method="POST" action="{{ route('admin.requests.close', $r) }}" onsubmit="return confirm(@js('إغلاق الطلب ' . ($r->number) . ' دون تنفيذ؟'))">
                            @csrf
                            <div class="form-group">
                                <select name="reason" class="form-control" required
                                        onchange="var n=this.form.elements.note; n.required=this.value==='other'; n.placeholder=this.value==='other' ? 'اكتب السبب (مطلوب)' : 'ملاحظة توضيحية (اختياري)';">
                                    <option value="">-- سبب الإغلاق --</option>
                                    @foreach (ServiceRequest::CLOSE_REASONS as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <textarea name="note" rows="2" class="form-control" placeholder="ملاحظة توضيحية (اختياري)"></textarea>
                            </div>
                            <button class="btn btn-outline-danger btn-block">إغلاق الطلب</button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
