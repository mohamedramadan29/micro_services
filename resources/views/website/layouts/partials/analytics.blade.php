{{-- Journey analytics (client file «تعديلات», item 08): consent first; nothing is recorded before «موافق». --}}
@php
    $consent = request()->cookie(\App\Services\Analytics\Tracker::CONSENT_COOKIE);
    $abandonPayment = session('abandon_payment');
@endphp
@if ($consent === null)
    <div class="rf-consent" role="dialog" aria-live="polite" aria-label="الخصوصية" dir="rtl">
        <p>نستخدم إحصائيات مجهولة داخل الموقع فقط لنعرف كيف يصل الزوار وأين يتوقفون، دون اسم أو رقم هاتف.
            <a href="{{ url('privacy-policy') }}">سياسة الخصوصية</a></p>
        <form method="POST" action="{{ route('analytics.consent') }}">
            @csrf
            <button name="accept" value="1" class="rf-sv-btn rf-sv-btn-primary">موافق</button>
            <button name="accept" value="0" class="rf-sv-btn rf-sv-btn-outline">لا شكرًا</button>
        </form>
    </div>
@endif

@if ($abandonPayment && $consent === '1')
    <div class="rf-consent rf-abandon" role="dialog" aria-label="لماذا لم تكمل الدفع؟" dir="rtl">
        <form method="POST" action="{{ route('analytics.abandon-reason') }}">
            @csrf
            <input type="hidden" name="payment" value="{{ $abandonPayment }}">
            <p><strong>ساعدنا نفهم (اختياري):</strong> لماذا لم تكمل الدفع؟</p>
            <div class="rf-abandon-options">
                @foreach (\App\Http\Controllers\front\AnalyticsController::ABANDON_REASONS as $key => $label)
                    <label><input type="radio" name="reason" value="{{ $key }}" required> <span>{{ $label }}</span></label>
                @endforeach
            </div>
            <input type="text" name="note" maxlength="500" class="form-control" placeholder="تفاصيل إضافية (اختياري)">
            <div class="rf-st-actions">
                <button class="rf-sv-btn rf-sv-btn-primary">إرسال</button>
                <button type="button" class="rf-sv-btn rf-sv-btn-outline" onclick="this.closest('.rf-abandon').remove()">تخطي</button>
            </div>
        </form>
    </div>
@endif

@if ($consent === '1')
    <script>
        // «بدء النموذج»: once per form, when the visitor first types or picks something.
        document.querySelectorAll('form[data-track-start]').forEach(function (form) {
            var sent = false;
            var send = function () {
                if (sent) return;
                sent = true;
                var body = new FormData();
                body.append('_token', @json(csrf_token()));
                body.append('form', form.dataset.trackStart);
                var service = form.dataset.trackService || (form.querySelector('[name="sub_category_id"]') || {}).value;
                if (service) body.append('service', service);
                fetch(@json(route('analytics.form-start')), { method: 'POST', body: body, credentials: 'same-origin', keepalive: true }).catch(function () {});
            };
            form.addEventListener('input', send, { once: true });
            form.addEventListener('change', send, { once: true });
        });
    </script>
@endif
