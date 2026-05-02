@extends('admin.layouts.master')

@section('content')
<div class="main-content app-content mt-0" dir="rtl">
    <div class="side-app">
        <div class="main-container container-fluid">

            {{-- Page Header --}}
            <div class="page-header">
                <h1 class="page-title">إدارة السوشيال ميديا</h1>
                <div>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item active">السوشيال ميديا</li>
                    </ol>
                </div>
            </div>

            @include('admin.layouts.alerts.success')
            @include('admin.layouts.alerts.errors')

            {{-- Stats Cards --}}
            <div class="row">
                <div class="col-xl-3 col-lg-6 col-md-6 col-xm-12">
                    <div class="card overflow-hidden sales-card bg-primary-gradient">
                        <div class="pl-3 pt-3 pr-3 pb-2 pt-0">
                            <div class="">
                                <h6 class="mb-3 tx-12 text-white">إجمالي البوستات</h6>
                            </div>
                            <div class="pb-0 mt-0">
                                <div class="d-flex">
                                    <div class="">
                                        <h4 class="tx-20 font-weight-semibold mb-1 text-white">{{ $totalPosts }}</h4>
                                        <p class="mb-0 tx-12 text-white op-7">كل البوستات</p>
                                    </div>
                                    <span class="float-right my-auto ml-auto">
                                        <i class="fas fa-share-alt text-white" style="font-size:2rem; opacity:.5"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 col-xm-12">
                    <div class="card overflow-hidden sales-card bg-info-gradient">
                        <div class="pl-3 pt-3 pr-3 pb-2 pt-0">
                            <div class="">
                                <h6 class="mb-3 tx-12 text-white">مجدولة</h6>
                            </div>
                            <div class="pb-0 mt-0">
                                <div class="d-flex">
                                    <div class="">
                                        <h4 class="tx-20 font-weight-semibold mb-1 text-white">{{ $scheduledPosts }}</h4>
                                        <p class="mb-0 tx-12 text-white op-7">في انتظار النشر</p>
                                    </div>
                                    <span class="float-right my-auto ml-auto">
                                        <i class="fas fa-clock text-white" style="font-size:2rem; opacity:.5"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 col-xm-12">
                    <div class="card overflow-hidden sales-card bg-success-gradient">
                        <div class="pl-3 pt-3 pr-3 pb-2 pt-0">
                            <div class="">
                                <h6 class="mb-3 tx-12 text-white">منشورة</h6>
                            </div>
                            <div class="pb-0 mt-0">
                                <div class="d-flex">
                                    <div class="">
                                        <h4 class="tx-20 font-weight-semibold mb-1 text-white">{{ $publishedPosts }}</h4>
                                        <p class="mb-0 tx-12 text-white op-7">تم النشر بنجاح</p>
                                    </div>
                                    <span class="float-right my-auto ml-auto">
                                        <i class="fas fa-check-circle text-white" style="font-size:2rem; opacity:.5"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 col-xm-12">
                    <div class="card overflow-hidden sales-card bg-danger-gradient">
                        <div class="pl-3 pt-3 pr-3 pb-2 pt-0">
                            <div class="">
                                <h6 class="mb-3 tx-12 text-white">فشل النشر</h6>
                            </div>
                            <div class="pb-0 mt-0">
                                <div class="d-flex">
                                    <div class="">
                                        <h4 class="tx-20 font-weight-semibold mb-1 text-white">{{ $failedPosts }}</h4>
                                        <p class="mb-0 tx-12 text-white op-7">تحتاج مراجعة</p>
                                    </div>
                                    <span class="float-right my-auto ml-auto">
                                        <i class="fas fa-times-circle text-white" style="font-size:2rem; opacity:.5"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Quick Actions + Connected Platforms --}}
            <div class="row">
                {{-- Quick Actions --}}
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">إجراءات سريعة</h3>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-3">
                                    <a href="{{ route('admin.social.post.create') }}" class="btn btn-primary btn-block">
                                        <i class="fas fa-plus-circle mb-1 d-block" style="font-size:1.8rem"></i>
                                        بوست جديد
                                    </a>
                                </div>
                                <div class="col-3">
                                    <a href="{{ route('admin.social.scheduled') }}" class="btn btn-info btn-block">
                                        <i class="fas fa-calendar-alt mb-1 d-block" style="font-size:1.8rem"></i>
                                        المجدولة
                                    </a>
                                </div>
                                <div class="col-3">
                                    <a href="{{ route('admin.social.published') }}" class="btn btn-success btn-block">
                                        <i class="fas fa-check-double mb-1 d-block" style="font-size:1.8rem"></i>
                                        المنشورة
                                    </a>
                                </div>
                                <div class="col-3">
                                    <a href="{{ route('admin.social.accounts') }}" class="btn btn-warning btn-block">
                                        <i class="fas fa-link mb-1 d-block" style="font-size:1.8rem"></i>
                                        ربط الحسابات
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Platforms Status --}}
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">حالة الحسابات</h3>
                        </div>
                        <div class="card-body p-2">
                            @php
                                $platformInfo = [
                                    'facebook'  => ['name' => 'Facebook',  'icon' => 'fab fa-facebook-f',  'color' => '#1877F2'],
                                    'instagram' => ['name' => 'Instagram', 'icon' => 'fab fa-instagram',   'color' => '#E4405F'],
                                    'tiktok'    => ['name' => 'TikTok',    'icon' => 'fab fa-tiktok',      'color' => '#000000'],
                                    'youtube'   => ['name' => 'YouTube',   'icon' => 'fab fa-youtube',     'color' => '#FF0000'],
                                    'linkedin'  => ['name' => 'LinkedIn',  'icon' => 'fab fa-linkedin-in', 'color' => '#0077B5'],
                                    'twitter'   => ['name' => 'Twitter',   'icon' => 'fab fa-twitter',     'color' => '#1DA1F2'],
                                ];
                            @endphp
                            @foreach($platformInfo as $key => $info)
                            <div class="d-flex align-items-center p-2 mb-1 rounded" style="background:#f8f9fa">
                                <div class="avatar avatar-sm mr-3 rounded-circle d-flex align-items-center justify-content-center"
                                     style="background:{{ $info['color'] }}; width:35px; height:35px; min-width:35px">
                                    <i class="{{ $info['icon'] }} text-white" style="font-size:.9rem"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <strong class="tx-13">{{ $info['name'] }}</strong>
                                </div>
                                @if(in_array($key, $connectedPlatforms))
                                    <span class="badge badge-success">متصل ✓</span>
                                @else
                                    <a href="{{ route('admin.social.accounts') }}" class="badge badge-light border">ربط</a>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- Recent Posts --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">آخر البوستات</h3>
                    <div class="card-options">
                        <a href="{{ route('admin.social.post.create') }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus"></i> بوست جديد
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>المحتوى</th>
                                    <th>النوع</th>
                                    <th>المنصات</th>
                                    <th>الحالة</th>
                                    <th>التاريخ</th>
                                    <th>إجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentPosts as $post)
                                <tr>
                                    <td>{{ $post->id }}</td>
                                    <td>
                                        <div style="max-width:250px; overflow:hidden; white-space:nowrap; text-overflow:ellipsis">
                                            {{ $post->content }}
                                        </div>
                                    </td>
                                    <td>
                                        <i class="{{ $post->media_type_icon }}"></i>
                                        {{ __($post->media_type) }}
                                    </td>
                                    <td>
                                        @foreach($post->platforms as $platform)
                                            @php
                                                $colors = ['facebook'=>'#1877F2','instagram'=>'#E4405F','tiktok'=>'#010101','youtube'=>'#FF0000','linkedin'=>'#0077B5','twitter'=>'#1DA1F2'];
                                                $icons  = ['facebook'=>'fab fa-facebook-f','instagram'=>'fab fa-instagram','tiktok'=>'fab fa-tiktok','youtube'=>'fab fa-youtube','linkedin'=>'fab fa-linkedin-in','twitter'=>'fab fa-twitter'];
                                            @endphp
                                            <span class="badge badge-pill text-white mr-1"
                                                  style="background:{{ $colors[$platform] ?? '#999' }}; font-size:.7rem">
                                                <i class="{{ $icons[$platform] ?? 'fas fa-share' }}"></i>
                                            </span>
                                        @endforeach
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $post->status_color }}">{{ $post->status_label }}</span>
                                    </td>
                                    <td class="tx-13 text-muted">{{ $post->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <a href="{{ route('admin.social.post.show', $post) }}" class="btn btn-sm btn-light">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if(in_array($post->status, ['failed','partial']))
                                        <form method="POST" action="{{ route('admin.social.post.retry', $post) }}" class="d-inline">
                                            @csrf
                                            <button class="btn btn-sm btn-warning" title="إعادة النشر">
                                                <i class="fas fa-redo"></i>
                                            </button>
                                        </form>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="fas fa-share-alt fa-3x mb-3 d-block op-3"></i>
                                        لا يوجد بوستات بعد. <a href="{{ route('admin.social.post.create') }}">أنشئ أول بوست الآن!</a>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
