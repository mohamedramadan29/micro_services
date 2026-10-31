@extends('admin.layouts.master')
@php use App\Models\BusinessTask as T; use App\Models\BusinessDelivery as D; use App\Models\BusinessProject as P; use App\Http\Controllers\admin\BusinessAdminController as A; @endphp
@section('title')
    {{ $project->number }} — مشاريع الشركات
@endsection
@section('css')
    <style>
        .bz-board { display: grid; grid-template-columns: repeat(4, minmax(200px, 1fr)); gap: 10px; overflow-x: auto; }
        .bz-col { background: #f5f7f8; border-radius: 8px; padding: 8px; min-height: 120px; }
        .bz-col-head { font-weight: 700; margin-bottom: 8px; display: flex; justify-content: space-between; }
        .bz-task { background: #fff; border: 1px solid #e3e6e8; border-radius: 6px; padding: 8px; margin-bottom: 8px; }
        .bz-task-late { border-color: #d93025; }
        .bz-task summary { cursor: pointer; font-size: 12px; color: #3fb699; }
        @media (max-width: 991px) { .bz-board { grid-template-columns: repeat(4, 220px); } }
    </style>
@endsection
@section('content')
    <div class="breadcrumb-header justify-content-between">
        <div class="my-auto"><div class="d-flex">
            <h4 class="content-title mb-0 my-auto">{{ $project->title }}</h4>
            <span class="text-muted mt-1 tx-13 mr-2 mb-0">/ <span dir="ltr">{{ $project->number }}</span> · {{ $project->statusLabel() }} · الإنجاز {{ $project->progress() }}%</span>
        </div></div>
        <div class="my-auto"><a href="{{ route('admin.business.projects') }}" class="btn btn-light"><i class="fas fa-arrow-right"></i> المشاريع</a></div>
    </div>
    @include('admin.catalog.partials.flash')

    <div class="row row-sm">
        <div class="col-lg-9">
            <div class="card" id="tasks">
                <div class="card-header d-flex justify-content-between align-items-center"><h6 class="mb-0">لوحة المهام</h6></div>
                <div class="card-body">
                    <div class="bz-board">
                        @foreach (T::STATUSES as $status => $label)
                            <div class="bz-col">
                                <div class="bz-col-head">{{ $label }} <span class="badge badge-light">{{ $project->tasks->where('status', $status)->count() }}</span></div>
                                @foreach ($project->tasks->where('status', $status) as $task)
                                    <div class="bz-task {{ $task->isLate() ? 'bz-task-late' : '' }}">
                                        <strong>{{ $task->title }}</strong>
                                        <div class="small text-muted">{{ $task->assignee?->name ?? 'بدون مسؤول' }} · {{ T::PRIORITIES[$task->priority] ?? '' }}@if ($task->due_at) · {{ $task->due_at->format('Y-m-d') }}@endif @if ($task->isLate())<span class="text-danger">· متأخرة</span>@endif</div>
                                        <form method="POST" action="{{ route('admin.business.tasks.move', [$project, $task->id]) }}" class="mt-1">
                                            @csrf
                                            <select name="status" class="form-control form-control-sm" onchange="this.form.submit()" aria-label="نقل المهمة">
                                                @foreach (T::STATUSES as $key => $name)<option value="{{ $key }}" @selected($task->status === $key)>{{ $name }}</option>@endforeach
                                            </select>
                                        </form>
                                        <details class="mt-1"><summary>تعديل</summary>
                                            <form method="POST" action="{{ route('admin.business.tasks.update', [$project, $task->id]) }}" class="mt-1">
                                                @csrf
                                                <input type="text" name="title" class="form-control form-control-sm mb-1" value="{{ $task->title }}" required maxlength="150" aria-label="العنوان">
                                                <textarea name="description" class="form-control form-control-sm mb-1" rows="2" maxlength="3000" aria-label="الوصف">{{ $task->description }}</textarea>
                                                <select name="assignee_id" class="form-control form-control-sm mb-1" aria-label="المسؤول"><option value="">بدون مسؤول</option>
                                                    @foreach ($staff as $member)<option value="{{ $member->id }}" @selected($task->assignee_id === $member->id)>{{ $member->name }}</option>@endforeach</select>
                                                <select name="priority" class="form-control form-control-sm mb-1" aria-label="الأولوية">@foreach (T::PRIORITIES as $key => $name)<option value="{{ $key }}" @selected($task->priority === $key)>{{ $name }}</option>@endforeach</select>
                                                <input type="date" name="due_at" class="form-control form-control-sm mb-1" value="{{ $task->due_at?->format('Y-m-d') }}" aria-label="الموعد">
                                                <input type="hidden" name="status" value="{{ $task->status }}">
                                                <button class="btn btn-primary btn-sm">حفظ</button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.business.tasks.destroy', [$project, $task->id]) }}" onsubmit="return confirm('حذف المهمة؟')" class="mt-1">@csrf<button class="btn btn-link btn-sm text-danger p-0">حذف المهمة</button></form>
                                        </details>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>

                    <details class="mt-3" @if ($project->tasks->isEmpty()) open @endif>
                        <summary class="font-weight-bold">+ مهمة جديدة</summary>
                        <form method="POST" action="{{ route('admin.business.tasks.store', $project) }}" class="mt-2">
                            @csrf
                            <div class="form-row">
                                <div class="col-md-5 form-group"><input type="text" name="title" class="form-control" required maxlength="150" placeholder="مثال: مونتاج 4 ريلز" aria-label="عنوان المهمة"></div>
                                <div class="col-md-3 form-group"><select name="assignee_id" class="form-control" aria-label="المسؤول"><option value="">بدون مسؤول</option>
                                    @foreach ($staff as $member)<option value="{{ $member->id }}">{{ $member->name }}</option>@endforeach</select></div>
                                <div class="col-md-2 form-group"><select name="priority" class="form-control" aria-label="الأولوية">@foreach (T::PRIORITIES as $key => $name)<option value="{{ $key }}" @selected($key === 'normal')>{{ $name }}</option>@endforeach</select></div>
                                <div class="col-md-2 form-group"><input type="date" name="due_at" class="form-control" aria-label="الموعد"></div>
                            </div>
                            <textarea name="description" class="form-control mb-2" rows="2" maxlength="3000" placeholder="وصف (اختياري)" aria-label="الوصف"></textarea>
                            <input type="hidden" name="status" value="{{ T::STATUS_TODO }}">
                            <button class="btn btn-primary">إضافة المهمة</button>
                        </form>
                    </details>
                </div>
            </div>

            <div class="card" id="deliveries">
                <div class="card-header"><h6 class="mb-0">المخرجات والنسخ (بوابة الاعتماد)</h6></div>
                <div class="card-body">
                    <details @if ($project->deliveries->isEmpty()) open @endif class="mb-3">
                        <summary class="font-weight-bold">+ إرسال نسخة للعميل</summary>
                        <form method="POST" action="{{ route('admin.business.deliveries.store', $project) }}" enctype="multipart/form-data" class="mt-2">
                            @csrf
                            <div class="form-row">
                                <div class="col-md-6 form-group"><label for="d-prev">نسخة جديدة من</label>
                                    <select id="d-prev" name="previous_id" class="form-control"><option value="">مخرج جديد (نسخة 1)</option>
                                        @foreach ($project->deliveries->whereNull('root_id') as $root)<option value="{{ $root->id }}">{{ $root->title }}</option>@endforeach</select></div>
                                <div class="col-md-6 form-group"><label for="d-title">اسم المخرج (للمخرج الجديد)</label>
                                    <input id="d-title" type="text" name="title" class="form-control" maxlength="150" placeholder="مثال: ريلز رقم 4"></div>
                            </div>
                            <div class="form-group"><label for="d-task">المهمة (اختياري — تنتقل إلى «للمراجعة»)</label>
                                <select id="d-task" name="business_task_id" class="form-control"><option value="">—</option>
                                    @foreach ($project->tasks as $task)<option value="{{ $task->id }}">{{ $task->title }}</option>@endforeach</select></div>
                            <div class="form-group"><label for="d-note">ملاحظة للعميل</label><textarea id="d-note" name="note" rows="2" class="form-control" maxlength="5000"></textarea></div>
                            <div class="form-group"><label for="d-links">روابط (رابط في كل سطر — للفيديوهات الكبيرة)</label><textarea id="d-links" name="links" rows="2" class="form-control" dir="ltr" maxlength="3000"></textarea></div>
                            <div class="form-group"><label for="d-files">ملفات (حتى 10 × 50 ميجابايت)</label><input id="d-files" type="file" name="files[]" multiple class="form-control"></div>
                            <button class="btn btn-primary">إرسال للعميل للاعتماد</button>
                        </form>
                    </details>
                    @forelse ($project->deliveries as $delivery)
                        <div class="border rounded p-2 mb-2">
                            <div class="d-flex justify-content-between"><strong>{{ $delivery->title }} — نسخة {{ $delivery->version }}</strong>
                                <span class="badge {{ $delivery->status === D::STATUS_APPROVED ? 'badge-success' : ($delivery->status === D::STATUS_CHANGES ? 'badge-warning' : 'badge-info') }}">{{ $delivery->statusLabel() }}</span></div>
                            @if ($delivery->task)<div class="small text-muted">المهمة: {{ $delivery->task->title }}</div>@endif
                            @if ($delivery->note)<div style="white-space: pre-line">{{ $delivery->note }}</div>@endif
                            @foreach ((array) $delivery->links as $link)<a href="{{ $link }}" target="_blank" rel="noopener nofollow" dir="ltr" class="d-block small">{{ $link }}</a>@endforeach
                            @foreach ($delivery->files as $file)<a href="{{ route('business.files.delivery', $file->id) }}" class="d-block small"><i class="fas fa-paperclip"></i> {{ $file->original_name }}</a>@endforeach
                            @if ($delivery->client_comment)<div class="mt-1"><strong>تعليق العميل:</strong> <span style="white-space: pre-line">{{ $delivery->client_comment }}</span></div>@endif
                            <small class="text-muted">{{ $delivery->created_at->format('Y-m-d H:i') }}@if ($delivery->reviewed_at) · رُوجعت {{ $delivery->reviewed_at->format('Y-m-d H:i') }}@endif</small>
                        </div>
                    @empty
                        <p class="text-muted mb-0">لم تُرسل نسخ بعد.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-lg-3">
            <div class="card"><div class="card-body">
                <dl class="mb-2">
                    <dt>العميل</dt><dd>@if ($project->user)<a href="{{ route('admin.clients.show', $project->user) }}">{{ $project->user->name }}</a>@else — @endif{{ $company ? ' · ' . $company->name : '' }}</dd>
                    <dt>القيمة</dt><dd>{{ \App\Support\Money::format($project->amount) }} @if ($project->request?->isPaid())<span class="badge badge-success">مدفوع</span>@else<span class="badge badge-warning">غير مدفوع</span>@endif</dd>
                    @if ($project->request)<dt>الطلب</dt><dd><a href="{{ route('admin.business.requests.show', $project->request) }}" dir="ltr">{{ $project->request->number }}</a></dd>@endif
                </dl>
                <form method="POST" action="{{ route('admin.business.projects.update', $project) }}">
                    @csrf
                    <div class="form-group"><label for="p-status">الحالة</label><select id="p-status" name="status" class="form-control">
                        @foreach (P::STATUSES as $key => $label)<option value="{{ $key }}" @selected($project->status === $key)>{{ $label }}</option>@endforeach</select></div>
                    <div class="form-group"><label for="p-manager">مدير المشروع</label><select id="p-manager" name="manager_id" class="form-control"><option value="">— بدون —</option>
                        @foreach ($staff as $member)<option value="{{ $member->id }}" @selected($project->manager_id === $member->id)>{{ $member->name }}</option>@endforeach</select></div>
                    <div class="form-group"><label for="p-due">موعد التسليم</label><input id="p-due" type="date" name="due_at" class="form-control" value="{{ $project->due_at?->format('Y-m-d') }}"></div>
                    <button class="btn btn-primary btn-block">حفظ</button>
                </form>
            </div></div>

            <div class="card">
                <div class="card-header"><h6 class="mb-0">ملاحظات وسجل المشروع</h6></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.business.projects.note', $project) }}" class="mb-2">
                        @csrf
                        <textarea name="body" rows="2" class="form-control" required maxlength="3000" placeholder="ملاحظة داخلية" aria-label="ملاحظة داخلية"></textarea>
                        <button class="btn btn-primary btn-sm mt-1">إضافة</button>
                    </form>
                    <ul class="list-unstyled mb-0 small">
                        @foreach ($project->events->take(40) as $event)
                            <li class="border-bottom py-1"><strong>{{ A::actionLabel($event->action) }}</strong>@if ($event->internal) <span class="badge badge-light">داخلية</span>@endif
                                @if ($event->details)<div style="white-space: pre-line">{{ $event->details }}</div>@endif
                                <span class="text-muted">{{ $event->created_at?->format('Y-m-d H:i') }} · {{ $event->user?->name ?? 'النظام' }}</span></li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection
