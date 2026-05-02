@extends('admin.layouts.master')

@section('content')
<div class="main-content app-content mt-0" dir="rtl">
    <div class="side-app">
        <div class="main-container container-fluid">

            <div class="page-header">
                <h1 class="page-title">ربط حسابات السوشيال ميديا</h1>
                <div>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.social.index') }}">السوشيال ميديا</a></li>
                        <li class="breadcrumb-item active">الحسابات</li>
                    </ol>
                </div>
            </div>

            @include('admin.layouts.alerts.success')
            @include('admin.layouts.alerts.errors')

            {{-- Platform Connect Cards --}}
            <div class="row">
                @php
                    $platformDefs = [
                        'facebook'  => [
                            'name'        => 'Facebook',
                            'desc'        => 'نشر بوستات، صور، وفيديوهات على صفحاتك',
                            'icon'        => 'fab fa-facebook-f',
                            'color'       => '#1877F2',
                            'connect_url' => route('admin.social.facebook.connect'),
                            'badge'       => 'بوستات | صور | فيديو',
                        ],
                        'instagram' => [
                            'name'        => 'Instagram',
                            'desc'        => 'نشر صور، ريلز، وستوريز على حساب Business',
                            'icon'        => 'fab fa-instagram',
                            'color'       => '#E4405F',
                            'connect_url' => route('admin.social.instagram.connect'),
                            'badge'       => 'صور | ريلز | ستوريز',
                        ],
                        'tiktok'    => [
                            'name'        => 'TikTok',
                            'desc'        => 'رفع ونشر فيديوهات TikTok مباشرة',
                            'icon'        => 'fab fa-tiktok',
                            'color'       => '#010101',
                            'connect_url' => route('admin.social.tiktok.connect'),
                            'badge'       => 'فيديو فقط',
                        ],
                        'youtube'   => [
                            'name'        => 'YouTube',
                            'desc'        => 'رفع ونشر فيديوهات على قناتك',
                            'icon'        => 'fab fa-youtube',
                            'color'       => '#FF0000',
                            'connect_url' => route('admin.social.youtube.connect'),
                            'badge'       => 'فيديو فقط',
                        ],
                        'linkedin'  => [
                            'name'        => 'LinkedIn',
                            'desc'        => 'نشر تحديثات احترافية على ملفك الشخصي',
                            'icon'        => 'fab fa-linkedin-in',
                            'color'       => '#0077B5',
                            'connect_url' => route('admin.social.linkedin.connect'),
                            'badge'       => 'بوستات نصية',
                        ],
                        'twitter'   => [
                            'name'        => 'Twitter (X)',
                            'desc'        => 'نشر تغريدات سريعة لمتابعيك',
                            'icon'        => 'fab fa-twitter',
                            'color'       => '#1DA1F2',
                            'connect_url' => route('admin.social.twitter.connect'),
                            'badge'       => 'تغريدات نصية',
                        ],
                    ];
                    $connectedByPlatform = $accounts->keyBy('platform');
                @endphp

                @foreach($platformDefs as $key => $def)
                <div class="col-md-6 col-xl-3">
                    <div class="card" style="border-top: 4px solid {{ $def['color'] }}">
                        <div class="card-body text-center">
                            <div class="avatar avatar-xl rounded-circle mb-3 mx-auto d-flex align-items-center justify-content-center"
                                 style="background: {{ $def['color'] }}; width:64px; height:64px">
                                <i class="{{ $def['icon'] }} text-white" style="font-size:1.8rem"></i>
                            </div>
                            <h4 class="font-weight-bold mb-1">{{ $def['name'] }}</h4>
                            <p class="text-muted tx-13 mb-2">{{ $def['desc'] }}</p>
                            <span class="badge badge-light border mb-3">{{ $def['badge'] }}</span>

                            @if(isset($connectedByPlatform[$key]))
                                @php $acc = $connectedByPlatform[$key]; @endphp
                                <div class="alert alert-success py-2 mb-2">
                                    <i class="fas fa-check-circle"></i>
                                    <strong>{{ $acc->account_name }}</strong>
                                    @if($acc->page_name && $acc->page_name !== $acc->account_name)
                                        <br><small class="text-muted">{{ $acc->page_name }}</small>
                                    @endif
                                </div>
                                <div class="d-flex gap-2 justify-content-center">
                                    <form method="POST" action="{{ route('admin.social.toggle', $acc) }}" class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm {{ $acc->is_active ? 'btn-warning' : 'btn-success' }}">
                                            <i class="fas fa-{{ $acc->is_active ? 'pause' : 'play' }}"></i>
                                            {{ $acc->is_active ? 'تعطيل' : 'تفعيل' }}
                                        </button>
                                    </form>
                                    <a href="{{ $def['connect_url'] }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-sync"></i> إعادة ربط
                                    </a>
                                    <form method="POST" action="{{ route('admin.social.delete', $acc) }}"
                                          onsubmit="return confirm('هل تريد حذف هذا الحساب؟')" class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            @else
                                <a href="{{ $def['connect_url'] }}" class="btn btn-block text-white"
                                   style="background: {{ $def['color'] }}">
                                    <i class="fas fa-link ml-1"></i> ربط الحساب
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- All Connected Accounts Table --}}
            @if($accounts->count() > 0)
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">جميع الحسابات المربوطة ({{ $accounts->count() }})</h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table">
                            <thead>
                                <tr>
                                    <th>المنصة</th>
                                    <th>اسم الحساب</th>
                                    <th>الصفحة</th>
                                    <th>حالة التوكن</th>
                                    <th>الحالة</th>
                                    <th>إجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($accounts as $account)
                                <tr>
                                    <td>
                                        <span class="badge text-white px-3 py-2"
                                              style="background: {{ $account->platform_color }}; font-size:.85rem">
                                            <i class="{{ $account->platform_icon }} ml-1"></i>
                                            {{ $account->platform_label }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($account->avatar)
                                            <img src="{{ $account->avatar }}" class="avatar avatar-sm rounded-circle ml-2" alt="">
                                        @endif
                                        <strong>{{ $account->account_name }}</strong>
                                    </td>
                                    <td>{{ $account->page_name ?? '-' }}</td>
                                    <td>
                                        @if($account->token_expires_at)
                                            @if($account->isTokenExpired())
                                                <span class="badge badge-danger">منتهي الصلاحية</span>
                                            @else
                                                <span class="badge badge-success">ساري - {{ $account->token_expires_at->diffForHumans() }}</span>
                                            @endif
                                        @else
                                            <span class="badge badge-success">دائم</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $account->is_active ? 'success' : 'secondary' }}">
                                            {{ $account->is_active ? 'مفعّل' : 'معطّل' }}
                                        </span>
                                    </td>
                                    <td>
                                        <form method="POST" action="{{ route('admin.social.toggle', $account) }}" class="d-inline">
                                            @csrf
                                            <button class="btn btn-sm btn-{{ $account->is_active ? 'warning' : 'success' }}">
                                                {{ $account->is_active ? 'تعطيل' : 'تفعيل' }}
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.social.delete', $account) }}"
                                              onsubmit="return confirm('هل تريد حذف الحساب؟')" class="d-inline">
                                            @csrf
                                            <button class="btn btn-sm btn-danger">حذف</button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>
</div>
@endsection
