@extends('admin.layouts.master')
@section('title')
عرض الموضوع: {{ $topic->title }}
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
                {{ $course->title }} </span><span class="text-muted mt-1 tx-13 mr-2 mb-0">/
                {{ $topic->title }} </span>
        </div>
    </div>
    <div class="my-auto">
        <a href="{{ route('admin.new-courses.lessons.create', [$course, $topic]) }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> إضافة درس جديد
        </a>
        <a href="{{ route('admin.new-courses.topics.edit', [$course, $topic]) }}" class="btn btn-warning">
            <i class="fas fa-edit"></i> تعديل الموضوع
        </a>
    </div>
</div>
<!-- breadcrumb -->
<!-- row -->
<div class="row row-sm">
    <!-- Topic Info -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">معلومات الموضوع</h5>
            </div>
            <div class="card-body">
                <h4>{{ $topic->title }}</h4>
                
                @if($topic->description)
                    <div class="mb-3">
                        <strong>الوصف:</strong>
                        <p>{!! $topic->description !!}</p>
                    </div>
                @endif

                <div class="row mb-3">
                    <div class="col-6">
                        <strong>عدد الدروس:</strong>
                        <p><span class="badge badge-info">{{ $topic->lessons_count }}</span></p>
                    </div>
                    <div class="col-6">
                        <strong>المدة الإجمالية:</strong>
                        <p><span class="badge badge-primary">{{ $topic->total_duration }} دقيقة</span></p>
                    </div>
                </div>

                <div class="mb-3">
                    <strong>ترتيب العرض:</strong>
                    <p>{{ $topic->sort_order }}</p>
                </div>

                <div class="mb-3">
                    <strong>الكورس:</strong>
                    <p>{{ $course->title }}</p>
                </div>

                <div class="d-grid gap-2">
                    <a href="{{ route('admin.new-courses.lessons.index', [$course, $topic]) }}" class="btn btn-primary">
                        <i class="fas fa-list"></i> إدارة الدروس
                    </a>
                    <a href="{{ route('admin.new-courses.topics.edit', [$course, $topic]) }}" class="btn btn-warning">
                        <i class="fas fa-edit"></i> تعديل الموضوع
                    </a>
                    <a href="{{ route('admin.new-courses.topics.index', $course) }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-right"></i> العودة للمواضيع
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Lessons -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">دروس الموضوع</h5>
            </div>
            <div class="card-body">
                @if($topic->lessons->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered text-nowrap" id="lessons-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>عنوان الدرس</th>
                                    <th>المدة</th>
                                    <th>النوع</th>
                                    <th>الترتيب</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topic->lessons as $index => $lesson)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <strong>{{ $lesson->title }}</strong>
                                        @if($lesson->description)
                                            <br><small class="text-muted">{{ Str::limit($lesson->description, 80) }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $lesson->formatted_duration }}</td>
                                    <td>
                                        @if($lesson->is_free)
                                            <span class="badge badge-success">مجاني</span>
                                        @else
                                            <span class="badge badge-info">مدفوع</span>
                                        @endif
                                    </td>
                                    <td>{{ $lesson->sort_order }}</td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ $lesson->video_url }}" target="_blank" class="btn btn-sm btn-info" title="مشاهدة الفيديو">
                                                <i class="fas fa-play"></i>
                                            </a>
                                            <a href="{{ route('admin.new-courses.lessons.edit', [$course, $topic, $lesson]) }}" class="btn btn-sm btn-warning" title="تعديل">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.new-courses.lessons.destroy', [$course, $topic, $lesson]) }}" method="POST" style="display: inline-block;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('هل أنت متأكد من حذف هذا الدرس؟')" title="حذف">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> لا توجد دروس في هذا الموضوع بعد.
                        <a href="{{ route('admin.new-courses.lessons.create', [$course, $topic]) }}" class="btn btn-sm btn-primary">إضافة درس جديد</a>
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
    $('#lessons-table').DataTable({
        responsive: true,
        language: {
            url: "//cdn.datatables.net/plug-ins/1.10.19/i18n/Arabic.json"
        }
    });
});
</script>
@endsection
