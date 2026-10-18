{{--
    Service 3, phase 5: disputes and changing the freelancer on the contract page.
    Expects $contract (disputes.files, freelancerChanges loaded), $isClient, $isFreelancer, $isAdmin.
--}}
@php
    use App\Models\Contract;
    use App\Models\ContractDispute as Dsp;
    use App\Models\ContractFreelancerChange as Chg;
    $open = $contract->disputes->first(fn ($d) => $d->isOpen());
    $pastDisputes = $contract->disputes->reject(fn ($d) => $d->isOpen());
    $pendingChange = $contract->freelancerChanges->firstWhere('status', Chg::STATUS_PENDING);
    $approvedChange = $contract->freelancerChanges->firstWhere('status', Chg::STATUS_APPROVED);
    $active = $contract->status === Contract::STATUS_ACTIVE;
    $party = $isClient || $isFreelancer;
    $roleOf = fn ($userId) => (int) $userId === (int) $contract->client_id ? 'العميل' : ((int) $userId === (int) $contract->freelancer_id ? 'المستقل' : 'الإدارة');
@endphp
<div id="disputes" class="mt-3">
    @if ($approvedChange)
        <div class="rf-notice rf-notice-warning">
            <strong>تغيّر المستقل</strong> ({{ Chg::KINDS[$approvedChange->kind] }}، {{ $approvedChange->decided_at?->format('Y-m-d') }}): {{ $approvedChange->decision_note }}
            — حصل المستقل المغادر على {{ $approvedChange->leaving_percent }}% من المرحلة الجارية.
            @if ($approvedChange->continuation)
                يُستكمل المشروع في <a href="{{ $isClient ? route('requests.show', $approvedChange->continuation) : ($isAdmin ? url('admin/requests/' . $approvedChange->continuation->number) : '#') }}">{{ $approvedChange->continuation->number }}</a> بعروض جديدة واتفاق جديد.
            @endif
        </div>
    @endif

    @if ($open)
        <div class="rf-dispute">
            <div class="rf-change-head">
                <strong><i class="bi bi-exclamation-octagon"></i> خلاف: {{ \App\Services\Contracts\ContractPolicy::disputeReasonLabel($open->reason) }}</strong>
                <span class="rf-status">{{ $open->statusLabel() }}</span>
            </div>
            <p class="rf-muted mb-1">فتحه {{ $roleOf($open->opened_by) }} {{ $open->created_at->format('Y-m-d H:i') }}
                @if ($open->stage) · المرحلة «{{ $open->stage->title }}» (مبلغها مجمّد حتى القرار)@endif</p>
            <p class="rf-problem mb-1">{{ $open->description }}</p>
            @foreach ($open->files as $file)<a href="{{ route('contracts.file', [$contract, $file]) }}" class="d-block"><i class="bi bi-paperclip"></i> {{ $file->original_name }}</a>@endforeach
            @if ($open->response)
                <p class="mb-1 mt-2"><strong>رد {{ $roleOf((int) $open->opened_by === (int) $contract->client_id ? $contract->freelancer_id : $contract->client_id) }}:</strong> {{ $open->response }}</p>
            @elseif ($open->status === Dsp::STATUS_OPEN)
                <p class="rf-muted mb-1">مهلة الرد حتى {{ $open->respond_due_at->format('Y-m-d H:i') }}، ثم تقرر الإدارة بما لديها.</p>
            @endif

            @if ($party && $open->status === Dsp::STATUS_OPEN && (int) $open->opened_by !== (int) auth()->id())
                <form method="POST" action="{{ route('contracts.disputes.action', [$contract, $open, 'respond']) }}" enctype="multipart/form-data" class="mt-2">
                    @csrf
                    <textarea name="response" rows="3" maxlength="5000" class="form-control mb-2" placeholder="ردك على الخلاف">{{ old('response') }}</textarea>
                    @error('response')<div class="rf-field-error">{{ $message }}</div>@enderror
                    <input type="file" name="files[]" multiple class="form-control mb-2">
                    <button class="btn rf-btn-primary">إرسال ردي وأدلتي</button>
                </form>
            @endif
            @if ((int) $open->opened_by === (int) auth()->id())
                <form method="POST" action="{{ route('contracts.disputes.action', [$contract, $open, 'withdraw']) }}" onsubmit="return confirm('سحب الخلاف؟')" class="mt-2">
                    @csrf<button class="btn rf-btn-danger-text">سحب الخلاف</button>
                </form>
            @endif

            @if ($isAdmin && $open->proposed)
                @php $p = $open->proposed; @endphp
                <div class="rf-admin-box mt-2">
                    <strong>قرار مقترح بانتظار الاعتماد</strong>
                    <p class="mb-1">{{ Dsp::DECISIONS[$p['decision']] ?? $p['decision'] }}{{ isset($p['percent']) && $p['percent'] !== null ? ' (' . $p['percent'] . '%)' : '' }}{{ ! empty($p['revision_due']) ? ' حتى ' . $p['revision_due'] : '' }} — {{ $p['note'] }}</p>
                    <p class="rf-muted mb-2">المبلغ المعني {{ \App\Support\Money::format($p['amount'] ?? 0) }}</p>
                    @if (\App\Support\StaffPermissions::canApprove(auth()->user(), (float) ($p['amount'] ?? 0)))
                        <form method="POST" action="{{ route('contracts.disputes.action', [$contract, $open, 'approve-proposal']) }}" class="d-inline">@csrf<button class="btn rf-btn-accept">اعتماد وتنفيذ</button></form>
                        <form method="POST" action="{{ route('contracts.disputes.action', [$contract, $open, 'reject-proposal']) }}" class="d-inline">@csrf<button class="btn rf-btn-danger-text">رفض المقترح</button></form>
                    @else
                        <p class="rf-muted mb-0">يعتمده {{ \App\Support\StaffPermissions::aboveThreshold((float) ($p['amount'] ?? 0)) ? 'المدير' : 'المحاسب أو المدير' }}.</p>
                    @endif
                </div>
            @elseif ($isAdmin)
                <form method="POST" action="{{ route('contracts.disputes.action', [$contract, $open, 'decide']) }}" class="rf-admin-box mt-2">
                    @csrf
                    <strong>قرار الإدارة</strong>
                    <select name="decision" class="form-control mb-2">
                        @foreach (Dsp::DECISIONS as $key => $label)<option value="{{ $key }}" @selected(old('decision') === $key)>{{ $label }}</option>@endforeach
                    </select>
                    <div class="row">
                        <div class="col-md-6 mb-2"><label class="rf-field-label">نسبة المستقل % (للتقسيم أو تغيير المستقل)</label><input type="number" name="percent" min="0" max="100" class="form-control" value="{{ old('percent') }}"></div>
                        <div class="col-md-6 mb-2"><label class="rf-field-label">موعد التعديل (لقرار التعديل)</label><input type="date" name="revision_due" class="form-control" value="{{ old('revision_due') }}" min="{{ now()->toDateString() }}"></div>
                    </div>
                    <textarea name="note" rows="2" maxlength="3000" class="form-control mb-2" placeholder="سبب القرار (يظهر للطرفين)">{{ old('note') }}</textarea>
                    @foreach (['note', 'percent', 'revision_due', 'decision'] as $field)@error($field)<div class="rf-field-error">{{ $message }}</div>@enderror @endforeach
                    <button class="btn rf-btn-primary" onclick="return confirm('تنفيذ القرار؟ القرارات المالية لا يمكن التراجع عنها.')">تنفيذ القرار</button>
                </form>
            @endif
        </div>
    @elseif ($party && $active)
        <details class="rf-dispute-new" @if ($errors->hasAny(['reason', 'description'])) open @endif>
            <summary>مشكلة لم تُحل؟ افتح خلافًا تحسمه الإدارة</summary>
            <form method="POST" action="{{ route('contracts.disputes.store', $contract) }}" enctype="multipart/form-data">
                @csrf
                <select name="reason" class="form-control mb-2">
                    <option value="">— سبب الخلاف —</option>
                    @foreach (\App\Services\Contracts\ContractPolicy::disputeReasons() as $key => $label)<option value="{{ $key }}" @selected(old('reason') === $key)>{{ $label }}</option>@endforeach
                </select>
                @error('reason')<div class="rf-field-error">{{ $message }}</div>@enderror
                <textarea name="description" rows="3" maxlength="5000" class="form-control mb-2" placeholder="اشرح ما حدث وما تطلبه">{{ old('description') }}</textarea>
                @error('description')<div class="rf-field-error">{{ $message }}</div>@enderror
                <input type="file" name="files[]" multiple class="form-control mb-2">
                <p class="rf-muted mb-2">يُجمَّد مبلغ المرحلة الحالية، وللطرف الآخر {{ \App\Services\Contracts\ContractPolicy::number('dispute_respond_days') }} أيام للرد، ثم تقرر الإدارة وفق <a href="{{ route('policy.disputes') }}" target="_blank">سياسة الخلافات</a>.</p>
                <button class="btn rf-btn-primary">فتح الخلاف</button>
            </form>
        </details>
    @endif

    {{-- Changing the freelancer. --}}
    @if ($pendingChange)
        <div class="rf-dispute">
            <strong>{{ Chg::KINDS[$pendingChange->kind] }} لتغيير المستقل</strong> <span class="rf-status">{{ $pendingChange->statusLabel() }}</span>
            <p class="rf-problem mb-1">{{ $pendingChange->reason }}</p>
            @if ($isAdmin && $pendingChange->proposed)
                <div class="rf-admin-box mt-2">
                    <strong>تغيير مقترح بانتظار الاعتماد</strong>: {{ $pendingChange->proposed['percent'] }}% للمستقل المغادر — {{ $pendingChange->proposed['note'] }}
                    @if (\App\Support\StaffPermissions::canApprove(auth()->user(), (float) ($pendingChange->proposed['amount'] ?? 0)))
                        <form method="POST" action="{{ route('contracts.change.proposal', [$contract, $pendingChange]) }}" class="mt-2">@csrf
                            <button name="decision" value="approve" class="btn rf-btn-accept">اعتماد وتنفيذ</button>
                            <button name="decision" value="reject" class="btn rf-btn-danger-text">رفض المقترح</button></form>
                    @else
                        <p class="rf-muted mb-0">يعتمده المحاسب أو المدير.</p>
                    @endif
                </div>
            @elseif ($isAdmin)
                @include('website.contracts.partials.change-decision', ['change' => $pendingChange])
            @endif
        </div>
    @elseif ($active)
        @if ($isClient && $contract->stages()->lateForChange()->exists())
            <details class="rf-dispute-new">
                <summary>طلب تغيير المستقل (مرّت {{ \App\Services\Contracts\ContractPolicy::number('late_change_days') }} أيام على التأخير)</summary>
                <form method="POST" action="{{ route('contracts.change.request', $contract) }}">
                    @csrf
                    <textarea name="reason" rows="2" maxlength="3000" class="form-control mb-2" placeholder="لماذا تطلب تغيير المستقل؟"></textarea>
                    <button class="btn rf-btn-primary">إرسال الطلب للإدارة</button>
                </form>
            </details>
        @elseif ($isFreelancer)
            <details class="rf-dispute-new">
                <summary>لا تستطيع الإكمال؟ اطلب الانسحاب</summary>
                <form method="POST" action="{{ route('contracts.change.request', $contract) }}">
                    @csrf
                    <textarea name="reason" rows="2" maxlength="3000" class="form-control mb-2" placeholder="سبب الانسحاب"></textarea>
                    <p class="rf-muted mb-2">تقرر الإدارة نسبتك من المرحلة الجارية حسب المُنجز؛ المراحل المعتمدة حقك ولا تُمس.</p>
                    <button class="btn rf-btn-danger-text">إرسال طلب الانسحاب</button>
                </form>
            </details>
        @endif
        @if ($isAdmin)
            <details class="rf-dispute-new">
                <summary>تغيير المستقل مباشرة (قرار الإدارة)</summary>
                @include('website.contracts.partials.change-decision', ['change' => null])
            </details>
        @endif
    @endif

    @if ($pastDisputes->isNotEmpty())
        <details class="rf-pause-history">
            <summary>خلافات سابقة ({{ $pastDisputes->count() }})</summary>
            <ul>
                @foreach ($pastDisputes as $d)
                    <li>{{ $d->created_at->format('Y-m-d') }} · {{ \App\Services\Contracts\ContractPolicy::disputeReasonLabel($d->reason) }} · {{ $d->statusLabel() }}
                        @if ($d->decision) — {{ Dsp::DECISIONS[$d->decision] ?? $d->decision }}{{ $d->freelancer_percent !== null && $d->decision === 'split' ? ' (' . $d->freelancer_percent . '%)' : '' }}: {{ $d->decision_note }}@endif</li>
                @endforeach
            </ul>
        </details>
    @endif
</div>
