{{--
    Service 3, phase 4: the clock of one stage — lateness, «waiting for the client» pause and its
    history. Expects $contract, $stage, $isCurrent, $isClient, $isFreelancer, $pending (delivery waiting review).
--}}
@php
    use App\Models\ContractPause as P;
    use App\Models\ContractStage as S;
    $active = $stage->pauses->firstWhere('status', P::STATUS_ACTIVE);
    $running = $isCurrent && $stage->status === S::STATUS_FUNDED && $stage->started_at;
    $late = $running && ! $active && ! $pending ? $stage->daysLate() : 0;
@endphp
@if ($late > 0)
    <div class="rf-notice rf-notice-error mb-2">
        <strong>متأخرة {{ $late }} {{ $late === 1 ? 'يوم' : 'أيام' }}</strong> عن موعدها {{ $stage->due_at->format('Y-m-d') }}.
        @if ($isClient && $late >= \App\Services\Contracts\ContractPolicy::number('late_change_days'))
            يحق لك طلب تغيير المستقل: <a href="#disputes">«طلب تغيير المستقل» أسفل الصفحة</a>.
        @elseif ($isFreelancer)
            سلّم المرحلة، أو أوقف العدّاد إن كنت تنتظر شيئًا من العميل.
        @endif
    </div>
@endif

@if ($active)
    <div class="rf-pause">
        <strong><i class="bi bi-pause-circle"></i> العدّاد متوقف منذ {{ $active->started_at->format('Y-m-d H:i') }}</strong>
        <p class="mb-1">المستقل يحتاج من العميل: {{ $active->request }}</p>
        <p class="rf-muted mb-2">يُمدّ موعد المرحلة بمدة الإيقاف عند الاستئناف. بعد {{ \App\Services\Contracts\ContractPolicy::number('pause_max_days') }} أيام دون رد يُحال للإدارة.</p>
        @if ($isClient)
            <form method="POST" action="{{ route('contracts.pause.action', [$contract, $active, 'answer']) }}" enctype="multipart/form-data">
                @csrf
                <textarea name="response" rows="2" maxlength="3000" class="form-control mb-2" placeholder="ردك على طلب المستقل">{{ old('response') }}</textarea>
                @error('response')<div class="rf-field-error">{{ $message }}</div>@enderror
                <input type="file" name="files[]" multiple class="form-control mb-2" accept="{{ collect(\App\Models\ContractDelivery::FILE_EXTENSIONS)->map(fn ($e) => '.' . $e)->implode(',') }}">
                @error('files.*')<div class="rf-field-error">{{ $message }}</div>@enderror
                <button class="btn rf-btn-accept">أرسلت المطلوب — استئناف العدّاد</button>
            </form>
            <details class="mt-2">
                <summary class="rf-link-btn">هذا الطلب غير حقيقي؟ اعترض</summary>
                <form method="POST" action="{{ route('contracts.pause.action', [$contract, $active, 'object']) }}">
                    @csrf
                    <textarea name="reason" rows="2" maxlength="2000" class="form-control mb-2" placeholder="لماذا ترى أن الطلب غير حقيقي؟"></textarea>
                    <button class="btn rf-btn-danger-text">إرسال الاعتراض (يُستأنف العدّاد دون تمديد)</button>
                </form>
            </details>
        @elseif ($isFreelancer)
            <form method="POST" action="{{ route('contracts.pause.action', [$contract, $active, 'resume']) }}">
                @csrf
                <button class="btn rf-btn-secondary">وصلني المطلوب — استئناف العدّاد</button>
            </form>
        @endif
    </div>
@elseif ($isFreelancer && $running && ! $pending)
    <details class="rf-pause-form" @if ($errors->has('request')) open @endif>
        <summary>تحتاج شيئًا من العميل؟ أوقف العدّاد</summary>
        <form method="POST" action="{{ route('contracts.pause', [$contract, $stage]) }}">
            @csrf
            <textarea name="request" rows="2" maxlength="2000" class="form-control mb-2" placeholder="بالتحديد: مثال «شعار الشركة بصيغة PNG وبيانات الدخول للاستضافة»">{{ old('request') }}</textarea>
            @error('request')<div class="rf-field-error">{{ $message }}</div>@enderror
            <button class="btn rf-btn-primary">إيقاف العدّاد وإرسال الطلب للعميل</button>
        </form>
    </details>
@endif

@php $past = $stage->pauses->where('status', '!=', P::STATUS_ACTIVE); @endphp
@if ($past->isNotEmpty())
    <details class="rf-pause-history">
        <summary>إيقافات العدّاد ({{ $past->count() }})</summary>
        <ul>
            @foreach ($past as $pause)
                <li>{{ $pause->started_at->format('Y-m-d') }} · {{ $pause->statusLabel() }}
                    @if ($pause->extended_minutes) · مُدّ الموعد {{ $pause->extended_minutes >= 1440 ? round($pause->extended_minutes / 1440, 1) . ' يوم' : ceil($pause->extended_minutes / 60) . ' ساعة' }}@endif
                    <div class="rf-muted">الطلب: {{ $pause->request }}</div>
                    @if ($pause->client_response)<div class="rf-muted">رد العميل: {{ $pause->client_response }}</div>@endif
                    @if ($pause->objection)<div class="rf-muted">اعتراض العميل: {{ $pause->objection }}</div>@endif
                    @foreach ($pause->files as $file)<a href="{{ route('contracts.file', [$contract, $file]) }}" class="d-block"><i class="bi bi-paperclip"></i> {{ $file->original_name }}</a>@endforeach
                </li>
            @endforeach
        </ul>
    </details>
@endif
