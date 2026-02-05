@extends('admin.layouts.master')
@section('title')
عرض الكورس: {{ $newCourse->title }}
@endsection
@section('css')
<link href="{{ URL::asset('assets/admin/plugins/datatable/css/dataTables.bootstrap4.min.css') }}" rel="stylesheet" />
@endsection
@section('content')
<!-- breadcrumb -->
<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto">الرئيسية </h4><span class="text-muted mt-1 tx-13 mr-2 mb-0">/
                الكورسات الجديدة </span><span class="text-muted mt-1 tx-13 mr-2 mb-0">/
                {{ $newCourse->title }} </span>
        </div>
    </div>
    <div class="my-auto">
        <a href="{{ route('admin.new-courses.topics.index', $newCourse) }}" class="btn btn-primary">
            <i class="fas fa-list"></i> إدارة المواضيع
        </a>
        <a href="{{ route('admin.new-courses.edit', $newCourse) }}" class="btn btn-warning">
            <i class="fas fa-edit"></i> تعديل الكورس
        </a>
    </div>
</div>
<!-- breadcrumb -->
<!-- row -->
<div class="row row-sm">
    <!-- Course Info -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">معلومات الكورس</h5>
            </div>
            <div class="card-body">
                @if($newCourse->image)
                    <img src="{{ asset($newCourse->image) }}" alt="{{ $newCourse->title }}" class="img-fluid mb-3">
                @endif

                <h4>{{ $newCourse->title }}</h4>
                <p class="text-muted">{{ $newCourse->slug }}</p>

                <div class="mb-3">
                    <strong>الوصف القصير:</strong>
                    <p>{{ $newCourse->short_description ?: 'غير متوفر' }}</p>
                </div>

                <div class="row mb-3">
                    <div class="col-6">
                        <strong>السعر:</strong>
                        @if($newCourse->is_free)
                            <span class="badge badge-success">مجاني</span>
                        @else
                            <span class="badge badge-info">{{ number_format($newCourse->price, 2) }} ريال</span>
                        @endif
                    </div>
                    <div class="col-6">
                        <strong>الحالة:</strong>
                        @if($newCourse->is_active)
                            <span class="badge badge-success">نشط</span>
                        @else
                            <span class="badge badge-warning">غير نشط</span>
                        @endif
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-6">
                        <strong>عدد المواضيع:</strong>
                        <span class="badge badge-primary">{{ $newCourse->topics->count() }}</span>
                    </div>
                    <div class="col-6">
                        <strong>عدد الدروس:</strong>
                        <span class="badge badge-info">{{ $newCourse->total_lessons_count }}</span>
                    </div>
                </div>

                <div class="mb-3">
                    <strong>المدة الإجمالية:</strong>
                    <p>{{ $newCourse->total_duration }} دقيقة</p>
                </div>

                <div class="mb-3">
                    <strong>ترتيب العرض:</strong>
                    <p>{{ $newCourse->sort_order }}</p>
                </div>

                @if($newCourse->meta_title || $newCourse->meta_description)
                <div class="mb-3">
                    <strong>معلومات SEO:</strong>
                    @if($newCourse->meta_title)
                        <p><strong>العنوان:</strong> {{ $newCourse->meta_title }}</p>
                    @endif
                    @if($newCourse->meta_description)
                        <p><strong>الوصف:</strong> {{ $newCourse->meta_description }}</p>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Course Content -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">محتوى الكورس</h5>
            </div>
            <div class="card-body">
                <h6>الوصف الكامل:</h6>
                <div class="mb-4">
                    {!! $newCourse->description !!}
                </div>

                <h6>المواضيع والدروس:</h6>
                @if($newCourse->topics->count() > 0)
                    @foreach($newCourse->topics as $topic)
                        <div class="card mb-3">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-book"></i> {{ $topic->title }}
                                    <small class="text-muted">({{ $topic->lessons_count }} درس - {{ $topic->total_duration }} دقيقة)</small>
                                </h6>
                            </div>
                            <div class="card-body">
                                @if($topic->description)
                                    <p class="text-muted">{{ $topic->description }}</p>
                                @endif

                                @if($topic->lessons->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>عنوان الدرس</th>
                                                    <th>المدة</th>
                                                    <th>النوع</th>
                                                    <th>الإجراءات</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($topic->lessons as $index => $lesson)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>{{ $lesson->title }}</td>
                                                    <td>{{ $lesson->formatted_duration }}</td>
                                                    <td>
                                                        @if($lesson->is_free)
                                                            <span class="badge badge-success">مجاني</span>
                                                        @else
                                                            <span class="badge badge-info">مدفوع</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <a href="{{ $lesson->video_url }}" target="_blank" class="btn btn-sm btn-info" title="مشاهدة الفيديو">
                                                            <i class="fas fa-play"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <p class="text-muted">لا توجد دروس في هذا الموضوع</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> لا توجد مواضيع في هذا الكورس بعد.
                        <a href="{{ route('admin.new-courses.topics.create', $newCourse) }}" class="btn btn-sm btn-primary">إضافة موضوع جديد</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
@section('js')
<script src="{{ URL::asset('assets/admin/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ URL::asset('assets/admin/plugins/datatable/js/dataTables.bootstrap4.min.js') }}"></script>

<script>
$(document).ready(function() {
    $('.table').DataTable({
        responsive: true,
        language: {
            url: "//cdn.datatables.net/plug-ins/1.10.19/i18n/Arabic.json"
        }
    });
});
</script>
@endsection
