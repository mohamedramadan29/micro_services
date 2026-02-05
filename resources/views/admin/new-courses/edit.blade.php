@extends('admin.layouts.master')
@section('title')
تعديل الكورس: {{ $newCourse->title }}
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
                تعديل الكورس </span>
        </div>
    </div>
</div>
<!-- breadcrumb -->
<!-- row -->
<div class="row row-sm">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">تعديل الكورس: {{ $newCourse->title }}</h5>
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

                <form action="{{ route('admin.new-courses.update', $newCourse) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    {{-- @method('PUT') --}}

                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="title">عنوان الكورس <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="title" name="title" value="{{ old('title', $newCourse->title) }}" required>
                            </div>

                            <div class="form-group">
                                <label for="short_description">وصف قصير</label>
                                <textarea class="form-control" id="short_description" name="short_description" rows="3">{{ old('short_description', $newCourse->short_description) }}</textarea>
                                <small class="text-muted">سيظهر في صفحة عرض الكورسات (أقصى 500 حرف)</small>
                            </div>

                            <div class="form-group">
                                <label for="description">وصف الكورس <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="description" name="description" rows="8">{{ old('description', $newCourse->description) }}</textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="price">السعر <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="price" name="price" value="{{ old('price', $newCourse->price) }}" step="0.01" min="0" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="sort_order">ترتيب العرض</label>
                                        <input type="number" class="form-control" id="sort_order" name="sort_order" value="{{ old('sort_order', $newCourse->sort_order) }}" min="0">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="is_free" name="is_free" value="1" {{ old('is_free', $newCourse->is_free) ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="is_free">كورس مجاني</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" {{ old('is_active', $newCourse->is_active) ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="is_active">كورس نشط</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="meta_title">عنوان SEO</label>
                                <input type="text" class="form-control" id="meta_title" name="meta_title" value="{{ old('meta_title', $newCourse->meta_title) }}">
                                <small class="text-muted">عنوان يظهر في محركات البحث</small>
                            </div>

                            <div class="form-group">
                                <label for="meta_description">وصف SEO</label>
                                <textarea class="form-control" id="meta_description" name="meta_description" rows="3">{{ old('meta_description', $newCourse->meta_description) }}</textarea>
                                <small class="text-muted">وصف يظهر في محركات البحث (أقصى 500 حرف)</small>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="image">صورة الكورس</label>
                                <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                <small class="text-muted">الحجم الموصى به: 800x600 بكسل</small>

                                @if($newCourse->image)
                                    <div class="mt-2">
                                        <p class="mb-1">الصورة الحالية:</p>
                                        <img src="{{ asset($newCourse->image) }}" alt="صورة الكورس" style="max-width: 100%; height: auto; max-height: 200px;">
                                    </div>
                                @endif
                            </div>

                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title">معلومات المسار</h6>
                                    <div class="form-group">
                                        <label for="slug">مسار URL</label>
                                        <input type="text" class="form-control" id="slug" name="slug" value="{{ old('slug', $newCourse->slug) }}">
                                        <small class="text-muted">سيتم تحديثه تلقائياً من العنوان إذا تركت فارغاً</small>
                                    </div>
                                </div>
                            </div>

                            <div class="card mt-3">
                                <div class="card-body">
                                    <h6 class="card-title">روابط سريعة</h6>
                                    <div class="d-grid gap-2">
                                        <a href="{{ route('admin.new-courses.show', $newCourse) }}" class="btn btn-info btn-sm">
                                            <i class="fas fa-eye"></i> عرض الكورس
                                        </a>
                                        <a href="{{ route('admin.new-courses.topics.index', $newCourse) }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-list"></i> إدارة المواضيع
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> تحديث الكورس
                        </button>
                        <a href="{{ route('admin.new-courses.index') }}" class="btn btn-secondary">
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

    // Generate slug from title
    $('#title').on('input', function() {
        var title = $(this).val();
        var slug = title.toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .trim('-');
        $('#slug').val(slug);
    });

    // Handle free course checkbox
    $('#is_free').change(function() {
        if($(this).is(':checked')) {
            $('#price').val(0).prop('readonly', true);
        } else {
            $('#price').prop('readonly', false);
        }
    });

    // Check initial state
    if($('#is_free').is(':checked')) {
        $('#price').prop('readonly', true);
    }
});
</script>
@endsection
