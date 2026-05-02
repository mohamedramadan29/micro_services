@extends('admin.layouts.master')

@section('content')
<div class="main-content app-content mt-0" dir="rtl">
    <div class="side-app">
        <div class="main-container container-fluid">

            <div class="page-header">
                <h1 class="page-title">تفاصيل البوست #{{ $post->id }}</h1>
                <div>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.social.index') }}">السوشيال ميديا</a></li>
                        <li class="breadcrumb-item active">تفاصيل البوست</li>
                    </ol>
                </div>
            </div>

            @include('admin.layouts.alerts.success')

            <div class="row">
                {{-- Post Content --}}
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">محتوى البوست</h3>
                            <div class="card-options">
                                <span class="badge badge-{{ $post->status_color }} px-3 py-2">
                                    {{ $post->status_label }}
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            @if($post->title)
                                <h5 class="font-weight-bold mb-3">{{ $post->title }}</h5>
                            @endif
                            <div class="p-3 bg-light rounded mb-3" style="white-space:pre-wrap; font-size:1rem; line-height:1.7">{{ $post->content }}</div>

                            {{-- Media --}}
                            @if($post->media_paths)
                            <div class="row">
                                @foreach($post->media_paths as $path)
                                <div class="col-md-4 mb-3">
                                    @if(in_array(pathinfo($path, PATHINFO_EXTENSION), ['jpg','jpeg','png','gif','webp']))
                                        <img src="{{ asset($path) }}" class="img-thumbnail w-100"
                                             style="height:180px; object-fit:cover" alt="Media">
                                    @else
                                        <video class="w-100 rounded" controls style="height:180px">
                                            <source src="{{ asset($path) }}">
                                        </video>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                            @endif

                            <div class="row text-center mt-3">
                                <div class="col-4">
                                    <small class="text-muted d-block">نوع المحتوى</small>
                                    <strong><i class="{{ $post->media_type_icon }}"></i> {{ $post->media_type }}</strong>
                                </div>
                                <div class="col-4">
                                    <small class="text-muted d-block">تاريخ الإنشاء</small>
                                    <strong>{{ $post->created_at->format('d/m/Y H:i') }}</strong>
                                </div>
                                <div class="col-4">
                                    <small class="text-muted d-block">
                                        {{ $post->scheduled_at ? 'وقت الجدولة' : 'تاريخ النشر' }}
                                    </small>
                                    <strong>
                                        {{ ($post->scheduled_at ?? $post->published_at)?->format('d/m/Y H:i') ?? '-' }}
                                    </strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Results per Platform --}}
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">نتائج النشر على المنصات</h3>
                        </div>
                        <div class="card-body p-0">
                            @php
                                $pColors = ['facebook'=>'#1877F2','instagram'=>'#E4405F','tiktok'=>'#010101','youtube'=>'#FF0000'];
                                $pIcons  = ['facebook'=>'fab fa-facebook-f','instagram'=>'fab fa-instagram','tiktok'=>'fab fa-tiktok','youtube'=>'fab fa-youtube'];
                            @endphp
                            @forelse($post->results as $result)
                            <div class="p-3 border-bottom d-flex align-items-center">
                                <div class="d-flex align-items-center justify-content-center rounded-circle text-white ml-3"
                                     style="background:{{ $pColors[$result->platform] ?? '#999' }}; width:42px; height:42px; min-width:42px">
                                    <i class="{{ $pIcons[$result->platform] ?? 'fas fa-share' }}" style="font-size:1.1rem"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <strong>{{ ucfirst($result->platform) }}</strong>
                                    @if($result->account)
                                        <small class="text-muted"> - {{ $result->account->account_name }}</small>
                                    @endif
                                    <div>
                                        <span class="badge badge-{{ $result->status_color }}">{{ $result->status_label }}</span>
                                        @if($result->published_at)
                                            <small class="text-muted mr-2">{{ $result->published_at->format('d/m/Y H:i') }}</small>
                                        @endif
                                        @if($result->platform_post_id)
                                            <small class="text-muted">ID: {{ $result->platform_post_id }}</small>
                                        @endif
                                    </div>
                                    @if($result->error_message)
                                        <div class="alert alert-danger py-1 px-2 mt-1 mb-0 tx-12">
                                            <i class="fas fa-exclamation-triangle ml-1"></i> {{ $result->error_message }}
                                        </div>
                                    @endif
                                </div>
                                {{-- Engagement --}}
                                @if($result->engagement)
                                <div class="text-center mr-3">
                                    <div class="d-flex gap-3">
                                        @if(isset($result->engagement['likes']))
                                        <div class="text-center px-2">
                                            <i class="fas fa-heart text-danger"></i>
                                            <div class="tx-13 font-weight-bold">{{ number_format($result->engagement['likes']) }}</div>
                                            <small class="text-muted">إعجاب</small>
                                        </div>
                                        @endif
                                        @if(isset($result->engagement['comments']))
                                        <div class="text-center px-2">
                                            <i class="fas fa-comment text-info"></i>
                                            <div class="tx-13 font-weight-bold">{{ number_format($result->engagement['comments']) }}</div>
                                            <small class="text-muted">تعليق</small>
                                        </div>
                                        @endif
                                        @if(isset($result->engagement['shares']))
                                        <div class="text-center px-2">
                                            <i class="fas fa-share text-success"></i>
                                            <div class="tx-13 font-weight-bold">{{ number_format($result->engagement['shares']) }}</div>
                                            <small class="text-muted">مشاركة</small>
                                        </div>
                                        @endif
                                        @if(isset($result->engagement['views']))
                                        <div class="text-center px-2">
                                            <i class="fas fa-eye text-warning"></i>
                                            <div class="tx-13 font-weight-bold">{{ number_format($result->engagement['views']) }}</div>
                                            <small class="text-muted">مشاهدة</small>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                @endif
                            </div>
                            @empty
                            <div class="text-center p-4 text-muted">لا توجد نتائج نشر بعد</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- Actions Sidebar --}}
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header"><h3 class="card-title">إجراءات</h3></div>
                        <div class="card-body">
                            @if(in_array($post->status, ['failed','partial']))
                            <form method="POST" action="{{ route('admin.social.post.retry', $post) }}" class="mb-2">
                                @csrf
                                <button class="btn btn-warning btn-block">
                                    <i class="fas fa-redo ml-1"></i> إعادة النشر
                                </button>
                            </form>
                            @endif
                            <a href="{{ route('admin.social.index') }}" class="btn btn-light btn-block mb-2">
                                <i class="fas fa-arrow-right ml-1"></i> العودة للوحة
                            </a>
                            <form method="POST" action="{{ route('admin.social.post.delete', $post) }}"
                                  onsubmit="return confirm('هل تريد حذف هذا البوست نهائياً؟')">
                                @csrf
                                <button class="btn btn-outline-danger btn-block">
                                    <i class="fas fa-trash ml-1"></i> حذف البوست
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Platforms Summary --}}
                    <div class="card">
                        <div class="card-header"><h3 class="card-title">المنصات المحددة</h3></div>
                        <div class="card-body p-2">
                            @foreach($post->platforms as $platform)
                            <div class="d-flex align-items-center p-2 mb-1 rounded" style="background:#f8f9fa">
                                <span class="d-flex align-items-center justify-content-center rounded-circle text-white ml-2"
                                      style="background:{{ $pColors[$platform] ?? '#999' }}; width:30px; height:30px; min-width:30px; font-size:.75rem">
                                    <i class="{{ $pIcons[$platform] ?? 'fas fa-share' }}"></i>
                                </span>
                                <strong>{{ ucfirst($platform) }}</strong>
                                @php $r = $post->results->where('platform', $platform)->first(); @endphp
                                @if($r)
                                    <span class="badge badge-{{ $r->status_color }} mr-auto">{{ $r->status_label }}</span>
                                @else
                                    <span class="badge badge-secondary mr-auto">لم ينشر</span>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
