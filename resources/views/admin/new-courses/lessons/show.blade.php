@extends('admin.layouts.master')
@section('title')
عرض الدرس: {{ $lesson->title }}
@endsection
@section('css')
@endsection
@section('content')
<!-- breadcrumb -->
<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto">الرئيسية </h4><span class="text-muted mt-1 tx-13 mr-2 mb-0">/
                الكورسات الجديدة </span><span class="text-muted mt-1 tx-13 mr-2 mb-0">/
                {{ $course->title }} </span><span class="text-muted mt-1 tx-13 mr-2 mb-0">/
                {{ $topic->title }} </span><span class="text-muted mt-1 tx-13 mr-2 mb-0">/
                {{ $lesson->title }} </span>
        </div>
    </div>
    <div class="my-auto">
        <a href="{{ $lesson->video_url }}" target="_blank" class="btn btn-primary">
            <i class="fas fa-play"></i> مشاهدة الفيديو
        </a>
        <a href="{{ route('admin.new-courses.lessons.edit', [$course, $topic, $lesson]) }}" class="btn btn-warning">
            <i class="fas fa-edit"></i> تعديل الدرس
        </a>
    </div>
</div>
<!-- breadcrumb -->
<!-- row -->
<div class="row row-sm">
    <!-- Lesson Info -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">معلومات الدرس</h5>
            </div>
            <div class="card-body">
                <h4>{{ $lesson->title }}</h4>
                
                @if($lesson->description)
                    <div class="mb-3">
                        <strong>الوصف:</strong>
                        <p>{!! $lesson->description !!}</p>
                    </div>
                @endif

                <div class="row mb-3">
                    <div class="col-6">
                        <strong>المدة:</strong>
                        <p>{{ $lesson->formatted_duration }}</p>
                    </div>
                    <div class="col-6">
                        <strong>النوع:</strong>
                        <p>
                            @if($lesson->is_free)
                                <span class="badge badge-success">مجاني</span>
                            @else
                                <span class="badge badge-info">مدفوع</span>
                            @endif
                        </p>
                    </div>
                </div>

                <div class="mb-3">
                    <strong>ترتيب العرض:</strong>
                    <p>{{ $lesson->sort_order }}</p>
                </div>

                <div class="mb-3">
                    <strong>الموضوع:</strong>
                    <p>{{ $topic->title }}</p>
                </div>

                <div class="mb-3">
                    <strong>الكورس:</strong>
                    <p>{{ $course->title }}</p>
                </div>

                @if($lesson->video_id)
                    <div class="mb-3">
                        <strong>Video ID:</strong>
                        <p><code>{{ $lesson->video_id }}</code></p>
                    </div>
                @endif

                <div class="d-grid gap-2">
                    <a href="{{ $lesson->video_url }}" target="_blank" class="btn btn-primary">
                        <i class="fas fa-play"></i> مشاهدة الفيديو
                    </a>
                    <a href="{{ route('admin.new-courses.lessons.edit', [$course, $topic, $lesson]) }}" class="btn btn-warning">
                        <i class="fas fa-edit"></i> تعديل الدرس
                    </a>
                    <a href="{{ route('admin.new-courses.lessons.index', [$course, $topic]) }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-right"></i> العودة للدروس
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Video Preview -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">معاينة الفيديو</h5>
            </div>
            <div class="card-body">
                @if($lesson->video_id)
                    <div class="embed-responsive embed-responsive-16by9">
                        <iframe class="embed-responsive-item" 
                                src="{{ $lesson->video_embed_url }}" 
                                frameborder="0" 
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                allowfullscreen>
                        </iframe>
                    </div>
                    
                    <div class="mt-3">
                        <h6>معلومات الفيديو:</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>رابط الفيديو:</strong></p>
                                <a href="{{ $lesson->video_url }}" target="_blank" class="btn btn-sm btn-info">
                                    <i class="fas fa-external-link-alt"></i> فتح في يوتيوب
                                </a>
                            </div>
                            <div class="col-md-6">
                                <p><strong>معرف الفيديو:</strong></p>
                                <code>{{ $lesson->video_id }}</code>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> 
                        لا يمكن عرض الفيديو. يرجى التأكد من صحة رابط الفيديو.
                    </div>
                    
                    <div class="text-center">
                        <img src="{{ asset('assets/admin/img/no-video.png') }}" alt="No Video" style="max-width: 200px; opacity: 0.5;">
                    </div>
                @endif
            </div>
        </div>

        <!-- Video Thumbnail -->
        @if($lesson->video_id)
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">صورة المصغرة للفيديو</h5>
                </div>
                <div class="card-body text-center">
                    <img src="{{ $lesson->thumbnail_url }}" alt="Video Thumbnail" class="img-fluid" style="max-width: 400px;">
                    <div class="mt-2">
                        <small class="text-muted">الأبعاد المقترحة: 1280x720 بكسل</small>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
@section('js')
<script>
$(document).ready(function() {
    // Any additional JavaScript can be added here
});
</script>
@endsection
