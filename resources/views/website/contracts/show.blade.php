@extends('website.layouts.master')
@php
    use App\Models\Contract;
    use App\Models\ContractAgreement as A;
    use App\Support\Money;
    $offer = $contract->request?->acceptedOffer;
    $inAgreement = $contract->status === Contract::STATUS_AGREEMENT;
    $canPropose = $isFreelancer && $inAgreement;
    // The proposal form starts from old input, else the latest version, else the chosen offer.
    $base = $latest ?? null;
    // ?template=N (a stage template of the admin) replaces the starting stages.
    $template = request()->has('template') ? (int) request('template') : null;
    $formStages = old('stages', $base && $template === null ? array_map(fn ($s) => $s + ['unlimited_revisions' => $s['revisions'] === null], $base->stages)
        : A::defaultStages((float) ($offer?->price ?? 0), (int) ($offer?->duration_days ?? 7), $template));
@endphp
@section('title')
    {{ $contract->number }} — {{ $contract->title }}
@endsection
@section('content')
    <section class="gray-bg text-right rf-page" dir="rtl">
        <div class="container">
            <div class="rf-card">
                <div class="rf-request-head">
                    <div>
                        <span class="rf-tag-badge rf-tag-staged">مشروعك ما بيوقف</span>
                        <h1 class="rf-request-title">{{ $contract->title }}</h1>
                        <span class="rf-muted">{{ $contract->number }} · العميل: {{ $contract->client?->name }} · المستقل: {{ $contract->freelancer?->name }}
                            @if ($contract->request) · <a href="{{ $isFreelancer ? route('freelancer.requests.show', $contract->request) : route('requests.show', $contract->request) }}">الطلب {{ $contract->request->number }}</a>@endif
                        </span>
                    </div>
                    <span class="rf-status">{{ match ($contract->status) { Contract::STATUS_AGREEMENT => 'الاتفاق قيد الإعداد', Contract::STATUS_ACTIVE => 'قيد التنفيذ', Contract::STATUS_COMPLETED => 'مكتمل', Contract::STATUS_REPLACED => 'تغيّر المستقل', default => 'ملغي' } }}</span>
                </div>

                <div class="rf-notice">موعد كل مرحلة يبدأ عند تمويلها (بعد الموافقة على الاتفاق). إذا احتاج المستقل موادًا أو ردًا من العميل يوقف العدّاد حتى يصله ما طلب، ويُمدّ الموعد بمدة الإيقاف. يبقى مبلغ كل مرحلة لدى نفّذها حتى يعتمدها العميل. <a href="{{ route('policy.disputes') }}" target="_blank">سياسة الخلافات وحماية الحقوق</a></div>

                @if (! $latest)
                    <div class="rf-notice rf-notice-warning">
                        {{ $isFreelancer ? 'اقترح الاتفاق ومراحل الدفع والتسليم ليراجعه العميل.' : 'بانتظار أن يقترح المستقل الاتفاق ومراحله.' }}
                    </div>
                @else
                    <div class="rf-agreement" id="agreement">
                        <div class="rf-change-head">
                            <h2 class="rf-section-title mb-0">الاتفاق — نسخة {{ $latest->version }}</h2>
                            <span class="rf-status">{{ $latest->statusLabel() }}</span>
                        </div>
                        <dl class="rf-answers">
                            <dt>نطاق العمل</dt><dd class="rf-problem">{{ $latest->scope }}</dd>
                            @if ($latest->out_of_scope)<dt>خارج النطاق</dt><dd class="rf-problem">{{ $latest->out_of_scope }}</dd>@endif
                            @if ($latest->client_materials)<dt>مطلوب من العميل</dt><dd class="rf-problem">{{ $latest->client_materials }}</dd>@endif
                            @if ($latest->freelancer_note)<dt>ملاحظة المستقل</dt><dd class="rf-problem">{{ $latest->freelancer_note }}</dd>@endif
                        </dl>
                        <div class="table-responsive">
                            <table class="table rf-stages-table">
                                <thead><tr><th>#</th><th>المرحلة وما يُسلَّم</th><th>المدة</th><th>المبلغ</th><th>التعديلات</th></tr></thead>
                                <tbody>
                                    @foreach ($latest->stages as $i => $stage)
                                        <tr>
                                            <td>{{ $i + 1 }}</td>
                                            <td><strong>{{ $stage['title'] }}</strong><div class="rf-muted" style="white-space: pre-line">{{ $stage['deliverable'] }}</div>@if (! empty($stage['acceptance']))<div class="rf-acceptance"><i class="bi bi-check2-square"></i> معيار القبول: {{ $stage['acceptance'] }}</div>@endif</td>
                                            <td>{{ $stage['duration_days'] }} يوم</td>
                                            <td>{{ Money::show($stage['amount']) }}</td>
                                            <td>{{ $stage['revisions'] === null ? 'غير محدود' : $stage['revisions'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot><tr><th></th><th>الإجمالي</th><th>{{ $latest->totalDays() }} يوم</th><th>{{ Money::show($latest->total) }}</th><th></th></tr></tfoot>
                            </table>
                        </div>
                        <p class="rf-muted mb-2">التعديل داخل نطاق المرحلة فقط؛ ما يخرج عن النطاق يكون ملحقًا يوافق عليه الطرفان.</p>
                        @if ($latest->status === A::STATUS_CHANGES_REQUESTED && $latest->client_note)
                            <div class="rf-notice rf-notice-warning"><strong>التعديلات التي طلبها العميل:</strong> {{ $latest->client_note }}</div>
                        @endif
                        @if ($latest->status === A::STATUS_APPROVED)
                            <p class="rf-muted mb-0">وافق المستقل {{ $latest->freelancer_approved_at?->format('Y-m-d H:i') }} · وافق العميل {{ $latest->client_approved_at?->format('Y-m-d H:i') }}</p>
                        @endif

                        @if ($isClient && $inAgreement && $latest->status === A::STATUS_PROPOSED)
                            <div class="rf-agreement-actions">
                                <form method="POST" action="{{ route('contracts.agreement.respond', [$contract, $latest]) }}" class="rf-agreement-approve">
                                    @csrf
                                    <label class="rf-confirm"><input type="checkbox" name="confirm" value="1" required> راجعت نطاق العمل والمراحل ومبالغها ومواعيدها، وقرأت <a href="{{ route('policy.disputes') }}" target="_blank">سياسة الخلافات</a>، وأوافق عليها.</label>
                                    @error('confirm')<div class="rf-field-error">{{ $message }}</div>@enderror
                                    <button name="decision" value="approve" class="btn rf-btn-accept">راجعت الاتفاق وأوافق</button>
                                </form>
                                <details @if ($errors->has('note')) open @endif>
                                    <summary class="rf-link-btn">اطلب تعديل الاتفاق</summary>
                                    <form method="POST" action="{{ route('contracts.agreement.respond', [$contract, $latest]) }}">
                                        @csrf
                                        <textarea name="note" rows="3" maxlength="3000" class="form-control mb-2" placeholder="ما الذي تريد تغييره؟ (المراحل، المبالغ، المدد، النطاق…)">{{ old('note') }}</textarea>
                                        @error('note')<div class="rf-field-error">{{ $message }}</div>@enderror
                                        <button name="decision" value="changes" class="btn rf-btn-primary">إرسال طلب التعديل</button>
                                    </form>
                                </details>
                            </div>
                        @elseif ($isFreelancer && $inAgreement && $latest->status === A::STATUS_PROPOSED)
                            <p class="rf-muted mb-0">بانتظار رد العميل. يمكنك إرسال نسخة معدّلة تحل محل هذه.</p>
                        @elseif ($isClient && $inAgreement && $latest->status === A::STATUS_CHANGES_REQUESTED)
                            <p class="rf-muted mb-0">بانتظار نسخة معدّلة من المستقل.</p>
                        @endif
                    </div>
                @endif

                @if ($canPropose)
                    <details class="rf-agreement-form" @if (! $latest || $latest->status === A::STATUS_CHANGES_REQUESTED || $errors->any()) open @endif>
                        <summary>{{ $latest ? 'إرسال نسخة معدّلة من الاتفاق' : 'اقتراح الاتفاق' }}</summary>
                        <form method="POST" action="{{ route('contracts.agreement.propose', $contract) }}" id="agreement-form">
                            @csrf
                            <label class="rf-field-label" for="ag-scope">نطاق العمل: ما المطلوب بالتحديد <span class="text-danger">*</span></label>
                            <textarea id="ag-scope" name="scope" rows="4" maxlength="5000" class="form-control">{{ old('scope', $base?->scope ?? $offer?->scope) }}</textarea>
                            @error('scope')<div class="rf-field-error">{{ $message }}</div>@enderror
                            <div class="row mt-2">
                                <div class="col-md-6">
                                    <label class="rf-field-label" for="ag-out">خارج النطاق (اختياري)</label>
                                    <textarea id="ag-out" name="out_of_scope" rows="3" maxlength="3000" class="form-control">{{ old('out_of_scope', $base?->out_of_scope) }}</textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="rf-field-label" for="ag-materials">المطلوب من العميل (ملفات، نصوص، صلاحيات…)</label>
                                    <textarea id="ag-materials" name="client_materials" rows="3" maxlength="3000" class="form-control">{{ old('client_materials', $base?->client_materials) }}</textarea>
                                </div>
                            </div>

                            <h3 class="rf-section-title mt-3">المراحل</h3>
                            <p class="rf-muted mb-2">ابدأ من قالب:
                                @foreach (\App\Services\Contracts\ContractPolicy::templates() as $t => $tpl)
                                    <a href="{{ route('contracts.show', ['contract' => $contract, 'template' => $t]) }}#agreement-form" class="rf-tab {{ $template === $t ? 'active' : '' }}">{{ $tpl['name'] }}</a>
                                @endforeach
                                <span class="d-block small">يقسّم سعر العرض ومدته على المراحل؛ عدّلها كما تريد.</span>
                            </p>
                            <div id="ag-stages">
                                @foreach ($formStages as $i => $stage)
                                    @include('website.contracts.partials.stage-row', ['i' => $i, 'stage' => $stage])
                                @endforeach
                            </div>
                            <template id="ag-stage-template">@include('website.contracts.partials.stage-row', ['i' => '__i__', 'stage' => ['revisions' => \App\Services\Contracts\ContractPolicy::number('default_revisions')]])</template>
                            @error('stages')<div class="rf-field-error">{{ $message }}</div>@enderror
                            <div class="rf-offer-actions">
                                <button type="button" class="btn rf-btn-secondary" id="ag-add">+ إضافة مرحلة</button>
                                <span class="rf-muted">الإجمالي: <strong id="ag-total">0</strong> ج.م · <span id="ag-days">0</span> يوم @if ($offer)(عرضك كان {{ Money::format($offer->price) }})@endif</span>
                            </div>

                            <label class="rf-field-label mt-2" for="ag-note">ملاحظة للعميل (اختياري)</label>
                            <textarea id="ag-note" name="freelancer_note" rows="2" maxlength="2000" class="form-control">{{ old('freelancer_note') }}</textarea>
                            <button class="btn rf-btn-primary mt-3">إرسال الاتفاق للعميل</button>
                        </form>
                    </details>
                @endif

                @if ($contract->agreements->count() > 1)
                    <details class="rf-agreement-history">
                        <summary>النسخ السابقة ({{ $contract->agreements->count() - 1 }})</summary>
                        <ul>
                            @foreach ($contract->agreements->skip(1) as $old)
                                <li>نسخة {{ $old->version }} · {{ $old->created_at->format('Y-m-d H:i') }} · {{ $old->statusLabel() }} · {{ count($old->stages) }} مراحل · {{ Money::format($old->total) }}
                                    @if ($old->client_note)<div class="rf-muted">طلب العميل: {{ $old->client_note }}</div>@endif</li>
                            @endforeach
                        </ul>
                    </details>
                @endif

                @if (in_array($contract->status, [Contract::STATUS_ACTIVE, Contract::STATUS_COMPLETED], true))
                    @if ($contract->status === Contract::STATUS_COMPLETED)
                        <div class="rf-notice rf-notice-success mt-3"><strong>اكتمل المشروع.</strong> اعتُمدت كل المراحل.</div>
                    @endif
                    @include('website.contracts.partials.stages-board')
                    @if ($isAdmin && $contract->status === Contract::STATUS_ACTIVE)
                        @include('website.contracts.partials.admin-tools')
                    @endif
                    @include('website.contracts.partials.addenda')
                @endif
                @if (in_array($contract->status, [Contract::STATUS_ACTIVE, Contract::STATUS_REPLACED, Contract::STATUS_COMPLETED], true))
                    @if ($contract->status === Contract::STATUS_REPLACED)
                        @include('website.contracts.partials.stages-board')
                    @endif
                    @include('website.contracts.partials.disputes')
                @endif
                @if ($contract->status === Contract::STATUS_COMPLETED)
                    @include('website.contracts.partials.ratings')
                @endif
                @include('website.contracts.partials.reply-requests')
                @if ($isAdmin)
                    @include('website.contracts.partials.admin-summary')
                @endif
            </div>
        </div>
    </section>
@endsection
@section('js')
    <script>
        (function () {
            var box = document.getElementById('ag-stages');
            if (!box) return;
            var counter = Date.now() % 1000000; // never collides with rows re-rendered from old input
            function totals() {
                var sum = 0, days = 0;
                box.querySelectorAll('.ag-stage').forEach(function (row, i) {
                    row.querySelector('.ag-num').textContent = i + 1;
                    sum += parseFloat(row.querySelector('[name$="[amount]"]').value) || 0;
                    days += parseInt(row.querySelector('[name$="[duration_days]"]').value, 10) || 0;
                });
                document.getElementById('ag-total').textContent = sum.toLocaleString('en-US', { maximumFractionDigits: 2 });
                document.getElementById('ag-days').textContent = days;
            }
            document.getElementById('ag-add').addEventListener('click', function () {
                if (box.children.length >= {{ \App\Services\Contracts\ContractPolicy::number('max_stages') }}) { alert('الحد الأقصى {{ \App\Services\Contracts\ContractPolicy::number('max_stages') }} مراحل'); return; }
                box.insertAdjacentHTML('beforeend', document.getElementById('ag-stage-template').innerHTML.replace(/__i__/g, 'n' + (counter++)));
                totals();
            });
            box.addEventListener('click', function (e) {
                var btn = e.target.closest('.ag-remove');
                if (!btn) return;
                if (box.children.length <= 1) { alert('الاتفاق يحتاج مرحلة واحدة على الأقل'); return; }
                btn.closest('.ag-stage').remove();
                totals();
            });
            box.addEventListener('input', totals);
            box.addEventListener('change', function (e) {
                if (e.target.matches('[name$="[unlimited_revisions]"]')) {
                    e.target.closest('.ag-stage').querySelector('[name$="[revisions]"]').disabled = e.target.checked;
                }
            });
            totals();
        })();
    </script>
@endsection
