@extends('admin.layouts.master')
@php use App\Models\Suggestion; @endphp
@section('title')
    {{ $suggestion->reference_code }} — الاقتراحات
@endsection
@section('content')
    <div class="breadcrumb-header justify-content-between">
        <div class="my-auto">
            <div class="d-flex">
                <h4 class="content-title mb-0 my-auto">الاقتراحات</h4>
                <span class="text-muted mt-1 tx-13 mr-2 mb-0">/ تفاصيل الاقتراح <span dir="ltr">{{ $suggestion->reference_code }}</span></span>
            </div>
        </div>
        <div class="my-auto">
            <a href="{{ route('admin.suggestions.index') }}" class="btn btn-light"><i class="fas fa-arrow-right"></i> رجوع</a>
        </div>
    </div>
    @include('admin.catalog.partials.flash')

    <div class="row row-sm">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><h6 class="mb-0">{{ $suggestion->title }}</h6></div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-3">الحالة</dt><dd class="col-sm-9">{{ $suggestion->statusLabel() }}</dd>
                        <dt class="col-sm-3">النوع</dt><dd class="col-sm-9">{{ $suggestion->categoryLabel() }}</dd>
                        <dt class="col-sm-3">المصدر</dt><dd class="col-sm-9">{{ $suggestion->roleLabel() }}</dd>
                        <dt class="col-sm-3">المرسل</dt>
                        <dd class="col-sm-9">
                            {{ $suggestion->senderName() }}
                            @if ($canSeeContact)
                                @if ($suggestion->user)
                                    · <span dir="ltr">{{ $suggestion->user->email }}</span>
                                @elseif ($suggestion->guest_email)
                                    · <span dir="ltr">{{ $suggestion->guest_email }}</span> (طلب التحديثات)
                                @endif
                            @endif
                        </dd>
                        <dt class="col-sm-3">التاريخ</dt><dd class="col-sm-9">{{ $suggestion->created_at->format('Y-m-d H:i') }}</dd>
                        <dt class="col-sm-3">الشرح</dt><dd class="col-sm-9" style="white-space: pre-line">{{ $suggestion->description }}</dd>
                        @if ($suggestion->status_reason)
                            <dt class="col-sm-3">آخر رد للمرسل</dt><dd class="col-sm-9" style="white-space: pre-line">{{ $suggestion->status_reason }}</dd>
                        @endif
                        @if ($suggestion->duplicateOf)
                            <dt class="col-sm-3">مكرر من</dt>
                            <dd class="col-sm-9"><a href="{{ route('admin.suggestions.show', $suggestion->duplicateOf) }}" dir="ltr">{{ $suggestion->duplicateOf->reference_code }}</a> — {{ $suggestion->duplicateOf->title }}</dd>
                        @endif
                        @if ($suggestion->duplicates->isNotEmpty())
                            <dt class="col-sm-3">اقتراحات مكررة منه</dt>
                            <dd class="col-sm-9">
                                @foreach ($suggestion->duplicates as $repeat)
                                    <a href="{{ route('admin.suggestions.show', $repeat) }}" dir="ltr">{{ $repeat->reference_code }}</a>@if (! $loop->last) · @endif
                                @endforeach
                            </dd>
                        @endif
                    </dl>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h6 class="mb-0">ملاحظات داخلية</h6></div>
                <div class="card-body">
                    @if ($canManage)
                        <form method="POST" action="{{ route('admin.suggestions.notes', $suggestion) }}" class="mb-3">
                            @csrf
                            <label for="sg-note" class="sr-only">ملاحظة داخلية</label>
                            <textarea id="sg-note" name="body" rows="2" class="form-control" required maxlength="3000" placeholder="تقييم الفكرة، تكلفتها، من يتابعها…"></textarea>
                            <button class="btn btn-primary btn-sm mt-2">إضافة ملاحظة</button>
                        </form>
                    @endif
                    <ul class="list-unstyled mb-0">
                        @forelse ($suggestion->notes as $note)
                            <li class="border-bottom py-2">
                                <div style="white-space: pre-line">{{ $note->body }}</div>
                                <small class="text-muted">{{ $note->created_at?->format('Y-m-d H:i') }} · {{ $note->user?->name }}</small>
                            </li>
                        @empty
                            <li class="text-muted">لا توجد ملاحظات بعد.</li>
                        @endforelse
                    </ul>
                    <small class="text-muted d-block mt-2">الملاحظات داخلية: لا يراها صاحب الاقتراح ولا تُرسل له إشعارًا.</small>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h6 class="mb-0">سجل الإجراءات</h6></div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        @foreach ($suggestion->history as $entry)
                            <li class="border-bottom py-2">
                                @switch($entry->action)
                                    @case('created') وصل الاقتراح @break
                                    @case('status')
                                        الحالة: {{ Suggestion::STATUSES[$entry->from_status] ?? $entry->from_status }} ← {{ Suggestion::STATUSES[$entry->to_status] ?? $entry->to_status }}
                                        @break
                                    @case('assigned') الإسناد: {{ $entry->details }} @break
                                    @case('priority') الأولوية: {{ $entry->details }} @break
                                    @default {{ $entry->details }}
                                @endswitch
                                @if ($entry->action === 'status' && $entry->details)<div class="text-muted" style="white-space: pre-line">الرد: {{ $entry->details }}</div>@endif
                                <small class="text-muted d-block">{{ $entry->created_at?->format('Y-m-d H:i') }} · {{ $entry->user?->name ?? ($entry->action === 'created' ? $suggestion->senderName() : 'النظام') }}</small>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            @if ($canManage)
                <div class="card">
                    <div class="card-header"><h6 class="mb-0">المتابعة</h6></div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.suggestions.update', $suggestion) }}">
                            @csrf
                            <div class="form-group">
                                <label for="sg-status">الحالة</label>
                                <select id="sg-status" name="status" class="form-control">
                                    @foreach (Suggestion::STATUSES as $key => $label)
                                        <option value="{{ $key }}" @selected($suggestion->status === $key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="sg-reason">الرد على صاحب الاقتراح (اختياري)</label>
                                <textarea id="sg-reason" name="status_reason" rows="3" class="form-control" maxlength="1000"
                                          placeholder="مثل: شكرًا لفكرتك، سنضيفها ضمن تحديثات الربع القادم">{{ old('status_reason', $suggestion->status_reason) }}</textarea>
                                <small class="text-muted">يظهر له في «اقتراحاتي»، ويُرسل مع الإشعار عند «مخطط للتنفيذ» و«تم التنفيذ» و«مغلق» فقط. تركه فارغًا يبقي الرد السابق.</small>
                                @error('status_reason')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group">
                                <label for="sg-assignee">إسناد لموظف</label>
                                <select id="sg-assignee" name="assigned_admin_id" class="form-control">
                                    <option value="">— بدون —</option>
                                    @foreach ($staff as $member)
                                        <option value="{{ $member->id }}" @selected($suggestion->assigned_admin_id === $member->id)>{{ $member->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="sg-priority">الأولوية الداخلية</label>
                                <select id="sg-priority" name="internal_priority" class="form-control">
                                    @foreach (Suggestion::PRIORITIES as $key => $label)
                                        <option value="{{ $key }}" @selected($suggestion->internal_priority === $key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button class="btn btn-primary btn-block">حفظ</button>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><h6 class="mb-0">ربط اقتراح مكرر</h6></div>
                    <div class="card-body">
                        @if ($suggestion->duplicate_of_id)
                            <form method="POST" action="{{ route('admin.suggestions.duplicate', $suggestion) }}">
                                @csrf
                                <input type="hidden" name="unlink" value="1">
                                <p class="mb-2">هذا الاقتراح مربوط كمكرر من <span dir="ltr">{{ $suggestion->duplicateOf?->reference_code }}</span>.</p>
                                <button class="btn btn-light btn-block">إلغاء الربط</button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('admin.suggestions.duplicate', $suggestion) }}">
                                @csrf
                                <label for="sg-original">رقم الاقتراح الأصلي</label>
                                <input id="sg-original" type="text" name="original" class="form-control" dir="ltr" placeholder="SUG-1001" value="{{ old('original') }}" required>
                                @error('original')<div class="text-danger small">{{ $message }}</div>@enderror
                                <button class="btn btn-light btn-block mt-2">ربط كمكرر</button>
                                <small class="text-muted d-block mt-1">يبقى الاقتراحان كما أُرسلا؛ يظهر الربط في كليهما.</small>
                            </form>
                        @endif
                    </div>
                </div>
            @else
                <div class="card"><div class="card-body text-muted">صلاحيتك اطلاع فقط على الاقتراحات.</div></div>
            @endif
        </div>
    </div>
@endsection
