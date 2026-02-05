@extends('admin.layouts.master')
@section('title')
إضافة موضوع جديد: {{ $course->title }}
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
                إضافة موضوع جديد </span>
        </div>
    </div>
</div>
<!-- breadcrumb -->
<!-- row -->
<div class="row row-sm">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">إضافة موضوع جديد للكورس: {{ $course->title }}</h5>
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

                <form action="{{ route('admin.new-courses.topics.store', $course) }}" method="POST">
                    @csrf

                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="title">عنوان الموضوع <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="title" name="title" value="{{ old('title') }}" required>
                            </div>

                            <div class="form-group">
                                <label for="description">وصف الموضوع</label>
                                <textarea class="form-control" id="description" name="description" rows="6">{{ old('description') }}</textarea>
                                <small class="text-muted">وصف تفصيلي للموضوع ومحتوياته</small>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="sort_order">ترتيب العرض</label>
                                <input type="number" step="0.01" class="form-control" id="sort_order" name="sort_order" value="{{ old('sort_order', 0) }}" min="0">
                                <small class="text-muted">يستخدم لترتيب المواضيع في الكورس</small>
                            </div>

                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title">معلومات الكورس</h6>
                                    <p><strong>الكورس:</strong> {{ $course->title }}</p>
                                    <p><strong>عدد المواضيع الحالية:</strong> {{ $course->topics->count() }}</p>
                                    <p><strong>عدد الدروس الإجمالي:</strong> {{ $course->total_lessons_count }}</p>
                                </div>
                            </div>

                            <div class="card mt-3">
                                <div class="card-body">
                                    <h6 class="card-title">روابط سريعة</h6>
                                    <div class="d-grid gap-2">
                                        <a href="{{ route('admin.new-courses.topics.index', $course) }}" class="btn btn-info btn-sm">
                                            <i class="fas fa-list"></i> عرض المواضيع
                                        </a>
                                        <a href="{{ route('admin.new-courses.show', $course) }}" class="btn btn-secondary btn-sm">
                                            <i class="fas fa-arrow-right"></i> العودة للكورس
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> حفظ الموضوع
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
