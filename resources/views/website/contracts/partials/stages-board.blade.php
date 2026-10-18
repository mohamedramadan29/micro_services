{{--
    Service 3, phase 3: the stages of an active (or completed) contract — funding, delivery,
    review. Expects $contract (stages.deliveries.files loaded), $current, $isClient, $isFreelancer.
--}}
@php
    use App\Models\ContractDelivery as D;
    use App\Models\ContractStage as S;
    use App\Support\Money;
    $totals = $contract->totals();
    $unfunded = $contract->stages->where('status', S::STATUS_UNFUNDED);
    $setting = \App\Models\admin\Setting::first();
    $feePercent = (float) ($setting?->stage_fee_percent ?? 0);
    $feeFixed = (float) ($setting?->stage_fee_fixed ?? 0);
    $balance = (float) (auth()->user()?->balance ?? 0);
    $frozen = (bool) $contract->disputes->first(fn ($d) => $d->isOpen());
@endphp
<div id="stages">
    <h2 class="rf-section-title mt-3">مراحل المشروع</h2>
    <div class="rf-contract-totals">
        <span>الإجمالي <strong>{{ Money::show($totals['total']) }}</strong></span>
        <span>لدى نفّذها <strong>{{ Money::show($totals['held']) }}</strong></span>
        <span>{{ $isFreelancer ? 'وصل رصيدك' : 'حُوّل للمستقل' }} <strong>{{ Money::show($totals['released']) }}</strong></span>
        @if ($totals['refunded'] > 0)<span>أُعيد للعميل <strong>{{ Money::show($totals['refunded']) }}</strong></span>@endif
        @if ($expectedEnd = $contract->expectedEnd())<span>التسليم المتوقع للمشروع <strong>{{ $expectedEnd->format('Y-m-d') }}</strong></span>@endif
        @if ($contract->status === \App\Models\Contract::STATUS_COMPLETED && $contract->completed_at)<span>اكتمل في <strong>{{ $contract->completed_at->format('Y-m-d') }}</strong></span>@endif
    </div>

    @if ($isClient && $unfunded->isNotEmpty() && $contract->status === \App\Models\Contract::STATUS_ACTIVE)
        <form method="POST" action="{{ route('contracts.fund', $contract) }}" class="rf-fund">
            @csrf
            <strong>تمويل المراحل</strong>
            <p class="rf-muted mb-2">موّل المرحلة التالية، أو أكثر من مرحلة مقدمًا. يبقى المبلغ لدى نفّذها حتى تعتمد المرحلة.</p>
            @foreach ($unfunded as $stage)
                <label class="rf-fund-row"><input type="checkbox" name="stages[]" value="{{ $stage->id }}" data-amount="{{ (float) $stage->amount }}" @checked($loop->first)>
                    <span>{{ $stage->sort_order }}. {{ $stage->title }} — {{ Money::show($stage->amount) }}</span></label>
            @endforeach
            @error('stages')<div class="rf-field-error">{{ $message }}</div>@enderror
            <div class="rf-offer-actions">
                <button name="source" value="balance" class="btn rf-btn-accept">من رصيدي ({{ Money::show($balance) }})</button>
                <button name="source" value="easykash" class="btn rf-btn-primary">ادفع عبر EasyKash</button>
            </div>
            @if ($feePercent > 0 || $feeFixed > 0)
                <p class="rf-muted mt-1 mb-0">الدفع عبر EasyKash تُضاف إليه رسوم {{ $feePercent > 0 ? $feePercent . '%' : '' }}{{ $feePercent > 0 && $feeFixed > 0 ? ' + ' : '' }}{{ $feeFixed > 0 ? Money::format($feeFixed) : '' }} ولا تُرد. الدفع من الرصيد بلا رسوم.</p>
            @endif
        </form>
    @endif

    @foreach ($contract->stages as $stage)
        @php
            $isCurrent = $current && $current->id === $stage->id;
            $pending = $stage->deliveries->firstWhere('status', D::STATUS_SUBMITTED);
            $left = $stage->revisionsLeft();
        @endphp
        <div class="rf-stage {{ $isCurrent ? 'is-current' : '' }} rf-stage-{{ $stage->status }}">
            <div class="rf-change-head">
                <strong>{{ $stage->sort_order }}. {{ $stage->title }}</strong>
                <span class="rf-status {{ $isCurrent && $frozen ? 'rf-status-frozen' : '' }}">{{ $isCurrent && $frozen ? 'معلّقة بسبب خلاف' : (! $isCurrent && $stage->status === S::STATUS_FUNDED && ! $stage->started_at ? 'مموّلة — تبدأ بعد اعتماد السابقة' : $stage->statusLabel()) }}</span>
            </div>
            <p class="rf-muted mb-1" style="white-space: pre-line">{{ $stage->deliverable }}</p>
            @if ($stage->acceptance)<p class="rf-acceptance"><i class="bi bi-check2-square"></i> معيار القبول: {{ $stage->acceptance }}</p>@endif
            <p class="rf-stage-meta">
                {{ Money::show($stage->amount) }} · {{ $stage->duration_days }} يوم
                · التعديلات: {{ $stage->revisions === null ? 'غير محدود' : $stage->revisions_used . ' من ' . $stage->revisions }}
                @if ($stage->started_at && ! $stage->isSettled()) · بدأت {{ $stage->started_at->format('Y-m-d') }} · موعد التسليم <strong>{{ $stage->due_at?->format('Y-m-d') }}</strong>@endif
                @if ($stage->status === S::STATUS_RELEASED) · {{ $isFreelancer ? 'وصل رصيدك' : 'حُوّل للمستقل' }} {{ Money::show($stage->freelancer_amount) }} (بعد عمولة {{ (float) $stage->commission_rate }}%)@endif
            </p>
            @if ($stage->status === S::STATUS_FUNDING && ($isClient || $isAdmin))
                <form method="POST" action="{{ route('contracts.fund.cancel', [$contract, $stage]) }}" class="mb-2" onsubmit="return confirm('إلغاء الدفع المعلّق لهذه المرحلة؟ إن كان الدفع قد تم فعلًا يُضاف المبلغ إلى رصيدك عند وصوله.')">
                    @csrf
                    <span class="rf-muted">لم يكتمل الدفع عبر EasyKash؟</span>
                    <button class="btn rf-btn-outline btn-sm">إلغاء الدفع المعلّق وإعادة التمويل</button>
                </form>
            @endif
            @include('website.contracts.partials.stage-clock')

            {{-- Deliveries, newest first. --}}
            @foreach ($stage->deliveries as $delivery)
                <div class="rf-delivery rf-delivery-{{ $delivery->status }}">
                    <div class="rf-change-head"><span>تسليم {{ $delivery->created_at->format('Y-m-d H:i') }}</span><span class="rf-status">{{ $delivery->statusLabel() }}</span></div>
                    <p class="rf-problem mb-1">{{ $delivery->note }}</p>
                    @foreach ((array) $delivery->links as $link)<a href="{{ $link }}" target="_blank" rel="noopener nofollow" class="d-block" dir="ltr">{{ $link }}</a>@endforeach
                    @foreach ($delivery->files as $file)<a href="{{ route('contracts.file', [$contract, $file]) }}" class="d-block"><i class="bi bi-paperclip"></i> {{ $file->original_name }}</a>@endforeach
                    @if ($delivery->client_note)<p class="rf-muted mb-0">التعديل المطلوب: {{ $delivery->client_note }}</p>@endif
                    @if ($delivery->status === D::STATUS_SUBMITTED && $delivery->review_due_at)
                        <p class="rf-muted mb-0">المراجعة حتى {{ $delivery->review_due_at->format('Y-m-d') }}</p>
                    @endif

                    @if ($isClient && $delivery->status === D::STATUS_SUBMITTED)
                        <div class="rf-agreement-actions">
                            <form method="POST" action="{{ route('contracts.review', [$contract, $delivery]) }}"
                                onsubmit="return confirm(@js('اعتماد المرحلة «' . $stage->title . '»؟ سيُحوّل مبلغها للمستقل ولا يمكن التراجع.'))">
                                @csrf
                                <button name="decision" value="approve" class="btn rf-btn-accept">اعتماد المرحلة وتحويل مبلغها</button>
                            </form>
                            @if ($left === 0)
                                <p class="rf-muted mb-0">انتهت التعديلات المتفق عليها لهذه المرحلة. يمكنك الاتفاق مع المستقل على ملحق، أو فتح خلاف لتحسمه الإدارة.</p>
                            @else
                                <details>
                                    <summary class="rf-link-btn">اطلب تعديلًا ({{ $left === null ? 'غير محدود' : 'متبقٍ ' . $left }})</summary>
                                    <form method="POST" action="{{ route('contracts.review', [$contract, $delivery]) }}">
                                        @csrf
                                        <textarea name="note" rows="3" maxlength="3000" class="form-control mb-2" placeholder="ما الذي يحتاج تعديلًا داخل نطاق المرحلة؟"></textarea>
                                        <button name="decision" value="revision" class="btn rf-btn-primary">إرسال طلب التعديل</button>
                                    </form>
                                </details>
                            @endif
                        </div>
                    @endif
                </div>
            @endforeach

            @if ($isFreelancer && $isCurrent && $stage->status === S::STATUS_FUNDED && $stage->started_at && ! $pending)
                <details class="rf-deliver-form" @if ($errors->hasAny(['note', 'links', 'files', 'files.*'])) open @endif>
                    <summary>تسليم المرحلة</summary>
                    <form method="POST" action="{{ route('contracts.deliver', [$contract, $stage]) }}" enctype="multipart/form-data">
                        @csrf
                        <label class="rf-field-label">ما الذي سلّمته؟</label>
                        <textarea name="note" rows="3" maxlength="3000" class="form-control">{{ old('note') }}</textarea>
                        @error('note')<div class="rf-field-error">{{ $message }}</div>@enderror
                        <label class="rf-field-label mt-2">روابط (اختياري، كل رابط في سطر — للملفات الكبيرة أو المعاينة)</label>
                        <textarea name="links" rows="2" class="form-control" dir="ltr" placeholder="https://drive.google.com/…">{{ old('links') }}</textarea>
                        @error('links')<div class="rf-field-error">{{ $message }}</div>@enderror
                        <label class="rf-field-label mt-2">ملفات (حتى {{ \App\Services\Contracts\ContractPolicy::number('max_files') }}، {{ \App\Services\Contracts\ContractPolicy::number('file_max_mb') }} ميجابايت لكل ملف)</label>
                        <input type="file" name="files[]" multiple class="form-control" accept="{{ collect(D::FILE_EXTENSIONS)->map(fn ($e) => '.' . $e)->implode(',') }}">
                        @error('files')<div class="rf-field-error">{{ $message }}</div>@enderror
                        @error('files.*')<div class="rf-field-error">{{ $message }}</div>@enderror
                        <button class="btn rf-btn-primary mt-2">تسليم للعميل</button>
                    </form>
                </details>
            @elseif ($isFreelancer && $isCurrent && $stage->status === S::STATUS_UNFUNDED)
                <p class="rf-muted mb-0">بانتظار تمويل العميل لهذه المرحلة قبل البدء.</p>
            @endif
        </div>
    @endforeach
</div>
