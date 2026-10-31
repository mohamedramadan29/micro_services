@extends('website.layouts.master')
@php use App\Models\BusinessDelivery as D; use App\Models\BusinessTask as T; @endphp
@section('title')
    {{ $project->title }} — نفّذها للأعمال
@endsection
@section('content')
    <section class="gray-bg pt-4 text-right profile_page" dir="rtl">
        <div class="container-fluid"><div class="row m-0">
            <div class="col-xl-3 col-lg-4 col-md-12 col-sm-12">@include('website.service-requests.partials.dashboard-nav')</div>
            <div class="col-xl-9 col-lg-8 col-md-12 col-sm-12">
                <div class="bredcrumb_wrap"><nav aria-label="breadcrumb"><ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('business.mine') }}"> نفّذها للأعمال </a></li>
                    <li class="breadcrumb-item active" aria-current="page" dir="ltr"> {{ $project->number }} </li>
                </ol></nav></div>
                @if ($errors->any())<div class="rf-notice rf-notice-error" role="alert">{{ $errors->first() }}</div>@endif

                <div class="_dashboard_content">
                    <div class="_dashboard_content_header"><div class="_dashboard__header_flex">
                        <h4>{{ $project->title }} <small class="rf-muted" dir="ltr">{{ $project->number }}</small></h4>
                        <span class="rf-status {{ $project->status === 'completed' ? 'rf-status-completed' : 'rf-status-open' }}">{{ $project->statusLabel() }}</span>
                    </div></div>
                    <div class="_dashboard_content_body">
                        @php($pending = $project->deliveries->where('status', D::STATUS_PENDING))
                        <div class="bz-kpis">
                            <div><strong>{{ $project->progress() }}%</strong><span>الإنجاز</span></div>
                            <div><strong>{{ $project->tasks->where('status', '!=', T::STATUS_DONE)->count() }}</strong><span>مهام جارية</span></div>
                            <div class="{{ $pending->isNotEmpty() ? 'bz-kpi-turn' : '' }}"><strong>{{ $pending->count() }}</strong><span>بانتظار اعتمادك</span></div>
                            <div><strong>{{ $project->due_at?->format('Y-m-d') ?? '—' }}</strong><span>موعد التسليم</span></div>
                        </div>

                        <h5 class="rf-section-title">المخرجات والنسخ</h5>
                        @forelse ($project->deliveries as $delivery)
                            <div class="bz-delivery bz-delivery-{{ $delivery->status }}" id="delivery-{{ $delivery->id }}">
                                <div class="d-flex justify-content-between flex-wrap" style="gap: 6px">
                                    <strong>{{ $delivery->title }} <small class="rf-muted">— نسخة {{ $delivery->version }}</small></strong>
                                    <span class="badge {{ $delivery->status === D::STATUS_APPROVED ? 'badge-success' : ($delivery->status === D::STATUS_CHANGES ? 'badge-warning' : 'badge-info') }}">{{ $delivery->statusLabel() }}</span>
                                </div>
                                @if ($delivery->note)<p class="mb-2" style="white-space: pre-line">{{ $delivery->note }}</p>@endif
                                @foreach ((array) $delivery->links as $link)
                                    <a href="{{ $link }}" target="_blank" rel="noopener nofollow" dir="ltr" class="d-block">{{ $link }}</a>
                                @endforeach
                                @foreach ($delivery->files as $file)
                                    <a href="{{ route('business.files.delivery', $file->id) }}" class="d-block"><i class="bi bi-paperclip"></i> {{ $file->original_name }}</a>
                                @endforeach
                                <small class="rf-muted d-block mt-1">{{ $delivery->created_at->format('Y-m-d H:i') }}@if ($delivery->reviewed_at) · رُوجعت {{ $delivery->reviewed_at->format('Y-m-d H:i') }}@endif</small>
                                @if ($delivery->client_comment)<p class="mt-2 mb-0"><strong>تعليقك:</strong> <span style="white-space: pre-line">{{ $delivery->client_comment }}</span></p>@endif

                                @if ($delivery->status === D::STATUS_PENDING)
                                    <form method="POST" action="{{ route('business.deliveries.review', $delivery->id) }}" class="mt-2">
                                        @csrf
                                        <label class="rf-field-label" for="bz-c-{{ $delivery->id }}">تعليق (مطلوب عند طلب التعديل)</label>
                                        <textarea id="bz-c-{{ $delivery->id }}" name="comment" rows="2" class="form-control mb-2" maxlength="3000" placeholder="مثال: غيّروا الموسيقى، واجعلوا الشعار أكبر في الثانية 5"></textarea>
                                        <div class="d-flex flex-wrap" style="gap: 8px">
                                            <button name="decision" value="approve" class="btn rf-btn-primary"><i class="bi bi-check2"></i> اعتماد النسخة</button>
                                            <button name="decision" value="changes" class="btn rf-btn-outline"><i class="bi bi-arrow-repeat"></i> طلب تعديل</button>
                                        </div>
                                    </form>
                                @endif
                            </div>
                        @empty
                            <div class="rf-empty-state"><p>لم تُرسل مخرجات بعد؛ تظهر هنا كل نسخة لتعتمدها أو تطلب تعديلها.</p></div>
                        @endforelse

                        <h5 class="rf-section-title mt-4">المهام</h5>
                        <div class="bz-board bz-board-client">
                            @foreach (T::STATUSES as $status => $label)
                                <div class="bz-col">
                                    <div class="bz-col-head">{{ $label }} <span>{{ $project->tasks->where('status', $status)->count() }}</span></div>
                                    @foreach ($project->tasks->where('status', $status) as $task)
                                        <div class="bz-task {{ $task->isLate() ? 'bz-task-late' : '' }}">
                                            <strong>{{ $task->title }}</strong>
                                            @if ($task->due_at)<small class="rf-muted d-block">{{ $task->due_at->format('Y-m-d') }}{{ $task->isLate() ? ' · متأخرة' : '' }}</small>@endif
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>

                        @if ($project->events->isNotEmpty())
                            <h5 class="rf-section-title mt-4">سجل المشروع</h5>
                            <ul class="bz-log">
                                @foreach ($project->events->take(30) as $event)
                                    <li><span>{{ \App\Http\Controllers\admin\BusinessAdminController::actionLabel($event->action) }}@if ($event->details && ! in_array($event->action, ['note'], true)) — {{ \Illuminate\Support\Str::limit($event->details, 120) }}@endif</span>
                                        <small class="rf-muted">{{ $event->created_at?->format('Y-m-d H:i') }}</small></li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>
        </div></div>
    </section>
@endsection
