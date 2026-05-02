@extends('admin.layouts.master')

@section('content')
<div class="main-content app-content mt-0" dir="rtl">
    <div class="side-app">
        <div class="main-container container-fluid">

            <div class="page-header">
                <h1 class="page-title">البوستات المنشورة</h1>
                <div>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.social.index') }}">السوشيال ميديا</a></li>
                        <li class="breadcrumb-item active">المنشورة</li>
                    </ol>
                </div>
            </div>

            @include('admin.layouts.alerts.success')

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-check-double text-success ml-2"></i>
                        البوستات المنشورة ({{ $posts->total() }})
                    </h3>
                    <div class="card-options">
                        <a href="{{ route('admin.social.post.create') }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus ml-1"></i> بوست جديد
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
                                    <th>المنصات ونتائج النشر</th>
                                    <th>الحالة</th>
                                    <th>تاريخ النشر</th>
                                    <th>إجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($posts as $post)
                                <tr>
                                    <td>{{ $post->id }}</td>
                                    <td style="max-width:220px">
                                        <div style="overflow:hidden; white-space:nowrap; text-overflow:ellipsis; font-size:.9rem">
                                            {{ $post->content }}
                                        </div>
                                        @if($post->media_type !== 'text')
                                            <small class="text-muted"><i class="{{ $post->media_type_icon }}"></i> {{ $post->media_type }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $pColors = ['facebook'=>'#1877F2','instagram'=>'#E4405F','tiktok'=>'#010101','youtube'=>'#FF0000'];
                                            $pIcons  = ['facebook'=>'fab fa-facebook-f','instagram'=>'fab fa-instagram','tiktok'=>'fab fa-tiktok','youtube'=>'fab fa-youtube'];
                                        @endphp
                                        @foreach($post->results as $result)
                                        <div class="d-flex align-items-center mb-1">
                                            <span class="d-inline-flex align-items-center justify-content-center rounded-circle text-white ml-1"
                                                  style="background:{{ $pColors[$result->platform] ?? '#999' }}; width:22px; height:22px; min-width:22px; font-size:.65rem">
                                                <i class="{{ $pIcons[$result->platform] ?? 'fas fa-share' }}"></i>
                                            </span>
                                            <span class="badge badge-{{ $result->status_color }} tx-11">{{ $result->status_label }}</span>
                                            @if($result->engagement)
                                                <small class="text-muted mr-1">
                                                    <i class="fas fa-heart tx-11"></i> {{ $result->engagement['likes'] ?? 0 }}
                                                    <i class="fas fa-comment tx-11 mr-1"></i> {{ $result->engagement['comments'] ?? 0 }}
                                                </small>
                                            @endif
                                        </div>
                                        @endforeach
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $post->status_color }}">{{ $post->status_label }}</span>
                                    </td>
                                    <td class="text-muted tx-13">
                                        {{ $post->published_at ? $post->published_at->format('d/m/Y H:i') : '-' }}
                                    </td>
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
                                        <form method="POST" action="{{ route('admin.social.post.delete', $post) }}"
                                              onsubmit="return confirm('حذف هذا البوست؟')" class="d-inline">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="fas fa-check-double fa-3x mb-3 d-block op-3"></i>
                                        لا يوجد بوستات منشورة بعد
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($posts->hasPages())
                <div class="card-footer">{{ $posts->links() }}</div>
                @endif
            </div>

        </div>
    </div>
</div>
@endsection
