@extends('admin.layouts.master')
@section('title')
    الرئيسية والتقارير
@endsection

@section('content')
    @php
        $change = function (array $kpi) {
            $v = (float) ($kpi['value'] ?? 0);
            $p = (float) ($kpi['previous'] ?? 0);
            if ($kpi['value'] === null || $kpi['previous'] === null || ($v == 0 && $p == 0)) {
                return '';
            }
            if ($p == 0) {
                return '<span class="up">جديد</span>';
            }
            $pct = round(($v - $p) * 100 / $p);

            return $pct >= 0 ? '<span class="up">▲ ' . $pct . '%</span>' : '<span class="down">▼ ' . abs($pct) . '%</span>';
        };
        $periodLabel = \App\Services\Admin\DashboardStats::PERIODS[$period];
        $statusTone = ['open' => 'warn', 'inquiry' => '', 'in_progress' => 'warn', 'completed' => 'ok', 'not_completed' => 'bad'];
        $maxService = max(array_column($services, 'count') ?: [1]);
        $maxPurpose = max(array_column($purposes, 'total') ?: [1]);
    @endphp

    <div class="rf-db">
        <div class="rf-db-head">
            <div>
                <h2>مرحبًا، {{ Auth::user()->name }}</h2>
                <p>ملخص الموقع خلال {{ $periodLabel }} مقارنةً بالفترة السابقة لها · آخر تحديث للأرقام {{ $built_at->format('H:i') }}</p>
            </div>
            <div class="rf-db-periods" role="group" aria-label="الفترة">
                @foreach (\App\Services\Admin\DashboardStats::PERIODS as $key => $label)
                    <a href="{{ route('admin.dashboard', ['period' => $key]) }}" class="{{ $period === (string) $key ? 'active' : '' }}">{{ $label }}</a>
                @endforeach
            </div>
        </div>

        {{-- What waits for the team --}}
        <div class="rf-db-card">
            <h3>يحتاج تدخّل الفريق الآن</h3>
            @if (count($attention))
                <div class="rf-db-attn">
                    @foreach ($attention as $row)
                        <a href="{{ $row['url'] }}" class="{{ $row['hot'] ? 'hot' : '' }}"><span class="n">{{ $row['count'] }}</span> {{ $row['label'] }}</a>
                    @endforeach
                </div>
            @else
                <p class="rf-db-attn-ok"><i class="fa fa-check-circle"></i> لا يوجد شيء معلّق، كل شيء تحت السيطرة.</p>
            @endif
        </div>

        {{-- Users first --}}
        <div class="rf-db-grid">
            <a class="rf-db-kpi" style="--rf-k: var(--rf-a-blue)" href="{{ url('admin/users') }}">
                <div class="t"><i class="fa fa-users"></i> عملاء جدد</div>
                <div class="v">{{ number_format($kpis['clients']['value']) }}</div>
                <div class="s">{!! $change($kpis['clients']) !!} · إجمالي العملاء {{ number_format($now['clients']) }}</div>
            </a>
            <a class="rf-db-kpi" style="--rf-k: var(--rf-a-violet)" href="{{ route('admin.freelancers.index') }}">
                <div class="t"><i class="fa fa-user-tie"></i> مستقلون جدد</div>
                <div class="v">{{ number_format($kpis['freelancers']['value']) }}</div>
                <div class="s">{!! $change($kpis['freelancers']) !!} · معتمدون {{ number_format($now['approved_freelancers']) }} من {{ number_format($now['freelancers']) }}</div>
            </a>
            <a class="rf-db-kpi" href="{{ route('admin.analytics.index') }}">
                <div class="t"><i class="fa fa-eye"></i> زيارات (بعد الموافقة على التتبع)</div>
                <div class="v">{{ number_format($kpis['visits']['value']) }}</div>
                <div class="s">{!! $change($kpis['visits']) !!} · أتمّت طلبًا {{ number_format($kpis['converted']['value']) }}
                    @if ($kpis['visits']['value']) ({{ round($kpis['converted']['value'] * 100 / $kpis['visits']['value'], 1) }}%) @endif</div>
            </a>
            <div class="rf-db-kpi" style="--rf-k: #64748b">
                <div class="t"><i class="fa fa-user-friends"></i> كل الحسابات</div>
                <div class="v">{{ number_format($now['users']) }}</div>
                <div class="s">عملاء {{ number_format($now['clients']) }} · مستقلون {{ number_format($now['freelancers']) }}</div>
            </div>
        </div>

        {{-- Money --}}
        <div class="rf-db-grid">
            <div class="rf-db-kpi" style="--rf-k: #16a34a">
                <div class="t"><i class="fa fa-money-bill-wave"></i> المدفوعات (EasyKash)</div>
                <div class="v">{{ \App\Support\Money::format($kpis['revenue']['value']) }}</div>
                <div class="s">{!! $change($kpis['revenue']) !!} · {{ number_format($kpis['payments']['value']) }} عملية دفع</div>
            </div>
            <div class="rf-db-kpi" style="--rf-k: #16a34a">
                <div class="t"><i class="fa fa-percent"></i> عمولة المنصة المحصّلة</div>
                <div class="v">{{ \App\Support\Money::format($kpis['commission']['value']) }}</div>
                <div class="s">{!! $change($kpis['commission']) !!} · من الطلبات والمشاريع المعتمدة</div>
            </div>
            <div class="rf-db-kpi" style="--rf-k: var(--rf-a-amber)">
                <div class="t"><i class="fa fa-lock"></i> مبالغ لدى نفّذها الآن</div>
                <div class="v">{{ \App\Support\Money::format($now['held']) }}</div>
                <div class="s">تُصرف للمستقل عند اعتماد العمل</div>
            </div>
            <a class="rf-db-kpi" style="--rf-k: var(--rf-a-red)" href="{{ url('admin/withdraws') }}">
                <div class="t"><i class="fa fa-hand-holding-usd"></i> أرصدة المستخدمين</div>
                <div class="v">{{ \App\Support\Money::format($now['balances']) }}</div>
                <div class="s">طلبات سحب معلّقة: {{ \App\Support\Money::format($now['pending_withdraw_amount']) }}</div>
            </a>
        </div>

        {{-- Work --}}
        <div class="rf-db-grid">
            <a class="rf-db-kpi" style="--rf-k: var(--rf-a-blue)" href="{{ route('admin.requests.index') }}">
                <div class="t"><i class="fa fa-clipboard-list"></i> طلبات خدمات جديدة</div>
                <div class="v">{{ number_format($kpis['requests']['value']) }}</div>
                <div class="s">{!! $change($kpis['requests']) !!} · مفتوحة الآن {{ number_format($now['open_requests']) }}</div>
            </a>
            <div class="rf-db-kpi" style="--rf-k: var(--rf-a-blue)">
                <div class="t"><i class="fa fa-reply-all"></i> طلبات وصلها عرض</div>
                <div class="v">{{ $kpis['offers_rate']['value'] === null ? '—' : $kpis['offers_rate']['value'] . '%' }}</div>
                <div class="s">من طلبات {{ $periodLabel }} (بدون استفسارات الأسعار)</div>
            </div>
            <a class="rf-db-kpi" style="--rf-k: #16a34a" href="{{ route('admin.requests.index', ['tab' => 'completed']) }}">
                <div class="t"><i class="fa fa-check-double"></i> أعمال اكتملت</div>
                <div class="v">{{ number_format($kpis['completed']['value']) }}</div>
                <div class="s">{!! $change($kpis['completed']) !!} · قيد التنفيذ {{ number_format($now['in_progress']) }}</div>
            </a>
            <a class="rf-db-kpi" style="--rf-k: var(--rf-a-violet)" href="{{ route('admin.contracts.index') }}">
                <div class="t"><i class="fa fa-file-contract"></i> مشاريع بمراحل جارية</div>
                <div class="v">{{ number_format($now['active_contracts']) }}</div>
                <div class="s">اتفاق أو تنفيذ</div>
            </a>
            <a class="rf-db-kpi" style="--rf-k: var(--rf-a-amber)" href="{{ route('admin.help-requests.index') }}">
                <div class="t"><i class="fa fa-life-ring"></i> طلبات «ساعدوني أختار»</div>
                <div class="v">{{ number_format($kpis['help']['value']) }}</div>
                <div class="s">{!! $change($kpis['help']) !!}</div>
            </a>
            <a class="rf-db-kpi" style="--rf-k: #0ea5e9" href="{{ route('admin.site-tests.index') }}">
                <div class="t"><i class="fa fa-tachometer-alt"></i> فحوص مواقع مجانية</div>
                <div class="v">{{ number_format($kpis['site_tests']['value']) }}</div>
                <div class="s">{!! $change($kpis['site_tests']) !!}</div>
            </a>
        </div>

        <div class="rf-db-card">
            <h3>الحركة خلال {{ $periodLabel }}</h3>
            <div class="rf-db-chart"><canvas id="rf-db-chart" aria-label="رسم بياني للتسجيلات والطلبات والمدفوعات"></canvas></div>
        </div>

        <div class="row">
            <div class="col-lg-4">
                <div class="rf-db-card">
                    <h3>المدفوعات حسب النوع</h3>
                    @forelse ($purposes as $row)
                        <div class="rf-db-bars"><div class="row-b">
                            <span class="l" title="{{ $row['label'] }}">{{ $row['label'] }} ({{ $row['count'] }})</span>
                            <span class="b"><span style="width: {{ max(3, round($row['total'] * 100 / $maxPurpose)) }}%"></span></span>
                            <span class="c">{{ \App\Support\Money::format($row['total']) }}</span>
                        </div></div>
                    @empty
                        <p class="rf-db-empty">لا توجد مدفوعات في هذه الفترة.</p>
                    @endforelse
                </div>
            </div>
            <div class="col-lg-4">
                <div class="rf-db-card">
                    <h3>أكثر الخدمات طلبًا</h3>
                    @forelse ($services as $row)
                        <div class="rf-db-bars"><div class="row-b">
                            <span class="l" title="{{ $row['label'] }}">{{ $row['label'] }}</span>
                            <span class="b"><span style="width: {{ max(3, round($row['count'] * 100 / $maxService)) }}%"></span></span>
                            <span class="c">{{ $row['count'] }}</span>
                        </div></div>
                    @empty
                        <p class="rf-db-empty">لا توجد طلبات في هذه الفترة.</p>
                    @endforelse
                </div>
            </div>
            <div class="col-lg-4">
                <div class="rf-db-card">
                    <h3>من أين جاءت الطلبات</h3>
                    @forelse ($sources as $row)
                        <div class="rf-db-bars"><div class="row-b">
                            <span class="l">{{ $row['label'] }}</span>
                            <span class="b"><span style="width: {{ max(3, round($row['count'] * 100 / max(1, $kpis['requests']['value']))) }}%"></span></span>
                            <span class="c">{{ $row['count'] }}</span>
                        </div></div>
                    @empty
                        <p class="rf-db-empty">لا توجد طلبات في هذه الفترة.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-5">
                <div class="rf-db-card">
                    <h3>أحدث المستخدمين <a href="{{ url('admin/users') }}">كل المستخدمين</a></h3>
                    <div class="table-responsive">
                        <table class="rf-db-table">
                            <thead><tr><th>الاسم</th><th>النوع</th><th>التسجيل</th></tr></thead>
                            <tbody>
                                @forelse ($latestUsers as $u)
                                    <tr>
                                        <td>{{ $u->name }}<br><small class="text-muted">{{ $u->email }}</small></td>
                                        <td>
                                            <span class="rf-db-pill">{{ $u->typeLabel() }}</span>
                                            @if ($u->account_type === \App\Models\User::TYPE_FREELANCER && $u->freelancer_status === \App\Models\User::FREELANCER_PENDING)
                                                <span class="rf-db-pill warn">بانتظار الاعتماد</span>
                                            @endif
                                        </td>
                                        <td class="text-muted" title="{{ $u->created_at }}">{{ $u->created_at?->diffForHumans() }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="rf-db-empty">لا يوجد مستخدمون بعد.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-xl-7">
                <div class="rf-db-card">
                    <h3>أحدث طلبات الخدمات <a href="{{ route('admin.requests.index') }}">كل الطلبات</a></h3>
                    <div class="table-responsive">
                        <table class="rf-db-table">
                            <thead><tr><th>الطلب</th><th>الخدمة</th><th>العميل</th><th>الحالة</th><th>منذ</th></tr></thead>
                            <tbody>
                                @forelse ($latestRequests as $r)
                                    <tr>
                                        <td><a href="{{ route('admin.requests.show', $r) }}">{{ $r->number }}</a></td>
                                        <td>{{ $r->kind === \App\Models\ServiceRequest::KIND_STAGED ? ($r->project_title ?: 'مشروع بمراحل') : $r->service_name }}</td>
                                        <td>{{ $r->user?->name }}</td>
                                        <td><span class="rf-db-pill {{ $statusTone[$r->status] ?? '' }}">{{ \App\Models\ServiceRequest::STATUSES[$r->status] ?? $r->status }}</span></td>
                                        <td class="text-muted" title="{{ $r->created_at }}">{{ $r->created_at?->diffForHumans() }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="rf-db-empty">لا توجد طلبات بعد.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="rf-db-card">
            <h3>أحدث المدفوعات المؤكدة</h3>
            <div class="table-responsive">
                <table class="rf-db-table">
                    <thead><tr><th>#</th><th>الوصف</th><th>النوع</th><th>الدافع</th><th>المبلغ</th><th>وقت الدفع</th></tr></thead>
                    <tbody>
                        @forelse ($latestPayments as $p)
                            <tr>
                                <td class="text-muted">{{ $p->id }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($p->description, 60) }}</td>
                                <td><span class="rf-db-pill">{{ \App\Services\Admin\DashboardStats::PURPOSES[$p->purpose] ?? $p->purpose }}</span></td>
                                <td>{{ $p->user?->name ?? $p->contact_name }}</td>
                                <td class="num">{{ \App\Support\Money::format($p->paid_amount ?? $p->amount) }}</td>
                                <td class="text-muted" title="{{ $p->paid_at }}">{{ $p->paid_at?->diffForHumans() }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="rf-db-empty">لا توجد مدفوعات مؤكدة بعد.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <p class="rf-db-note">الأرقام تُحدَّث كل 5 دقائق. الزيارات تُحسب فقط لمن وافق على التتبع.</p>
        </div>
    </div>
@endsection

@section('js')
    <script src="{{ URL::asset('assets/admin/plugins/chart.js/Chart.bundle.min.js') }}"></script>
    <script>
        (function () {
            var el = document.getElementById('rf-db-chart');
            if (!el || typeof Chart === 'undefined') return;
            var data = @json($chart);
            Chart.defaults.global.defaultFontFamily = getComputedStyle(document.body).fontFamily;
            new Chart(el, {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: [
                        { type: 'line', label: 'المدفوعات (ج.م)', data: data.revenue, yAxisID: 'money', borderColor: '#16a34a', backgroundColor: 'rgba(22,163,74,.08)', pointRadius: 2, lineTension: .3, fill: true },
                        { label: 'مستخدمون جدد', data: data.users, yAxisID: 'count', backgroundColor: '#93c5fd' },
                        { label: 'طلبات جديدة', data: data.requests, yAxisID: 'count', backgroundColor: '#3FB699' }
                    ]
                },
                options: {
                    maintainAspectRatio: false,
                    legend: { position: 'bottom', rtl: true },
                    tooltips: { mode: 'index', intersect: false, rtl: true },
                    scales: {
                        xAxes: [{ gridLines: { display: false }, ticks: { autoSkip: true, maxTicksLimit: 15 } }],
                        yAxes: [
                            { id: 'count', position: 'right', ticks: { beginAtZero: true, precision: 0 } },
                            { id: 'money', position: 'left', ticks: { beginAtZero: true }, gridLines: { display: false } }
                        ]
                    }
                }
            });
        })();
    </script>
@endsection
