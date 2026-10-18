@extends('admin.layouts.master')
@php
    use App\Models\ServiceRequest;
    use App\Http\Controllers\admin\ServiceRequestAdminController as Ctl;
    $f = fn ($key) => $filters[$key] ?? '';
    $statusClass = [
        ServiceRequest::STATUS_OPEN => 'badge-info', ServiceRequest::STATUS_INQUIRY => 'badge-warning',
        ServiceRequest::STATUS_IN_PROGRESS => 'badge-primary', ServiceRequest::STATUS_COMPLETED => 'badge-success',
        ServiceRequest::STATUS_NOT_COMPLETED => 'badge-secondary',
    ];
@endphp
@section('title')
    {{ Ctl::TABS[$tab] }}
@endsection
@section('css')
    <style>
        .rq-stats { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 12px; margin-bottom: 16px; }
        .rq-stat { background: #fff; border: 1px solid #e3e8ef; border-radius: 8px; padding: 12px 14px; display: block; color: inherit; }
        .rq-stat:hover { border-color: #3fb697; color: inherit; }
        .rq-stat strong { display: block; font-size: 24px; line-height: 1.2; }
        .rq-stat span { color: #6b7785; font-size: 13px; }
        .rq-stat.rq-alert strong { color: #c0392b; }
        .rq-tabs .nav-link { border-radius: 20px; margin-left: 6px; }
        .rq-tabs .nav-link.active { background: #3fb697; color: #fff; }
        .rq-filters .form-control { min-width: 140px; }
        .rq-muted { color: #6b7785; font-size: 12px; }
    </style>
@endsection
@section('content')
    <div class="breadcrumb-header justify-content-between">
        <div class="my-auto">
            <div class="d-flex">
                <h4 class="content-title mb-0 my-auto">طلبات الخدمات</h4><span class="text-muted mt-1 tx-13 mr-2 mb-0">/ {{ Ctl::TABS[$tab] }}</span>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="rq-stats">
        <a class="rq-stat" href="{{ route('admin.requests.index', ['tab' => 'active', 'status' => 'open']) }}"><strong>{{ $stats['open'] }}</strong><span>مفتوحة للعروض</span></a>
        <a class="rq-stat" href="{{ route('admin.requests.index', ['tab' => 'active', 'status' => 'in_progress']) }}"><strong>{{ $stats['in_progress'] }}</strong><span>قيد التنفيذ</span></a>
        <a class="rq-stat" href="{{ route('admin.requests.index', ['tab' => 'completed']) }}"><strong>{{ $stats['completed'] }}</strong><span>تم تنفيذها</span></a>
        <a class="rq-stat" href="{{ route('admin.requests.index', ['tab' => 'not_completed']) }}"><strong>{{ $stats['not_completed'] }}</strong><span>لم يتم تنفيذها</span></a>
        <a class="rq-stat" href="{{ route('admin.requests.index', ['tab' => 'inquiries']) }}"><strong>{{ $stats['inquiries'] }}</strong><span>استفسارات أسعار</span></a>
        <a class="rq-stat {{ $stats['followup'] ? 'rq-alert' : '' }}" href="{{ route('admin.requests.index', ['tab' => 'followup']) }}"><strong>{{ $stats['followup'] }}</strong><span>تنبيهات تحتاج متابعة</span></a>
        @if ($stats['refunds'])
            <a class="rq-stat rq-alert" href="{{ route('admin.requests.index', ['tab' => 'refunds']) }}"><strong>{{ $stats['refunds'] }}</strong><span>دفعات تحتاج استرجاعًا</span></a>
        @endif
    </div>

    <div class="card">
        <div class="card-body">
            <ul class="nav rq-tabs mb-3">
                @foreach (Ctl::TABS as $key => $label)
                    @continue($key === 'refunds' && ! $stats['refunds'] && $tab !== 'refunds')
                    <li class="nav-item">
                        <a class="nav-link {{ $tab === $key ? 'active' : '' }}" href="{{ route('admin.requests.index', ['tab' => $key]) }}">{{ $label }}</a>
                    </li>
                @endforeach
            </ul>

            @if ($tab === 'refunds')
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead><tr><th>الطلب</th><th>العميل</th><th>المبلغ</th><th>تاريخ الدفع</th><th></th></tr></thead>
                        <tbody>
                            @forelse ($payments as $payment)
                                <tr>
                                    <td><a href="{{ route('admin.requests.show', $payment->request) }}">{{ $payment->request->number }}</a><br><span class="rq-muted">عرض {{ $payment->offer?->freelancer?->name }}</span></td>
                                    <td>{{ $payment->client?->name }}<br><span class="rq-muted">{{ $payment->client?->email }}</span></td>
                                    <td>{{ \App\Support\Money::format($payment->amount) }}</td>
                                    <td>{{ $payment->paid_at?->format('Y-m-d H:i') }}</td>
                                    <td>
                                        <form method="POST" action="{{ route('admin.requests.refund', $payment) }}" onsubmit="return confirm(@js('هل استرجعت ' . (\App\Support\Money::format($payment->amount)) . ' للعميل من لوحة EasyKash؟ سيُسجَّل الاسترجاع هنا.'))">
                                            @csrf
                                            <button class="btn btn-sm btn-danger">تأكيد الاسترجاع</button><div class="rq-muted mt-1">استرجعه أولًا من لوحة EasyKash</div>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted">لا توجد دفعات تحتاج استرجاعًا</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $payments->links() }}
            @else
                <form method="GET" class="form-inline rq-filters mb-3" style="gap: 8px">
                    <input type="hidden" name="tab" value="{{ $tab }}">
                    <input type="search" name="q" value="{{ $f('q') }}" class="form-control" placeholder="رقم الطلب، العميل، البريد، الهاتف">
                    @if (in_array($tab, ['active', 'completed', 'not_completed']))
                        <input type="search" name="freelancer" value="{{ $f('freelancer') }}" class="form-control" placeholder="المستقل المختار">
                    @endif
                    <select name="source" class="form-control">
                        <option value="">كل المصادر</option>
                        @foreach (\App\Models\ServiceRequest::SOURCES as $key => $label)
                            <option value="{{ $key }}" @selected($f('source') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <select name="service" class="form-control">
                        <option value="">كل الخدمات</option>
                        @foreach ($services as $service)
                            <option value="{{ $service->id }}" @selected($f('service') == $service->id)>{{ $service->name }}</option>
                        @endforeach
                    </select>
                    @if ($tab === 'active')
                        <select name="status" class="form-control">
                            <option value="">كل الحالات</option>
                            <option value="open" @selected($f('status') === 'open')>مفتوح للعروض</option>
                            <option value="in_progress" @selected($f('status') === 'in_progress')>قيد التنفيذ</option>
                        </select>
                    @endif
                    @if ($tab === 'not_completed')
                        <select name="reason" class="form-control">
                            <option value="">كل الأسباب</option>
                            @foreach (ServiceRequest::CLOSE_REASONS as $key => $label)
                                <option value="{{ $key }}" @selected($f('reason') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    @endif
                    @if (in_array($tab, ['followup', 'inquiries']))
                        <select name="followup" class="form-control">
                            <option value="">كل حالات المتابعة</option>
                            @foreach (ServiceRequest::FOLLOWUP_STATUSES as $key => $label)
                                <option value="{{ $key }}" @selected($f('followup') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    @endif
                    @if (in_array($tab, ['active', 'not_completed']))
                        <select name="offers" class="form-control">
                            <option value="">العروض: الكل</option>
                            <option value="none" @selected($f('offers') === 'none')>بدون عروض</option>
                            <option value="some" @selected($f('offers') === 'some')>وصلت عروض</option>
                        </select>
                    @endif
                    <label class="rq-muted">من</label><input type="date" name="from" value="{{ $f('from') }}" class="form-control">
                    <label class="rq-muted">إلى</label><input type="date" name="to" value="{{ $f('to') }}" class="form-control">
                    <button type="submit" class="btn btn-primary">تصفية</button>
                    <a href="{{ route('admin.requests.index', ['tab' => $tab]) }}" class="btn btn-light">مسح</a>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>الطلب</th>
                                <th>العميل</th>
                                <th>الحالة</th>
                                <th>العروض</th>
                                <th>{{ in_array($tab, ['completed', 'not_completed']) ? 'تاريخ الإغلاق' : 'التاريخ' }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($requests as $item)
                                <tr>
                                    <td>
                                        <strong>{{ $item->number }}</strong><br>
                                        <span class="rq-muted">{{ $item->category_name }} ← {{ $item->service_name }}</span>
                                        @if ($item->source !== \App\Models\ServiceRequest::SOURCE_GUIDED)<span class="badge badge-info">{{ \App\Models\ServiceRequest::SOURCES[$item->source] ?? $item->source }}</span>@endif
                                        @if ($item->isStaged())<span class="badge badge-warning">مشروع بمراحل{{ $item->stopped_project ? ' · متوقف من الخارج' : '' }}</span>@endif
                                    </td>
                                    <td>{{ $item->contact_name }}<br><span class="rq-muted">{{ $item->contact_email }}</span></td>
                                    <td>
                                        <span class="badge {{ $statusClass[$item->status] ?? 'badge-light' }}">{{ $item->statusLabel() }}</span>
                                        @if ($item->closed_reason)
                                            <br><span class="rq-muted">{{ $item->closeReasonLabel() }}</span>
                                        @endif
                                        @if ($item->no_offer_alerted_at || $item->isInquiry())
                                            <br><span class="rq-muted"><i class="fas fa-bell"></i> متابعة: {{ $item->followupLabel() }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $item->offers_count }}
                                        @if ($item->acceptedOffer)
                                            <br><span class="rq-muted">المختار: {{ $item->acceptedOffer->freelancer?->name }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($tab === 'completed')
                                            {{ $item->completed_at?->format('Y-m-d') }}
                                        @elseif ($tab === 'not_completed')
                                            {{ $item->closed_at?->format('Y-m-d') }}
                                        @elseif ($tab === 'followup')
                                            تنبيه {{ $item->no_offer_alerted_at?->format('Y-m-d H:i') }}
                                        @else
                                            {{ $item->created_at->format('Y-m-d H:i') }}
                                        @endif
                                    </td>
                                    <td><a href="{{ route('admin.requests.show', $item) }}" class="btn btn-sm btn-primary">فتح السجل الكامل</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted">لا توجد طلبات</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $requests->links() }}
            @endif
        </div>
    </div>
@endsection
