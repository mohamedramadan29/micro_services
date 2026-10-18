{{--
    Changes after work started (client decision 2026-10-16): the client proposes, the chosen
    freelancer approves; a higher price is paid before it applies, a lower one returns to the
    client's balance. Expects $serviceRequest and $viewer ('client' | 'freelancer').
--}}
@php
    use App\Models\ServiceRequestChange as Change;
    use App\Support\Money;
    $changes = $serviceRequest->changes()->get();
    $open = $changes->first(fn ($c) => $c->isOpen());
    $canPropose = $viewer === 'client' && $serviceRequest->isInProgress() && ! $open;
    $balance = (float) (auth()->user()?->balance ?? 0);
@endphp
@if ($changes->isNotEmpty() || $canPropose)
    <div class="rf-changes" id="changes">
        <h3 class="rf-changes-title">تعديلات بعد بدء التنفيذ</h3>
        <p class="rf-muted mb-2">السعر المتفق عليه الآن: <strong>{{ Money::show($serviceRequest->currentPrice()) }}</strong>
            @if ($serviceRequest->deadline) · الموعد: <strong>{{ $serviceRequest->deadline->format('Y-m-d') }}</strong>@endif</p>

        @foreach ($changes as $change)
            @php $diff = $change->difference(); @endphp
            <div class="rf-change rf-change-{{ $change->status }}">
                <div class="rf-change-head">
                    <span class="rf-status">{{ $change->statusLabel() }}</span>
                    <span class="rf-muted">{{ $change->created_at->format('Y-m-d H:i') }}</span>
                </div>
                <p class="rf-problem mb-1">{{ $change->description }}</p>
                @if ($change->reason)<p class="rf-muted mb-1">السبب: {{ $change->reason }}</p>@endif
                <ul class="rf-change-terms">
                    @if ($diff != 0)
                        <li>السعر: {{ Money::show($change->old_price) }} ← <strong>{{ Money::show($change->new_price) }}</strong>
                            ({{ $diff > 0 ? 'زيادة ' . Money::show($diff) : 'تخفيض ' . Money::show(-$diff) . ' يعود لرصيد العميل' }})</li>
                    @endif
                    @if ($change->new_deadline && $change->new_deadline->toDateString() !== $change->old_deadline?->toDateString())
                        <li>الموعد: {{ $change->old_deadline?->format('Y-m-d') ?? '—' }} ← <strong>{{ $change->new_deadline->format('Y-m-d') }}</strong></li>
                    @endif
                </ul>
                @if ($change->status === Change::STATUS_REJECTED && $change->response_note)
                    <p class="rf-muted mb-0">رد المستقل: {{ $change->response_note }}</p>
                @endif

                @if ($viewer === 'client' && $change->isOpen())
                    <div class="rf-offer-actions">
                        @if ($change->status === Change::STATUS_AWAITING_PAYMENT)
                            @if ($balance + 0.001 >= $diff)
                                <form method="POST" action="{{ route('requests.changes.action', [$serviceRequest, $change, 'pay-balance']) }}"
                                    onsubmit="return confirm(@js('خصم ' . Money::format($diff) . ' من رصيدك واعتماد التعديل؟'))">@csrf
                                    <button class="btn rf-btn-accept">ادفع الفرق من رصيدك ({{ Money::show($diff) }})</button></form>
                            @endif
                            <form method="POST" action="{{ route('requests.changes.action', [$serviceRequest, $change, 'pay']) }}">@csrf
                                <button class="btn rf-btn-primary">ادفع الفرق عبر EasyKash ({{ Money::show($diff) }})</button></form>
                        @endif
                        <form method="POST" action="{{ route('requests.changes.action', [$serviceRequest, $change, 'cancel']) }}" onsubmit="return confirm('إلغاء طلب التعديل؟')">@csrf
                            <button class="btn rf-btn-danger-text">إلغاء طلب التعديل</button></form>
                    </div>
                    @if ($change->status === Change::STATUS_AWAITING_PAYMENT)
                        <p class="rf-muted mt-1 mb-0">رصيدك في الموقع: {{ Money::show($balance) }}. يُعتمد التعديل بمجرد دفع الفرق، ويبقى المبلغ لدى نفّذها حتى تؤكد استلام العمل.</p>
                    @endif
                @elseif ($viewer === 'freelancer' && $change->status === Change::STATUS_PENDING)
                    <form method="POST" action="{{ route('freelancer.changes.respond', [$serviceRequest, $change]) }}" class="mt-2">
                        @csrf
                        <textarea name="note" rows="2" maxlength="1000" class="form-control mb-2" placeholder="ملاحظة للعميل (مطلوبة عند الرفض)">{{ old('note') }}</textarea>
                        <div class="rf-offer-actions">
                            <button name="decision" value="accept" class="btn rf-btn-accept">موافقة على التعديل</button>
                            <button name="decision" value="reject" class="btn rf-btn-danger-text">رفض</button>
                        </div>
                        @if ($diff > 0)<p class="rf-muted mt-1 mb-0">بعد موافقتك يدفع العميل الفرق، ثم يُعتمد التعديل.</p>@endif
                        @if ($diff < 0)<p class="rf-muted mt-1 mb-0">بموافقتك يعود فرق التخفيض لرصيد العميل، وتُحسب مستحقاتك على السعر الجديد.</p>@endif
                    </form>
                @endif
            </div>
        @endforeach

        @if ($canPropose)
            <details class="rf-change-new" @if ($errors->has('description') || old('description')) open @endif>
                <summary>طلب تعديل على العمل أو الموعد أو السعر</summary>
                <form method="POST" action="{{ route('requests.changes.store', $serviceRequest) }}">
                    @csrf
                    <label class="rf-field-label" for="ch-desc">ما التعديل المطلوب؟ <span class="text-danger">*</span></label>
                    <textarea id="ch-desc" name="description" rows="3" maxlength="3000" class="form-control">{{ old('description') }}</textarea>
                    @error('description')<div class="rf-field-error">{{ $message }}</div>@enderror
                    <div class="row mt-2">
                        <div class="col-md-6">
                            <label class="rf-field-label" for="ch-price">السعر الجديد (اختياري)</label>
                            <input type="number" id="ch-price" name="new_price" min="1" step="0.01" class="form-control" value="{{ old('new_price') }}" placeholder="{{ $serviceRequest->currentPrice() }}">
                            @error('new_price')<div class="rf-field-error">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="rf-field-label" for="ch-deadline">الموعد الجديد (اختياري)</label>
                            <input type="date" id="ch-deadline" name="new_deadline" min="{{ now()->toDateString() }}" class="form-control" value="{{ old('new_deadline') }}">
                            @error('new_deadline')<div class="rf-field-error">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <label class="rf-field-label mt-2" for="ch-reason">السبب (اختياري)</label>
                    <input type="text" id="ch-reason" name="reason" maxlength="1000" class="form-control" value="{{ old('reason') }}">
                    <p class="rf-muted mt-2 mb-2">يصل الطلب للمستقل ليوافق أو يرفض. إن رفعتَ السعر تدفع الفرق بعد موافقته، وإن خفّضته يعود الفرق لرصيدك في الموقع.</p>
                    <button class="btn rf-btn-primary">إرسال طلب التعديل</button>
                </form>
            </details>
        @endif
    </div>
@endif
