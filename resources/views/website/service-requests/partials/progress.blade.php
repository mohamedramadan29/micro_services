{{--
    State of a request after an offer was chosen and paid.
    Expects $serviceRequest and $viewer ('client' | 'freelancer').
--}}
@php
    $offer = $serviceRequest->acceptedOffer;
    $payment = $serviceRequest->activePayment();
    $completed = $serviceRequest->status === \App\Models\ServiceRequest::STATUS_COMPLETED;
    $steps = [
        ['تم الدفع', true],
        ['قيد التنفيذ', true],
        ['تم التسليم', (bool) $serviceRequest->delivered_at || $completed],
        ['تم التأكيد', $completed],
    ];
@endphp
<ol class="rf-steps" aria-label="مراحل الطلب">
    @foreach ($steps as [$label, $done])
        <li class="{{ $done ? 'done' : '' }}">{{ $label }}</li>
    @endforeach
</ol>

@if ($viewer === 'client')
    @if ($completed)
        <div class="rf-notice rf-notice-success">
            <strong>تم التنفيذ</strong> {{ $serviceRequest->completed_at?->format('Y-m-d') }}، وتم تحويل مستحقات المستقل {{ $offer?->freelancer?->name }}.
        </div>
    @else
        <div class="rf-notice rf-notice-success">
            <p class="mb-1"><strong>اخترت عرض {{ $offer?->freelancer?->name }}</strong>: {{ \App\Support\Money::show($payment?->amount) }} خلال {{ $offer?->duration_days }} يوم.</p>
            <p class="mb-0">المبلغ محجوز لدى نفّذها، ولا يُحوّل للمستقل إلا بعد تأكيدك استلام العمل.</p>
        </div>
        @if ($serviceRequest->delivered_at)
            <div class="rf-notice">
                <p class="mb-1"><strong>سلّم المستقل العمل</strong> {{ $serviceRequest->delivered_at->diffForHumans() }}.</p>
                @if ($serviceRequest->delivery_note)
                    <p class="rf-problem mb-0">{{ $serviceRequest->delivery_note }}</p>
                @endif
            </div>
        @endif
        <div class="rf-offer-actions">
            @if ($offer)
                <a href="{{ route('offers.chat', $offer) }}" class="rf-link-btn"><i class="bi bi-chat-dots"></i> محادثة المستقل</a>
            @endif
            <form method="POST" action="{{ route('requests.complete', $serviceRequest) }}"
                onsubmit="return confirm('تأكيد استلام العمل كاملًا؟ سيُحوّل المبلغ للمستقل ولا يمكن التراجع.')">
                @csrf
                <button type="submit" class="btn rf-btn-accept"><i class="bi bi-check2-all"></i> تأكيد استلام العمل</button>
            </form>
        </div>
        @unless ($serviceRequest->delivered_at)
            <p class="rf-muted mt-2">أكّد فقط بعد استلامك العمل كاملًا. لم يُعلن المستقل التسليم بعد.</p>
        @endunless
    @endif
@else
    @if ($completed)
        <div class="rf-notice rf-notice-success">
            <strong>أكّد العميل الاستلام</strong>، وأُضيف {{ \App\Support\Money::show($payment?->freelancer_amount) }} إلى
            <a href="{{ url('balance') }}">رصيدك</a>.
        </div>
    @else
        <div class="rf-notice rf-notice-success">
            @php $totals = $serviceRequest->moneyTotals(); @endphp
            <p class="mb-1"><strong>تم اختيار عرضك</strong>، ودفع العميل {{ \App\Support\Money::show($totals['amount']) }} (محجوز لدى نفّذها).</p>
            <p class="mb-0">ستحصل على {{ \App\Support\Money::show($totals['freelancer']) }} بعد خصم عمولة الموقع {{ $totals['rate'] }}% عند تأكيد العميل الاستلام.</p>
        </div>
        @if ($serviceRequest->delivered_at)
            <div class="rf-notice">سلّمت العمل {{ $serviceRequest->delivered_at->diffForHumans() }}، بانتظار تأكيد العميل.</div>
        @else
            <form method="POST" action="{{ route('freelancer.requests.deliver', $serviceRequest) }}" class="rf-deliver">
                @csrf
                <label class="rf-field-label" for="delivery_note">ملاحظة التسليم (اختياري)</label>
                <textarea id="delivery_note" name="delivery_note" rows="3" maxlength="3000" class="form-control" placeholder="ما الذي سلّمته؟ روابط أو تعليمات للعميل">{{ old('delivery_note') }}</textarea>
                @error('delivery_note')<div class="rf-field-error">{{ $message }}</div>@enderror
                <div class="rf-offer-actions">
                    <button type="submit" class="btn rf-btn-accept"><i class="bi bi-box-arrow-up"></i> تسليم العمل</button>
                </div>
            </form>
        @endif
    @endif
@endif
