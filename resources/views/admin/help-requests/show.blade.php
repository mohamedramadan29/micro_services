@extends('admin.layouts.master')
@php use App\Models\HelpRequest; @endphp
@section('title')
    {{ $help->number }} — طلبات المساعدة
@endsection
@section('content')
    <div class="breadcrumb-header justify-content-between">
        <div class="my-auto">
            <div class="d-flex">
                <h4 class="content-title mb-0 my-auto">طلبات المساعدة</h4>
                <span class="text-muted mt-1 tx-13 mr-2 mb-0">/ تفاصيل الطلب <span dir="ltr">{{ $help->number }}</span></span>
            </div>
        </div>
        <div class="my-auto">
            <a href="{{ route('admin.help-requests.index') }}" class="btn btn-light"><i class="fas fa-arrow-right"></i> رجوع</a>
        </div>
    </div>
    @include('admin.catalog.partials.flash')

    <div class="row row-sm">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><h6 class="mb-0">تفاصيل الطلب</h6></div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-3">الخدمة</dt><dd class="col-sm-9">{{ $help->serviceLabel() }}</dd>
                        <dt class="col-sm-3">العميل</dt><dd class="col-sm-9">{{ $help->user?->name }} · <span dir="ltr">{{ $help->user?->email }}</span></dd>
                        <dt class="col-sm-3">واتساب العميل</dt><dd class="col-sm-9"><span dir="ltr">{{ $help->whatsapp }}</span></dd>
                        <dt class="col-sm-3">التاريخ</dt><dd class="col-sm-9">{{ $help->created_at->format('Y-m-d H:i') }}</dd>
                        <dt class="col-sm-3">الرسالة</dt><dd class="col-sm-9" style="white-space: pre-line">{{ $help->message }}</dd>
                        @if ($help->source)<dt class="col-sm-3">المصدر</dt><dd class="col-sm-9">{{ $help->source === 'staged_form' ? 'تعثّر في نموذج «مشروعك ما بيوقف»' : 'تعثّر في نموذج «اطلب عرضًا»' }}</dd>@endif
                        @if ($help->context)<dt class="col-sm-3">ما كتبه في النموذج</dt><dd class="col-sm-9" style="white-space: pre-line">{{ $help->context }}</dd>@endif
                    </dl>
                    <a class="btn btn-success mt-3" target="_blank" rel="noopener"
                        href="{{ $help->whatsappUrl('مرحبًا ' . $help->user?->name . '، معك فريق نفّذها بخصوص طلب المساعدة ' . $help->number . ' (' . $help->serviceLabel() . ').') }}">
                        <i class="fab fa-whatsapp"></i> فتح واتساب
                    </a>
                    <small class="text-muted d-block mt-1">تُفتح رسالة جاهزة، وأنت من يضغط إرسال.</small>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h6 class="mb-0">ملاحظات الفريق</h6></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.help-requests.notes', $help) }}" class="mb-3">
                        @csrf
                        <textarea name="body" rows="2" class="form-control" required maxlength="3000" placeholder="ماذا تم؟ (اتصلت، اقترحت خدمة…)"></textarea>
                        <button class="btn btn-primary btn-sm mt-2">إضافة ملاحظة</button>
                    </form>
                    <ul class="list-unstyled mb-0">
                        @forelse ($help->notes as $note)
                            <li class="border-bottom py-2">
                                <div style="white-space: pre-line">{{ $note->body }}</div>
                                <small class="text-muted">{{ $note->created_at?->format('Y-m-d H:i') }} · {{ $note->user?->name }}</small>
                            </li>
                        @empty
                            <li class="text-muted">لا توجد ملاحظات بعد.</li>
                        @endforelse
                    </ul>
                    <small class="text-muted d-block mt-2">ملاحظات الفريق تبقى داخلية ولا تظهر للعميل.</small>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><h6 class="mb-0">المتابعة</h6></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.help-requests.update', $help) }}">
                        @csrf
                        <div class="form-group">
                            <label>الحالة</label>
                            <select name="status" class="form-control">
                                @foreach (HelpRequest::STATUSES as $key => $label)
                                    <option value="{{ $key }}" @selected($help->status === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>إسناد لموظف</label>
                            <select name="assigned_to" class="form-control">
                                <option value="">— بدون —</option>
                                @foreach ($staff as $member)
                                    <option value="{{ $member->id }}" @selected($help->assigned_to === $member->id)>{{ $member->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button class="btn btn-primary btn-block">حفظ</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
