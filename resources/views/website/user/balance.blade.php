@extends('website.layouts.master')
@section('title')
    الرصيد والسحب
@endsection
@section('content')
    @use('App\Models\front\WithDraw')
    @php
        $me = Auth::user();
        $kinds = [
            'release' => ['مستحقات عمل', '+', 'in'],
            'refund' => ['مبلغ أُعيد لرصيدك', '+', 'in'],
            'topup' => ['شحن رصيد', '+', 'in'],
            'withdraw' => ['طلب سحب', '−', 'out'],
            'escrow_in' => ['دفعة لعمل (تبقى لدى نفّذها حتى تعتمد العمل)', '', 'held'],
        ];
        $statusTone = [0 => 'warn', 1 => 'ok', 2 => 'bad'];
        $method = old('method', 'bank');
        $hasPending = $withdrawals->contains('status', 0);
    @endphp

    <section class="gray-bg pt-4 text-right profile_page" dir="rtl">
        <div class="container-fluid">
            <div class="row m-0">
                <div class="col-xl-3 col-lg-4 col-md-12 col-sm-12">
                    @include('website.service-requests.partials.dashboard-nav')
                </div>

                <div class="col-xl-9 col-lg-8 col-md-12 col-sm-12 rf-acc">
                    <div class="bredcrumb_wrap">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ url('dashboard') }}"> لوحتي </a></li>
                                <li class="breadcrumb-item active" aria-current="page"> الرصيد والسحب</li>
                            </ol>
                        </nav>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                    @endif

                    <div class="rf-acc-stats">
                        <div><strong>{{ \App\Support\Money::show($me->balance ?? 0) }}</strong><span>رصيدك المتاح (يمكن سحبه أو الدفع به)</span></div>
                        <div><strong>{{ \App\Support\Money::show($pendingWithdraw) }}</strong><span>قيد السحب (بانتظار التحويل)</span></div>
                    </div>

                    <div class="rf-acc-card">
                        <h3>إدارة الرصيد</h3>
                        <div class="d-flex flex-wrap" style="gap: 8px">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#charge_balance">شحن رصيد <i class="bi bi-plus"></i></button>
                            @if ($me->balance > 0 && ! $hasPending)
                                <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#minus_balance">طلب سحب <i class="bi bi-dash"></i></button>
                            @elseif ($hasPending)
                                <span class="rf-acc-pill warn align-self-center">عندك طلب سحب قيد التنفيذ؛ يمكنك طلب سحب جديد بعد تحويله.</span>
                            @endif
                        </div>
                        <p class="rf-acc-empty mt-2 mb-0">الدفع والشحن عبر EasyKash بالجنيه المصري. يتم تحويل طلبات السحب يدويًا من فريق نفّذها، وإن رُفض الطلب يعود المبلغ لرصيدك.</p>
                    </div>

                    @if ($withdrawals->isNotEmpty())
                        <div class="rf-acc-card">
                            <h3>طلبات السحب</h3>
                            @foreach ($withdrawals as $w)
                                <div class="rf-acc-row">
                                    <div>
                                        <strong>{{ $w->methodLabel() }}</strong>
                                        <small>{{ $w->account_name }} · <span dir="ltr">{{ $w->account_number }}</span>{{ $w->bank_name ? ' · ' . $w->bank_name : '' }} · {{ $w->created_at?->format('Y-m-d') }}</small>
                                        @if ($w->notes)<small>ملاحظة الفريق: {{ $w->notes }}</small>@endif
                                    </div>
                                    <span class="rf-acc-num">{{ \App\Support\Money::show($w->amount) }}</span>
                                    <span class="rf-acc-pill {{ $statusTone[(int) $w->status] ?? '' }}">{{ WithDraw::STATUSES[(int) $w->status] ?? '—' }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="rf-acc-card">
                        <h3>حركة الرصيد</h3>
                        @forelse ($history as $row)
                            @php
                                [$label, $sign, $tone] = $kinds[$row['type']] ?? [$row['type'], '', ''];
                            @endphp
                            <div class="rf-acc-row">
                                <div>
                                    <strong>{{ $label }}</strong>
                                    <small>{{ $row['description'] }} · {{ $row['at']?->format('Y-m-d H:i') }}</small>
                                </div>
                                <span class="rf-acc-num rf-acc-money {{ $tone }}" dir="ltr">{{ $sign }} {{ \App\Support\Money::format($row['amount']) }}</span>
                            </div>
                        @empty
                            <p class="rf-acc-empty">لا توجد حركات على رصيدك بعد.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Top up --}}
    <div class="modal fade buy_services_model" id="charge_balance" tabindex="-1" aria-labelledby="rf-charge-title" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="rf-charge-title">شحن رصيد</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق">X</button>
                </div>
                <form method="post" action="{{ url('charge_balance') }}">
                    @csrf
                    <div class="modal-body">
                        <p class="text-muted">الدفع عبر EasyKash (بطاقة، فوري، محفظة) بالجنيه المصري، ويُضاف المبلغ لرصيدك فور تأكيد الدفع.</p>
                        <div class="form-group">
                            <label>المبلغ (ج.م)</label>
                            <input type="number" class="form-control" name="price" min="1" step="0.01" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">متابعة للدفع</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Withdraw --}}
    <div class="modal fade buy_services_model" id="minus_balance" tabindex="-1" aria-labelledby="rf-withdraw-title" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="rf-withdraw-title">طلب سحب رصيد</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق">X</button>
                </div>
                <form method="post" action="{{ url('withdraw_balance') }}" id="rf-withdraw-form">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>المبلغ (ج.م) — المتاح {{ \App\Support\Money::format($me->balance ?? 0) }}</label>
                            <input type="number" class="form-control" name="amount" min="1" step="0.01" max="{{ $me->balance }}" value="{{ old('amount') }}" required>
                        </div>
                        <div class="form-group">
                            <label>طريقة الاستلام</label>
                            <select name="method" class="form-control" id="rf-withdraw-method">
                                @foreach (WithDraw::METHODS as $key => $label)
                                    <option value="{{ $key }}" @selected($method === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>اسم صاحب الحساب</label>
                            <input type="text" class="form-control" name="account_name" value="{{ old('account_name', $me->name) }}" required maxlength="255">
                        </div>
                        <div class="form-group">
                            <label id="rf-withdraw-number-label">{{ $method === 'bank' ? 'رقم الحساب البنكي' : 'رقم المحفظة أو عنوان إنستاباي' }}</label>
                            <input type="text" dir="ltr" class="form-control" name="account_number" value="{{ old('account_number') }}" required maxlength="255">
                        </div>
                        <div data-bank-only @if ($method !== 'bank') hidden @endif>
                            <div class="form-group">
                                <label>اسم البنك</label>
                                <input type="text" class="form-control" name="bank_name" value="{{ old('bank_name') }}" maxlength="255" @if ($method === 'bank') required @endif>
                            </div>
                            <div class="form-row row">
                                <div class="form-group col-6">
                                    <label>IBAN <small class="text-muted">(اختياري)</small></label>
                                    <input type="text" dir="ltr" class="form-control" name="iban_code" value="{{ old('iban_code') }}" maxlength="50">
                                </div>
                                <div class="form-group col-6">
                                    <label>SWIFT <small class="text-muted">(اختياري، للتحويل خارج مصر)</small></label>
                                    <input type="text" dir="ltr" class="form-control" name="swift_code" value="{{ old('swift_code') }}" maxlength="50">
                                </div>
                            </div>
                            <div class="form-group">
                                <label>الدولة</label>
                                <input type="text" class="form-control" name="country" value="{{ old('country', 'مصر') }}" maxlength="100">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-warning">إرسال طلب السحب</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        (function () {
            var select = document.getElementById('rf-withdraw-method');
            if (!select) return;
            select.addEventListener('change', function () {
                var bank = select.value === 'bank';
                document.querySelectorAll('#rf-withdraw-form [data-bank-only]').forEach(function (el) { el.hidden = !bank; });
                document.querySelector('#rf-withdraw-form [name=bank_name]').required = bank;
                document.getElementById('rf-withdraw-number-label').textContent = bank ? 'رقم الحساب البنكي' : 'رقم المحفظة أو عنوان إنستاباي';
            });
        })();
    </script>
@endsection
