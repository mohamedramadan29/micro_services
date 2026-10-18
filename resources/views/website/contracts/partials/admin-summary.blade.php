{{-- For the team only: case summary, money movements and the project log (service 3 spec pages 9, 13, 16). --}}
@php
    $lastDelivery = $contract->stages->flatMap->deliveries->sortByDesc('created_at')->first();
    $clientActs = collect([
        $contract->stages->flatMap->deliveries->whereNotNull('reviewed_at')->sortByDesc('reviewed_at')->first()?->reviewed_at,
        $contract->stages->flatMap->pauses->whereNotNull('client_response')->sortByDesc('ended_at')->first()?->ended_at,
        $contract->replyRequests->whereNotNull('client_replied_at')->sortByDesc('client_replied_at')->first()?->client_replied_at,
    ])->filter()->sortDesc()->first();
    $stageIds = $contract->stages->pluck('id');
    $movements = \App\Models\WalletTransaction::whereIn('contract_stage_id', $stageIds)->latest('id')->limit(30)->get();
    $payments = \App\Models\OnlinePayment::where('purpose', \App\Models\OnlinePayment::PURPOSE_CONTRACT_STAGE)
        ->where('payload->contract_id', $contract->id)->latest('id')->get();
    $log = $contract->request ? $contract->request->events()->getQuery()->latest('id')->limit(40)->get() : collect();
@endphp
<div class="rf-card-section mt-4 rf-admin-tools" id="admin-summary">
    <h2 class="rf-section-title"><i class="bi bi-clipboard-data"></i> ملخص للإدارة</h2>
    <div class="row">
        <div class="col-md-4 mb-2"><span class="rf-muted d-block">آخر تسليم من المستقل</span>{{ $lastDelivery ? $lastDelivery->created_at->format('Y-m-d H:i') . ' — ' . $lastDelivery->stage->title : '—' }}</div>
        <div class="col-md-4 mb-2"><span class="rf-muted d-block">آخر تفاعل من العميل</span>{{ $clientActs ? $clientActs->format('Y-m-d H:i') : '—' }}</div>
        <div class="col-md-4 mb-2"><span class="rf-muted d-block">أدلة وملفات الخلافات</span>{{ $contract->disputes->sum(fn ($d) => $d->files->count()) }} ملف في {{ $contract->disputes->count() }} خلاف</div>
    </div>

    @if (in_array($contract->status, [\App\Models\Contract::STATUS_AGREEMENT, \App\Models\Contract::STATUS_ACTIVE], true))
        <form method="POST" action="{{ route('contracts.admin.reply-request', $contract) }}" class="mt-2">
            @csrf
            <strong class="d-block mb-1">طلب رد من الطرفين بمهلة</strong>
            <div class="d-flex flex-wrap" style="gap: 6px">
                <input type="text" name="question" maxlength="2000" class="form-control" style="flex: 1; min-width: 220px" placeholder="ما الذي تحتاج معرفته من الطرفين؟" required>
                <input type="number" name="days" min="1" max="30" value="2" class="form-control" style="max-width: 90px" title="المهلة بالأيام">
                <button class="btn rf-btn-secondary btn-sm">إرسال</button>
            </div>
        </form>
    @endif

    <details class="mt-3">
        <summary><strong>حركة المبالغ ({{ $movements->count() }}) ومراجع الدفع ({{ $payments->count() }})</strong></summary>
        <table class="table table-sm mt-2">
            <thead><tr><th>التاريخ</th><th>الحركة</th><th>المبلغ</th></tr></thead>
            <tbody>
                @foreach ($movements as $m)
                    <tr><td>{{ $m->created_at?->format('Y-m-d H:i') }}</td><td>{{ $m->description }}</td><td>{{ \App\Support\Money::format($m->amount) }}</td></tr>
                @endforeach
            </tbody>
        </table>
        @if ($payments->isNotEmpty())
            <table class="table table-sm">
                <thead><tr><th>مرجع EasyKash</th><th>الحالة</th><th>المبلغ (مع الرسوم)</th><th>التاريخ</th></tr></thead>
                <tbody>
                    @foreach ($payments as $p)
                        <tr><td dir="ltr">#{{ $p->id }}{{ $p->gateway_transaction ? ' · ' . $p->gateway_transaction : '' }}</td><td>{{ $p->status }}</td><td>{{ \App\Support\Money::format($p->amount) }}</td><td>{{ $p->created_at->format('Y-m-d H:i') }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </details>

    <details class="mt-2">
        <summary><strong>سجل المشروع ({{ $log->count() }})</strong></summary>
        <ul class="mt-2 mb-0">
            @foreach ($log as $event)
                <li class="small"><span class="rf-muted">{{ $event->created_at->format('Y-m-d H:i') }}</span> — {{ $event->note ?? $event->type }}</li>
            @endforeach
        </ul>
    </details>
</div>
