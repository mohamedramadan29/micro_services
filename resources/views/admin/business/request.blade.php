@extends('admin.layouts.master')
@php use App\Models\BusinessRequest as R; use App\Http\Controllers\admin\BusinessAdminController as A; @endphp
@section('title')
    {{ $item->number }} — طلبات الشركات
@endsection
@section('content')
    <div class="breadcrumb-header justify-content-between">
        <div class="my-auto"><div class="d-flex">
            <h4 class="content-title mb-0 my-auto">طلبات الشركات</h4>
            <span class="text-muted mt-1 tx-13 mr-2 mb-0">/ <span dir="ltr">{{ $item->number }}</span> · {{ $item->sourceLabel() }} · {{ $item->statusLabel() }}</span>
        </div></div>
        <div class="my-auto"><a href="{{ route('admin.business.requests') }}" class="btn btn-light"><i class="fas fa-arrow-right"></i> رجوع</a></div>
    </div>
    @include('admin.catalog.partials.flash')

    <div class="row row-sm">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><h6 class="mb-0">{{ $item->displayName() }}</h6></div>
                <div class="card-body">
                    <dl class="row mb-3">
                        <dt class="col-sm-3">الهاتف</dt><dd class="col-sm-9"><span dir="ltr">{{ $item->phone }}</span>
                            <a class="btn btn-sm btn-success mr-2" target="_blank" rel="noopener" href="{{ $item->whatsappUrl('مرحبًا، معك فريق نفّذها للأعمال بخصوص طلبك ' . $item->number) }}"><i class="fab fa-whatsapp"></i> واتساب</a></dd>
                        <dt class="col-sm-3">البريد</dt><dd class="col-sm-9" dir="ltr">{{ $item->email ?: '—' }}</dd>
                        <dt class="col-sm-3">الشركة</dt><dd class="col-sm-9">{{ $item->company_name ?: '—' }} @if ($item->company_size)<span class="badge badge-light">{{ $item->sizeLabel() }}</span>@endif</dd>
                        <dt class="col-sm-3">الحساب</dt><dd class="col-sm-9">@if ($item->user)<a href="{{ route('admin.clients.show', $item->user) }}">{{ $item->user->name }}</a>@else زائر بلا حساب @endif</dd>
                        <dt class="col-sm-3">التاريخ</dt><dd class="col-sm-9">{{ $item->created_at->format('Y-m-d H:i') }} · الموافقة على التواصل {{ $item->consent_at?->format('Y-m-d H:i') }}</dd>
                    </dl>
                    @include('website.business.partials.answers', ['item' => $item])
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h6 class="mb-0">عرض السعر والدفع</h6></div>
                <div class="card-body">
                    @if ($item->hasQuote())
                        <p class="mb-1"><strong>{{ \App\Support\Money::format($item->quote_amount) }}</strong> — أُرسل {{ $item->quote_sent_at->format('Y-m-d') }}@if ($item->quote_due_at) · صالح حتى {{ $item->quote_due_at->format('Y-m-d') }}@endif</p>
                        <div class="text-muted mb-2" style="white-space: pre-line">{{ $item->quote_scope }}</div>
                        @if ($item->quote_decided_at)<p class="mb-2">القرار: <strong>{{ in_array($item->status, ['accepted', 'converted'], true) ? 'مقبول' : 'مرفوض' }}</strong> في {{ $item->quote_decided_at->format('Y-m-d') }}@if ($item->decision_note) — {{ $item->decision_note }}@endif</p>@endif
                    @endif

                    @if ($item->status !== R::STATUS_CONVERTED)
                        <details @if (! $item->hasQuote()) open @endif class="mb-3">
                            <summary class="font-weight-bold">{{ $item->hasQuote() ? 'تعديل / إرسال عرض جديد' : 'إرسال عرض السعر' }}</summary>
                            <form method="POST" action="{{ route('admin.business.requests.quote', $item) }}" class="mt-2">
                                @csrf
                                <div class="form-row">
                                    <div class="col-md-4 form-group"><label for="q-amount">المبلغ (ج.م)</label>
                                        <input id="q-amount" type="number" name="quote_amount" min="1" step="0.01" class="form-control" value="{{ old('quote_amount', $item->quote_amount) }}" required></div>
                                    <div class="col-md-4 form-group"><label for="q-due">صالح حتى (اختياري)</label>
                                        <input id="q-due" type="date" name="quote_due_at" class="form-control" value="{{ old('quote_due_at', $item->quote_due_at?->format('Y-m-d')) }}"></div>
                                </div>
                                <div class="form-group"><label for="q-scope">نطاق العمل والمخرجات والمواعيد</label>
                                    <textarea id="q-scope" name="quote_scope" rows="4" class="form-control" maxlength="5000" required>{{ old('quote_scope', $item->quote_scope) }}</textarea></div>
                                <button class="btn btn-primary">{{ $item->user_id ? 'إرسال العرض للعميل' : 'تسجيل العرض' }}</button>
                                @unless ($item->user_id)<small class="text-muted d-block mt-1">العميل زائر بلا حساب: أبلغه العرض بالهاتف، وسجّل قراره بالأسفل، أو اربطه بحساب ليعتمده بنفسه.</small>@endunless
                            </form>
                        </details>
                    @endif

                    @if ($item->awaitsClientDecision())
                        <form method="POST" action="{{ route('admin.business.requests.decision', $item) }}" class="mb-3">
                            @csrf
                            <label class="font-weight-bold d-block">تسجيل قرار العميل (بعد التواصل معه)</label>
                            <input type="text" name="note" class="form-control mb-2" maxlength="1000" placeholder="ملاحظة (اختياري)">
                            <button name="decision" value="accept" class="btn btn-success btn-sm">قبل العرض</button>
                            <button name="decision" value="reject" class="btn btn-outline-secondary btn-sm">رفض العرض</button>
                        </form>
                    @endif

                    @if ($item->hasQuote())
                        @if ($item->isPaid())
                            <p class="mb-0"><span class="badge badge-success">مدفوع</span> {{ $item->paymentMethodLabel() }} @if ($item->payment_reference)· <span dir="ltr">{{ $item->payment_reference }}</span>@endif · {{ $item->paid_at?->format('Y-m-d H:i') }}</p>
                        @else
                            <form method="POST" action="{{ route('admin.business.requests.payment', $item) }}" class="form-inline" style="gap: 8px">
                                @csrf
                                <label class="font-weight-bold">تسجيل دفع يدوي:</label>
                                <select name="payment_method" class="form-control" required aria-label="طريقة الدفع">
                                    @foreach (R::PAYMENT_METHODS as $key => $label)@continue($key === 'online')<option value="{{ $key }}">{{ $label }}</option>@endforeach
                                </select>
                                <input type="text" name="payment_reference" class="form-control" maxlength="120" placeholder="رقم الإيصال / المرجع">
                                <button class="btn btn-outline-primary" onclick="return confirm('تسجيل أن العميل دفع {{ \App\Support\Money::format($item->quote_amount) }}؟')">تسجيل الدفع</button>
                            </form>
                            <small class="text-muted d-block mt-1">أو يدفع العميل إلكترونيًا من حسابه بعد قبول العرض.</small>
                        @endif
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h6 class="mb-0">ملاحظات الفريق وسجل الطلب</h6></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.business.requests.note', $item) }}" class="mb-3">
                        @csrf
                        <textarea name="body" rows="2" class="form-control" required maxlength="3000" placeholder="ماذا تم؟ (اتصلت، طلبت تفاصيل…) — داخلية لا يراها العميل" aria-label="ملاحظة داخلية"></textarea>
                        <button class="btn btn-primary btn-sm mt-2">إضافة ملاحظة</button>
                    </form>
                    <ul class="list-unstyled mb-0">
                        @foreach ($item->events as $event)
                            <li class="border-bottom py-2">
                                <strong>{{ A::actionLabel($event->action) }}</strong>@if ($event->internal) <span class="badge badge-light">داخلية</span>@endif
                                @if ($event->details)<div style="white-space: pre-line">{{ $event->details }}</div>@endif
                                <small class="text-muted">{{ $event->created_at?->format('Y-m-d H:i') }} · {{ $event->user?->name ?? ($event->action === 'created' ? $item->displayName() : 'النظام') }}</small>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            @if ($item->project)
                <div class="card border-success"><div class="card-body">
                    <p class="mb-2">تحوّل الطلب إلى مشروع:</p>
                    <a href="{{ route('admin.business.projects.show', $item->project) }}" class="btn btn-success btn-block">{{ $item->project->number }} — {{ $item->project->title }}</a>
                </div></div>
            @else
                <div class="card">
                    <div class="card-header"><h6 class="mb-0">المتابعة</h6></div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.business.requests.update', $item) }}">
                            @csrf
                            <div class="form-group"><label for="r-status">الحالة</label>
                                <select id="r-status" name="status" class="form-control">
                                    @foreach (R::STATUSES as $key => $label)
                                        @continue(! in_array($key, R::MANUAL_STATUSES, true) && $key !== $item->status)
                                        <option value="{{ $key }}" @selected($item->status === $key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">«أُرسل عرض السعر» و«مقبول» و«محوّل لمشروع» تتغير من إجراءاتها.</small></div>
                            <div class="form-group"><label for="r-note">سبب / ملاحظة على الحالة (اختياري)</label>
                                <input id="r-note" type="text" name="status_note" class="form-control" maxlength="1000"></div>
                            <div class="form-group"><label for="r-assignee">المسؤول</label>
                                <select id="r-assignee" name="assigned_to" class="form-control"><option value="">— بدون —</option>
                                    @foreach ($staff as $member)<option value="{{ $member->id }}" @selected($item->assigned_to === $member->id)>{{ $member->name }}</option>@endforeach
                                </select></div>
                            <button class="btn btn-primary btn-block">حفظ</button>
                        </form>
                    </div>
                </div>

                @unless ($item->user_id)
                    <div class="card">
                        <div class="card-header"><h6 class="mb-0">حساب العميل</h6></div>
                        <div class="card-body">
                            <p class="text-muted small">أرسل الطلب زائر بلا حساب. اربطه بحساب عميل ليتابع العرض والمشروع ويعتمد النسخ بنفسه؛ إن لم يكن للبريد حساب يُنشأ حساب ويُرسل له رابط ضبط كلمة المرور.</p>
                            <form method="POST" action="{{ route('admin.business.requests.account', $item) }}">
                                @csrf
                                <input type="email" name="email" class="form-control mb-2" dir="ltr" required maxlength="150" value="{{ old('email', $item->email) }}" placeholder="بريد العميل" aria-label="بريد العميل">
                                <input type="text" name="name" class="form-control mb-2" maxlength="100" value="{{ old('name', $item->contact_name) }}" placeholder="اسم العميل (لحساب جديد)" aria-label="اسم العميل">
                                <button class="btn btn-outline-primary btn-block">ربط / إنشاء الحساب</button>
                            </form>
                        </div>
                    </div>
                @endunless

                <div class="card">
                    <div class="card-header"><h6 class="mb-0">تحويل إلى مشروع</h6></div>
                    <div class="card-body">
                        @if ($item->status === R::STATUS_ACCEPTED && $item->user_id)
                            <form method="POST" action="{{ route('admin.business.requests.convert', $item) }}">
                                @csrf
                                <div class="form-group"><label for="c-title">عنوان المشروع</label>
                                    <input id="c-title" type="text" name="title" class="form-control" maxlength="150" required value="{{ old('title', $item->company_name ? 'مشروع ' . $item->company_name : '') }}"></div>
                                <div class="form-group"><label for="c-manager">مدير المشروع</label>
                                    <select id="c-manager" name="manager_id" class="form-control"><option value="">— بدون —</option>
                                        @foreach ($staff as $member)<option value="{{ $member->id }}" @selected(($item->assigned_to ?? auth()->id()) === $member->id)>{{ $member->name }}</option>@endforeach</select></div>
                                <div class="form-group"><label for="c-due">موعد التسليم (اختياري)</label><input id="c-due" type="date" name="due_at" class="form-control"></div>
                                <button class="btn btn-success btn-block">تحويل إلى مشروع</button>
                            </form>
                        @else
                            <p class="text-muted small mb-0">يتحوّل الطلب لمشروع بعد <strong>قبول عرض السعر</strong>@unless ($item->user_id) و<strong>ربطه بحساب العميل</strong>@endunless. الطلب الأصلي يبقى بسجله.</p>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
