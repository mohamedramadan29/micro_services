@extends('admin.layouts.master')
@section('title')
تعديل الموضوع: {{ $topic->title }}
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
                تعديل الموضوع </span>
        </div>
    </div>
</div>
<!-- breadcrumb -->
<!-- row -->
<div class="row row-sm">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">تعديل الموضوع: {{ $topic->title }}</h5>
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

                <form action="{{ route('admin.new-courses.topics.update', [$course, $topic]) }}" method="POST">
                    @csrf
                    {{-- @method('PUT')
                     --}}
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="title">عنوان الموضوع <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="title" name="title" value="{{ old('title', $topic->title) }}" required>
                            </div>

                            <div class="form-group">
                                <label for="description">وصف الموضوع</label>
                                <textarea class="form-control" id="description" name="description" rows="6">{{ old('description', $topic->description) }}</textarea>
                                <small class="text-muted">وصف تفصيلي للموضوع ومحتوياته</small>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="sort_order">ترتيب العرض</label>
                                <input type="number" class="form-control" id="sort_order" name="sort_order" value="{{ old('sort_order', $topic->sort_order) }}" min="0">
                                <small class="text-muted">يستخدم لترتيب المواضيع في الكورس</small>
                            </div>

                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title">معلومات الموضوع</h6>
                                    <p><strong>الكورس:</strong> {{ $course->title }}</p>
                                    <p><strong>عدد الدروس:</strong> {{ $topic->lessons_count }}</p>
                                    <p><strong>المدة الإجمالية:</strong> {{ $topic->total_duration }} دقيقة</p>
                                </div>
                            </div>

                            <div class="card mt-3">
                                <div class="card-body">
                                    <h6 class="card-title">روابط سريعة</h6>
                                    <div class="d-grid gap-2">
                                        <a href="{{ route('admin.new-courses.lessons.index', [$course, $topic]) }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-list"></i> إدارة الدروس
                                        </a>
                                        <a href="{{ route('admin.new-courses.topics.show', [$course, $topic]) }}" class="btn btn-info btn-sm">
                                            <i class="fas fa-eye"></i> عرض الموضوع
                                        </a>
                                        <a href="{{ route('admin.new-courses.topics.index', $course) }}" class="btn btn-secondary btn-sm">
                                            <i class="fas fa-arrow-right"></i> قائمة المواضيع
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> تحديث الموضوع
                        </button>
                        <a href="{{ route('admin.new-courses.topics.index', $course) }}" class="btn btn-secondary">
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
        height: 200,
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
