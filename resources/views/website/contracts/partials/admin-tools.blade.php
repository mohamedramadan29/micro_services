{{-- Team tools without a dispute: extend the running stage, approve a delivery the client did not review, end a pause. --}}
@php
    $toolStage = $current;
    $waiting = $toolStage?->deliveries->firstWhere('status', \App\Models\ContractDelivery::STATUS_SUBMITTED);
    $activePause = $toolStage?->pauses->firstWhere('status', \App\Models\ContractPause::STATUS_ACTIVE);
@endphp
@if ($toolStage && $toolStage->started_at)
    <div class="rf-card-section mt-4 rf-admin-tools" id="admin-tools">
        <h2 class="rf-section-title"><i class="bi bi-shield-check"></i> أدوات الإدارة — المرحلة «{{ $toolStage->title }}»</h2>
        <p class="rf-muted">يظهر للإدارة فقط. كل إجراء يحتاج سببًا مكتوبًا، ويُسجَّل في سجل الطلب ويُبلَّغ به الطرفان.</p>
        <div class="row">
            @if ($toolStage->due_at)
                <div class="col-md-4 mb-3">
                    <form method="POST" action="{{ route('contracts.admin.extend', [$contract, $toolStage]) }}">
                        @csrf
                        <strong class="d-block mb-1">تمديد الموعد</strong>
                        <span class="rf-muted d-block mb-1">الموعد الحالي {{ $toolStage->due_at->format('Y-m-d H:i') }}</span>
                        <input type="number" name="days" min="1" max="90" class="form-control mb-1" placeholder="عدد الأيام" required>
                        <textarea name="reason" rows="2" class="form-control mb-1" placeholder="السبب" required></textarea>
                        <button class="btn rf-btn-secondary btn-sm">مدّ الموعد</button>
                    </form>
                </div>
            @endif
            @if ($waiting)
                <div class="col-md-4 mb-3">
                    <form method="POST" action="{{ route('contracts.admin.approve', [$contract, $waiting]) }}" onsubmit="return confirm('اعتماد التسليم نيابة عن العميل وتحويل المبلغ للمستقل؟')">
                        @csrf
                        <strong class="d-block mb-1">اعتماد التسليم نيابة عن العميل</strong>
                        <span class="rf-muted d-block mb-1">سُلّم {{ $waiting->created_at->format('Y-m-d') }} · موعد المراجعة {{ $waiting->review_due_at?->format('Y-m-d') }}</span>
                        <textarea name="reason" rows="2" class="form-control mb-1" placeholder="راجعنا التسليم ووجدناه مطابقًا… (لماذا؟)" required></textarea>
                        <button class="btn rf-btn-primary btn-sm">اعتماد وتحويل {{ \App\Support\Money::format($toolStage->amount) }}</button>
                    </form>
                </div>
            @endif
            @if ($activePause)
                <div class="col-md-4 mb-3">
                    <form method="POST" action="{{ route('contracts.admin.end-pause', [$contract, $activePause]) }}">
                        @csrf
                        <strong class="d-block mb-1">إنهاء إيقاف العدّاد</strong>
                        <span class="rf-muted d-block mb-1">متوقف منذ {{ $activePause->started_at->format('Y-m-d H:i') }}</span>
                        <label class="ag-unlimited d-block"><input type="checkbox" name="extend" value="1" checked> مدّ الموعد بمدة الإيقاف</label>
                        <textarea name="reason" rows="2" class="form-control mb-1" placeholder="السبب" required></textarea>
                        <button class="btn rf-btn-secondary btn-sm">إنهاء الإيقاف</button>
                    </form>
                </div>
            @endif
        </div>
    </div>
@endif
