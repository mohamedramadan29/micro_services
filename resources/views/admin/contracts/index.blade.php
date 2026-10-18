@extends('admin.layouts.master')
@php use App\Http\Controllers\admin\ContractAdminController as C; use App\Models\Contract; @endphp
@section('title')
    مشروعك ما بيوقف — العقود
@endsection
@section('content')
    <div class="breadcrumb-header justify-content-between">
        <div class="my-auto">
            <div class="d-flex">
                <h4 class="content-title mb-0 my-auto">مشروعك ما بيوقف</h4>
                <span class="text-muted mt-1 tx-13 mr-2 mb-0">/ العقود والخلافات وتغيير المستقلين — القرارات من صفحة كل عقد</span>
            </div>
        </div>
    </div>
    <div class="d-flex flex-wrap mb-3" style="gap: 6px">
        @foreach (C::TABS as $key => $label)
            <a href="{{ route('admin.contracts.index', ['tab' => $key]) }}" class="btn {{ $tab === $key ? 'btn-primary' : 'btn-light' }}">{{ $label }}
                @if (! empty($counts[$key]))<span class="badge badge-danger">{{ $counts[$key] }}</span>@endif</a>
        @endforeach
    </div>
    <div class="card">
        <div class="card-body">
            <form method="GET" class="mb-3 d-flex flex-wrap" style="gap: 6px"><input type="hidden" name="tab" value="{{ $tab }}">
                <input type="search" name="q" value="{{ request('q') }}" class="form-control" style="max-width: 240px" placeholder="رقم العقد أو عنوانه أو اسم/بريد طرف">
                <select name="status" class="form-control" style="max-width: 170px">
                    <option value="">كل الحالات</option>
                    @foreach (Contract::STATUSES as $key => $label)<option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>@endforeach
                </select>
                <select name="service" class="form-control" style="max-width: 190px">
                    <option value="">كل الخدمات</option>
                    @foreach ($services as $service)<option value="{{ $service->id }}" @selected((string) request('service') === (string) $service->id)>{{ $service->name }}</option>@endforeach
                </select>
                <select name="waiting" class="form-control" style="max-width: 170px">
                    <option value="">بانتظار: الكل</option>
                    @foreach (Contract::WAITING_ON as $key => $label)<option value="{{ $key }}" @selected(request('waiting') === $key)>بانتظار {{ $label }}</option>@endforeach
                </select>
                <input type="date" name="from" value="{{ request('from') }}" class="form-control" style="max-width: 160px" title="من تاريخ">
                <input type="date" name="to" value="{{ request('to') }}" class="form-control" style="max-width: 160px" title="إلى تاريخ">
                <select name="sort" class="form-control" style="max-width: 170px">
                    <option value="">الأحدث</option>
                    <option value="due" @selected(request('sort') === 'due')>الأقرب موعدًا</option>
                    <option value="value" @selected(request('sort') === 'value')>الأعلى قيمة</option>
                </select>
                <button class="btn btn-light">تطبيق</button>
            </form>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>العقد</th><th>الطرفان</th><th>الحالة</th><th>المرحلة الحالية</th><th>المبالغ</th><th>الموعد</th><th>بانتظار</th><th>آخر نشاط</th><th></th></tr></thead>
                    <tbody>
                        @forelse ($contracts as $contract)
                            @php
                                $stage = $contract->currentStageLoaded();
                                $waitingOn = $contract->waitingOn();
                                $total = (float) $contract->stages->sum('amount');
                                $held = (float) $contract->stages->where('status', \App\Models\ContractStage::STATUS_FUNDED)->sum('amount');
                                $released = (float) $contract->stages->sum('freelancer_amount');
                                $late = $stage && $stage->due_at && $stage->due_at->isPast() && $stage->status === \App\Models\ContractStage::STATUS_FUNDED
                                    && ! $stage->deliveries->contains('status', \App\Models\ContractDelivery::STATUS_SUBMITTED) && ! $stage->pauses->contains('status', \App\Models\ContractPause::STATUS_ACTIVE);
                            @endphp
                            <tr>
                                <td><strong>{{ $contract->number }}</strong><div class="small text-muted">{{ $contract->title }}</div>
                                    @if ($contract->request)<a class="small" href="{{ url('admin/requests/' . $contract->request->number) }}">{{ $contract->request->number }}</a>@endif</td>
                                <td><div>العميل: {{ $contract->client?->name }}</div><div class="small text-muted">المستقل: {{ $contract->freelancer?->name }}</div></td>
                                <td>{{ $contract->statusLabel() }}</td>
                                <td>
                                    @if ($stage)
                                        {{ $stage->sort_order }}/{{ $contract->stages->count() }} — {{ $stage->title }}
                                        <div class="small text-muted">{{ $stage->statusLabel() }}</div>
                                    @elseif ($contract->stages->isNotEmpty())
                                        <span class="text-muted">كل المراحل انتهت</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="small">
                                    @if ($total)
                                        الإجمالي {{ \App\Support\Money::format($total) }}<br>
                                        محجوز {{ \App\Support\Money::format($held) }}<br>
                                        للمستقل {{ \App\Support\Money::format($released) }}
                                    @else — @endif
                                </td>
                                <td>
                                    @if ($stage?->due_at)
                                        {{ $stage->due_at->format('Y-m-d') }}
                                        @if ($late)<span class="badge badge-danger">متأخر {{ (int) $stage->due_at->diffInDays(now()) }} يوم</span>@endif
                                    @else — @endif
                                </td>
                                <td>
                                    @if ($waitingOn)
                                        <span class="badge {{ ['client' => 'badge-info', 'freelancer' => 'badge-warning', 'team' => 'badge-danger'][$waitingOn[0]] }}">{{ Contract::WAITING_ON[$waitingOn[0]] }}</span>
                                        <div class="small text-muted">{{ $waitingOn[1] }}</div>
                                    @else — @endif
                                </td>
                                <td class="small">{{ $contract->last_activity ? \Illuminate\Support\Carbon::parse($contract->last_activity)->diffForHumans() : '—' }}</td>
                                <td><a href="{{ route('contracts.show', $contract) }}" class="btn btn-sm btn-primary" target="_blank">فتح العقد</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center text-muted">لا توجد عقود هنا.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $contracts->links() }}</div>
        </div>
    </div>
@endsection
