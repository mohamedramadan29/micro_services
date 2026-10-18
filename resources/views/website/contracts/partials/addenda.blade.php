{{-- Addendum after the start: history, the open one (approve/reject/withdraw) and the proposal form. --}}
@php
    use App\Models\ContractAddendum as AD;
    use App\Models\ContractStage as CS;
    $open = $contract->addenda->firstWhere('status', AD::STATUS_PROPOSED);
    $isParty = $isClient || $isFreelancer;
    $editable = $contract->stages->reject(fn ($s) => $s->isSettled());
@endphp
<div class="rf-card-section mt-4" id="addenda">
    <h2 class="rf-section-title">الملحق (تعديل الاتفاق بعد البدء)</h2>
    <p class="rf-muted">أي تغيير بعد بدء العمل — مرحلة جديدة، أو تغيير مبلغ أو مدة أو عدد تعديلات مرحلة لم تنتهِ — يقترحه أحد الطرفين، ولا يُطبَّق إلا بعد موافقة الآخر. المرحلة المموّلة يمكن تخفيض مبلغها فقط (يعود الفرق لرصيد العميل)؛ للزيادة أضف مرحلة إضافية.</p>

    @foreach ($contract->addenda as $addendum)
        <div class="rf-addendum rf-addendum-{{ $addendum->status }}">
            <div class="rf-change-head">
                <strong>ملحق من {{ (int) $addendum->proposed_by === (int) $contract->client_id ? 'العميل' : 'المستقل' }} · {{ $addendum->created_at->format('Y-m-d H:i') }}</strong>
                <span class="rf-status">{{ $addendum->statusLabel() }}</span>
            </div>
            <ul class="mb-1">
                @foreach ($addendum->lines() as $line)<li>{{ $line }}</li>@endforeach
            </ul>
            <p class="rf-muted mb-1">السبب: {{ $addendum->reason }} · الإجمالي {{ \App\Support\Money::show($addendum->total_before) }} ← {{ \App\Support\Money::show($addendum->total_after) }}</p>
            @if ($addendum->response_note)<p class="rf-muted mb-1">الرد: {{ $addendum->response_note }}</p>@endif

            @if ($addendum->isOpen() && $isParty)
                @if ((int) $addendum->proposed_by === (int) Auth::id())
                    <form method="POST" action="{{ route('contracts.addenda.action', [$contract, $addendum, 'cancel']) }}" class="d-inline">
                        @csrf
                        <button class="btn rf-btn-outline btn-sm">سحب الملحق</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('contracts.addenda.action', [$contract, $addendum, 'approve']) }}" class="mb-2" onsubmit="return confirm('الموافقة تطبّق التعديلات على المراحل فورًا. متابعة؟')">
                        @csrf
                        <textarea name="note" rows="2" maxlength="2000" class="form-control mb-2" placeholder="ملاحظة (اختياري)"></textarea>
                        <button class="btn rf-btn-primary btn-sm">أوافق على الملحق</button>
                        <button class="btn rf-btn-outline btn-sm" formaction="{{ route('contracts.addenda.action', [$contract, $addendum, 'reject']) }}" onclick="this.form.onsubmit = null">أرفض</button>
                    </form>
                @endif
            @endif
        </div>
    @endforeach

    @if ($isParty && $contract->status === \App\Models\Contract::STATUS_ACTIVE && ! $open && ! $contract->openDispute())
        <details class="rf-addendum-form" @if ($errors->any() && old('reason')) open @endif>
            <summary class="btn rf-btn-secondary">اقتراح ملحق</summary>
            <form method="POST" action="{{ route('contracts.addenda.store', $contract) }}" class="mt-3">
                @csrf
                @if ($editable->isNotEmpty())
                    <h3 class="rf-section-title">المراحل الحالية (عدّل ما تريد فقط)</h3>
                    @foreach ($editable as $stage)
                        @php $row = old('stages.' . $stage->id, []); @endphp
                        <details class="ag-stage rf-addendum-stage" @if ($row) open @endif>
                            <summary class="ag-stage-head">
                                <strong>{{ $stage->sort_order }}. {{ $stage->title }}</strong>
                                <span class="rf-muted">{{ \App\Support\Money::show($stage->amount) }} · {{ $stage->duration_days }} يوم · {{ $stage->statusLabel() }} — اضغط للتعديل</span>
                            </summary>
                            @if ($stage->status === CS::STATUS_FUNDING)
                                <p class="rf-muted mb-0">قيد الدفع الآن؛ لا تُعدَّل حتى يكتمل الدفع أو يفشل.</p>
                            @else
                                <div class="row">
                                    <div class="col-md-6 mb-2">
                                        <label class="rf-field-label">العنوان</label>
                                        <input type="text" name="stages[{{ $stage->id }}][title]" maxlength="150" class="form-control" value="{{ $row['title'] ?? $stage->title }}">
                                    </div>
                                    <div class="col-md-2 col-4 mb-2">
                                        <label class="rf-field-label">المدة (أيام)</label>
                                        <input type="number" name="stages[{{ $stage->id }}][duration_days]" min="1" max="365" class="form-control" value="{{ $row['duration_days'] ?? $stage->duration_days }}">
                                    </div>
                                    <div class="col-md-2 col-4 mb-2">
                                        <label class="rf-field-label">المبلغ (ج.م)</label>
                                        <input type="number" name="stages[{{ $stage->id }}][amount]" min="1" step="0.01" class="form-control" value="{{ $row['amount'] ?? (float) $stage->amount }}"
                                               @if ($stage->status === CS::STATUS_FUNDED) max="{{ (float) $stage->amount }}" title="مموّلة: التخفيض فقط" @endif>
                                    </div>
                                    <div class="col-md-2 col-4 mb-2">
                                        <label class="rf-field-label">التعديلات</label>
                                        <input type="number" name="stages[{{ $stage->id }}][revisions]" min="{{ $stage->revisions_used }}" max="20" class="form-control" value="{{ $row['revisions'] ?? $stage->revisions }}" placeholder="غير محدود">
                                    </div>
                                </div>
                                <label class="rf-field-label">ما يُسلَّم</label>
                                <textarea name="stages[{{ $stage->id }}][deliverable]" rows="2" maxlength="2000" class="form-control mb-2">{{ $row['deliverable'] ?? $stage->deliverable }}</textarea>
                                <label class="rf-field-label">معيار القبول</label>
                                <textarea name="stages[{{ $stage->id }}][acceptance]" rows="1" maxlength="1000" class="form-control">{{ $row['acceptance'] ?? $stage->acceptance }}</textarea>
                                <div class="ag-revisions">
                                    <label class="ag-unlimited"><input type="checkbox" name="stages[{{ $stage->id }}][unlimited_revisions]" value="1" @checked(($row['unlimited_revisions'] ?? $stage->revisions === null))> تعديلات غير محدودة</label>
                                    @if ($stage->status === CS::STATUS_UNFUNDED && ! $stage->started_at)
                                        <label class="ag-unlimited"><input type="checkbox" name="stages[{{ $stage->id }}][remove]" value="1" @checked(! empty($row['remove']))> حذف هذه المرحلة</label>
                                    @endif
                                </div>
                            @endif
                        </details>
                    @endforeach
                @endif

                <h3 class="rf-section-title mt-3">مراحل جديدة (اختياري)</h3>
                @foreach (range(0, 1) as $n)
                    @php $row = old('new.' . $n, []); @endphp
                    <div class="ag-stage">
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label class="rf-field-label">عنوان المرحلة الجديدة</label>
                                <input type="text" name="new[{{ $n }}][title]" maxlength="150" class="form-control" value="{{ $row['title'] ?? '' }}" placeholder="اتركه فارغًا إن لا تريد إضافة">
                            </div>
                            <div class="col-md-2 col-4 mb-2">
                                <label class="rf-field-label">المدة (أيام)</label>
                                <input type="number" name="new[{{ $n }}][duration_days]" min="1" max="365" class="form-control" value="{{ $row['duration_days'] ?? '' }}">
                            </div>
                            <div class="col-md-2 col-4 mb-2">
                                <label class="rf-field-label">المبلغ (ج.م)</label>
                                <input type="number" name="new[{{ $n }}][amount]" min="1" step="0.01" class="form-control" value="{{ $row['amount'] ?? '' }}">
                            </div>
                            <div class="col-md-2 col-4 mb-2">
                                <label class="rf-field-label">التعديلات</label>
                                <input type="number" name="new[{{ $n }}][revisions]" min="0" max="20" class="form-control" value="{{ $row['revisions'] ?? \App\Services\Contracts\ContractPolicy::number('default_revisions') }}">
                            </div>
                        </div>
                        <label class="rf-field-label">ما يُسلَّم</label>
                        <textarea name="new[{{ $n }}][deliverable]" rows="2" maxlength="2000" class="form-control mb-2">{{ $row['deliverable'] ?? '' }}</textarea>
                        <label class="rf-field-label">معيار القبول (اختياري)</label>
                        <textarea name="new[{{ $n }}][acceptance]" rows="1" maxlength="1000" class="form-control">{{ $row['acceptance'] ?? '' }}</textarea>
                    </div>
                @endforeach

                <label class="rf-field-label mt-2" for="addendum-reason">سبب الملحق</label>
                <textarea id="addendum-reason" name="reason" rows="3" maxlength="2000" class="form-control" placeholder="مثال: العميل طلب صفحة إضافية خارج النطاق المتفق عليه">{{ old('reason') }}</textarea>
                <button class="btn rf-btn-primary mt-2">إرسال الملحق للطرف الآخر</button>
            </form>
        </details>
    @endif
</div>
