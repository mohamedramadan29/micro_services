@extends('admin.layouts.master')
@section('title')
    المستخدمون
@endsection
@section('content')
    @use('App\Http\Controllers\admin\UserController', 'Users')
    @use('App\Models\User')
    @php
        $typeTone = [User::TYPE_CLIENT => 'rf-db-pill', User::TYPE_FREELANCER => 'rf-db-pill warn', User::TYPE_ADMIN => 'rf-db-pill bad', User::TYPE_EMPLOYEE => 'rf-db-pill'];
        $freelancerTone = [User::FREELANCER_APPROVED => 'ok', User::FREELANCER_PENDING => 'warn', User::FREELANCER_REJECTED => 'bad'];
    @endphp

    <div class="rf-db rf-us">
        <div class="rf-db-head">
            <div>
                <h2>المستخدمون</h2>
                <p>كل الحسابات المسجلة. «العميل» يطلب الخدمات، و«المستقل» ينفّذها ويقدّم العروض.</p>
            </div>
            <a class="btn btn-outline-secondary btn-sm" href="{{ url('admin/users') . '?' . http_build_query(array_filter($filters + ['tab' => $tab]) + ['export' => 'csv']) }}">
                <i class="fa fa-download ml-1"></i> تصدير CSV للنتائج
            </a>
        </div>

        @if (Session::has('Error_Message'))
            <div class="alert alert-danger">{{ Session::get('Error_Message') }}</div>
        @endif

        <div class="rf-us-tabs">
            @foreach (Users::TABS as $key => [$label])
                <a href="{{ url('admin/users') . '?' . http_build_query(array_filter(['tab' => $key === 'all' ? null : $key] + $filters)) }}" class="{{ $tab === $key ? 'active' : '' }}">
                    {{ $label }} <span>{{ number_format($counts[$key]) }}</span>
                </a>
            @endforeach
        </div>

        <form method="get" action="{{ url('admin/users') }}" class="rf-db-card rf-us-filters">
            @if ($tab !== 'all')
                <input type="hidden" name="tab" value="{{ $tab }}">
            @endif
            <input type="search" name="q" value="{{ $filters['q'] }}" class="form-control" placeholder="ابحث بالاسم أو البريد أو الهاتف أو اسم المستخدم">
            <select name="state" class="form-control">
                <option value="">كل الحالات</option>
                @foreach (Users::STATES as $key => $label)
                    <option value="{{ $key }}" @selected($filters['state'] === $key)>{{ $label }}</option>
                @endforeach
            </select>
            <select name="sort" class="form-control">
                @foreach (Users::SORTS as $key => $label)
                    <option value="{{ $key }}" @selected($filters['sort'] === $key)>ترتيب: {{ $label }}</option>
                @endforeach
            </select>
            <button class="btn btn-primary">تصفية</button>
            @if ($filters['q'] !== '' || $filters['state'] !== '' || $filters['sort'] !== 'newest')
                <a href="{{ url('admin/users') . ($tab !== 'all' ? '?tab=' . $tab : '') }}" class="btn btn-light">مسح</a>
            @endif
        </form>

        <div class="rf-db-card">
            <div class="table-responsive">
                <table class="rf-db-table rf-us-table">
                    <thead>
                        <tr>
                            <th>المستخدم</th>
                            <th>التواصل</th>
                            <th>نوع الحساب</th>
                            <th>البريد</th>
                            <th>الحالة</th>
                            <th>الرصيد</th>
                            <th>التسجيل</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            @php
                                $state = Users::stateOf($user);
                            @endphp
                            <tr>
                                <td>
                                    <div class="rf-us-who">
                                        <img src="{{ $user->avatarUrl(128) }}" alt="" loading="lazy">
                                        <div>
                                            <strong>{{ $user->name }}</strong>
                                            <small>#{{ $user->id }}@if ($user->user_name) · {{ $user->user_name }}@endif</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div dir="ltr" class="text-right">{{ $user->email }}</div>
                                    @if ($user->phone)<small class="text-muted d-block" dir="ltr">{{ $user->phone }}</small>@endif
                                    @if ($user->whatsapp)
                                        <a href="https://wa.me/{{ preg_replace('/\D+/', '', $user->whatsapp) }}" target="_blank" rel="noopener" class="d-block small"><i class="fab fa-whatsapp"></i> <span dir="ltr">{{ $user->whatsapp }}</span></a>
                                    @endif
                                </td>
                                <td>
                                    <span class="{{ $typeTone[$user->account_type] ?? 'rf-db-pill' }}">{{ $user->typeLabel() }}</span>
                                    @if ($user->account_type === User::TYPE_FREELANCER)
                                        <span class="rf-db-pill {{ $freelancerTone[$user->freelancer_status] ?? '' }}">{{ User::FREELANCER_STATUSES[$user->freelancer_status] ?? 'قيد المراجعة' }}</span>
                                    @elseif ($user->account_type === User::TYPE_ADMIN && $user->admin_role)
                                        <small class="d-block text-muted">{{ \Illuminate\Support\Str::before(\App\Support\StaffPermissions::ROLES[$user->admin_role] ?? '', ' (') }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if ($user->email_verified_at)
                                        <span class="rf-db-pill ok">مؤكد</span>
                                    @else
                                        <span class="rf-db-pill warn">غير مؤكد</span>
                                    @endif
                                </td>
                                <td><span class="rf-db-pill {{ $state === 'نشط' ? 'ok' : 'bad' }}">{{ $state }}</span></td>
                                <td class="num">{{ \App\Support\Money::format($user->balance ?? 0) }}</td>
                                <td class="text-muted" title="{{ $user->created_at }}">{{ $user->created_at?->format('Y-m-d') }}<small class="d-block">{{ $user->created_at?->diffForHumans() }}</small></td>
                                <td class="rf-us-actions">
                                    @if ($user->account_type === User::TYPE_FREELANCER)
                                        <a class="btn btn-light btn-sm" href="{{ route('admin.freelancers.index', ['q' => $user->email]) }}">ملف المستقل</a>
                                    @elseif ($user->account_type === User::TYPE_CLIENT)
                                        <a class="btn btn-light btn-sm" href="{{ route('admin.clients.show', $user) }}">ملف العميل</a>
                                    @endif
                                    @if ($user->id !== auth()->id())
                                        <form action="{{ url('admin/block_status/' . $user->id) }}" method="post"
                                              onsubmit="return confirm('{{ (string) $user->block_status === '0' ? 'فك حظر هذا المستخدم؟' : 'حظر هذا المستخدم؟ لن يستطيع تسجيل الدخول.' }}')">
                                            @csrf
                                            @if ((string) $user->block_status === '0')
                                                <button class="btn btn-outline-success btn-sm">فك الحظر</button>
                                            @else
                                                <button class="btn btn-outline-danger btn-sm">حظر</button>
                                            @endif
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="rf-db-empty text-center py-4">لا يوجد مستخدمون مطابقون.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $users->links() }}</div>
        </div>
    </div>
@endsection
