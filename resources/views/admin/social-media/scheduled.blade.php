@extends('admin.layouts.master')

@section('content')
<div class="main-content app-content mt-0" dir="rtl">
    <div class="side-app">
        <div class="main-container container-fluid">

            <div class="page-header">
                <h1 class="page-title">البوستات المجدولة</h1>
                <div>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.social.index') }}">السوشيال ميديا</a></li>
                        <li class="breadcrumb-item active">المجدولة</li>
                    </ol>
                </div>
            </div>

            @include('admin.layouts.alerts.success')
            @include('admin.layouts.alerts.errors')

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-calendar-alt text-info ml-2"></i>
                        البوستات المجدولة ({{ $posts->total() }})
                    </h3>
                    <div class="card-options">
                        <a href="{{ route('admin.social.post.create') }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus ml-1"></i> بوست جديد
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    @forelse($posts as $post)
                    <div class="p-4 border-bottom">
                        <div class="d-flex align-items-start">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center mb-2">
                                    <span class="badge badge-info ml-2">
                                        <i class="fas fa-clock ml-1"></i>
                                        {{ $post->scheduled_at->format('d/m/Y - H:i') }}
                                    </span>
                                    <span class="badge badge-{{ $post->status_color }}">{{ $post->status_label }}</span>
                                    <small class="text-muted mr-2">
                                        ({{ $post->scheduled_at->diffForHumans() }})
                                    </small>
                                </div>

                                {{-- Content preview --}}
                                <p class="mb-2" style="max-height:60px; overflow:hidden; text-overflow:ellipsis">
                                    {{ $post->content }}
                                </p>

                                {{-- Platforms --}}
                                <div class="mb-0">
                                    @php
                                        $pColors = ['facebook'=>'#1877F2','instagram'=>'#E4405F','tiktok'=>'#010101','youtube'=>'#FF0000'];
                                        $pIcons  = ['facebook'=>'fab fa-facebook-f','instagram'=>'fab fa-instagram','tiktok'=>'fab fa-tiktok','youtube'=>'fab fa-youtube'];
                                    @endphp
                                    @foreach($post->platforms as $platform)
                                        <span class="badge text-white px-2 py-1 ml-1"
                                              style="background:{{ $pColors[$platform] ?? '#999' }}">
                                            <i class="{{ $pIcons[$platform] ?? 'fas fa-share' }} ml-1"></i>
                                            {{ ucfirst($platform) }}
                                        </span>
                                    @endforeach
                                    @if($post->media_type !== 'text')
                                        <span class="badge badge-light border mr-1">
                                            <i class="{{ $post->media_type_icon }}"></i> {{ $post->media_type }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Actions --}}
                            <div class="mr-3 d-flex flex-column gap-1" style="min-width:130px">
                                <a href="{{ route('admin.social.post.show', $post) }}" class="btn btn-sm btn-light mb-1">
                                    <i class="fas fa-eye ml-1"></i> تفاصيل
                                </a>
                                <form method="POST" action="{{ route('admin.social.post.delete', $post) }}"
                                      onsubmit="return confirm('هل تريد حذف هذا البوست المجدول؟')">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-danger btn-block">
                                        <i class="fas fa-trash ml-1"></i> حذف
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-calendar-alt fa-3x mb-3 d-block op-3"></i>
                        <p>لا يوجد بوستات مجدولة حالياً</p>
                        <a href="{{ route('admin.social.post.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus ml-1"></i> جدول بوست جديد
                        </a>
                    </div>
                    @endforelse
                </div>
                @if($posts->hasPages())
                <div class="card-footer">
                    {{ $posts->links() }}
                </div>
                @endif
            </div>

        </div>
    </div>
</div>
@endsection
