@extends('admin.layouts.master')
@section('title')
تعديل الدرس: {{ $lesson->title }}
@endsection
@section('css')
<link href="{{ URL::asset('assets/admin/plugins/select2/css/select2.min.css') }}" rel="stylesheet">
<link href="{{ URL::asset('assets/admin/plugins/summernote/summernote-bs4.css') }}" rel="stylesheet">
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
                تعديل الدرس </span>
        </div>
    </div>
</div>
<!-- breadcrumb -->
<!-- row -->
<div class="row row-sm">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">تعديل الدرس: {{ $lesson->title }}</h5>
            </div>
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('admin.new-courses.lessons.update', [$course, $topic, $lesson]) }}" method="POST">
                    @csrf
                    {{-- @method('PUT')
                     --}}
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="title">عنوان الدرس <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="title" name="title" value="{{ old('title', $lesson->title) }}" required>
                            </div>

                            <div class="form-group">
                                <label for="description">وصف الدرس</label>
                                <textarea class="form-control" id="description" name="description" rows="4">{{ old('description', $lesson->description) }}</textarea>
                                <small class="text-muted">وصف موجز للدرس ومحتوياته</small>
                            </div>

                            <div class="form-group">
                                <label for="video_url">رابط فيديو يوتيوب <span class="text-danger">*</span></label>
                                <input type="url" class="form-control" id="video_url" name="video_url" value="{{ old('video_url', $lesson->video_url) }}" required>
                                <small class="text-muted">يجب أن يكون رابط فيديو unlisted من يوتيوب</small>
                                <div class="mt-2">
                                    <small class="text-info">
                                        <strong>أمثلة للروابط المقبولة:</strong><br>
                                        • https://www.youtube.com/watch?v=VIDEO_ID<br>
                                        • https://youtu.be/VIDEO_ID<br>
                                        • https://www.youtube.com/embed/VIDEO_ID<br>
                                        • https://www.youtube.com/shorts/VIDEO_ID<br>
                                        • https://www.youtube.com/watch?v=VIDEO_ID&feature=shorts
                                    </small>
                                </div>
                                @if($lesson->video_id)
                                    <div class="mt-2">
                                        <small class="text-info">
                                            <strong>Video ID الحالي:</strong> {{ $lesson->video_id }}
                                        </small>
                                    </div>
                                @endif
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="duration_minutes">المدة بالدقائق</label>
                                        <input type="number" class="form-control" id="duration_minutes" name="duration_minutes" value="{{ old('duration_minutes', $lesson->duration_minutes) }}" min="1">
                                        <small class="text-muted">مدة الفيديو بالدقائق</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="sort_order">ترتيب العرض</label>
                                        <input type="number" class="form-control" id="sort_order" name="sort_order" value="{{ old('sort_order', $lesson->sort_order) }}" min="0">
                                        <small class="text-muted">يستخدم لترتيب الدروس في الموضوع</small>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="is_free" name="is_free" value="1" {{ old('is_free', $lesson->is_free) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="is_free">درس مجاني (معاينة مجانية)</label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title">معلومات الدرس</h6>
                                    <p><strong>الكورس:</strong> {{ $course->title }}</p>
                                    <p><strong>الموضوع:</strong> {{ $topic->title }}</p>
                                    <p><strong>المدة:</strong> {{ $lesson->formatted_duration }}</p>
                                    <p><strong>النوع:</strong>
                                        @if($lesson->is_free)
                                            <span class="badge badge-success">مجاني</span>
                                        @else
                                            <span class="badge badge-info">مدفوع</span>
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <div class="card mt-3">
                                <div class="card-body">
                                    <h6 class="card-title">معاينة الفيديو</h6>
                                    @if($lesson->video_id)
                                        <img src="https://img.youtube.com/vi/{{ $lesson->video_id }}/hqdefault.jpg" alt="Video Thumbnail" class="img-fluid mb-2">
                                        <p class="mb-0"><strong>Video ID:</strong> {{ $lesson->video_id }}</p>
                                        <a href="{{ $lesson->video_url }}" target="_blank" class="btn btn-sm btn-info mt-2">
                                            <i class="fas fa-play"></i> مشاهدة الفيديو
                                        </a>
                                    @else
                                        <p class="text-muted">لا يوجد معرف فيديو</p>
                                    @endif
                                </div>
                            </div>

                            <div class="card mt-3">
                                <div class="card-body">
                                    <h6 class="card-title">روابط سريعة</h6>
                                    <div class="d-grid gap-2">
                                        <a href="{{ route('admin.new-courses.lessons.show', [$course, $topic, $lesson]) }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-eye"></i> عرض الدرس
                                        </a>
                                        <a href="{{ $lesson->video_url }}" target="_blank" class="btn btn-info btn-sm">
                                            <i class="fas fa-play"></i> مشاهدة الفيديو
                                        </a>
                                        <a href="{{ route('admin.new-courses.lessons.index', [$course, $topic]) }}" class="btn btn-secondary btn-sm">
                                            <i class="fas fa-arrow-right"></i> قائمة الدروس
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> تحديث الدرس
                        </button>
                        <a href="{{ route('admin.new-courses.lessons.index', [$course, $topic]) }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> إلغاء
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
@section('js')
<script src="{{ URL::asset('assets/admin/plugins/select2/js/select2.min.js') }}"></script>
<script src="{{ URL::asset('assets/admin/plugins/summernote/summernote-bs4.min.js') }}"></script>

<script>
$(document).ready(function() {
    // Summernote editor
    $('#description').summernote({
        height: 150,
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'italic', 'underline', 'clear']],
            ['fontname', ['fontname']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['table', ['table']],
            ['insert', ['link', 'picture', 'video']],
            ['view', ['fullscreen', 'codeview', 'help']]
        ]
    });
});
</script>
@endsection
