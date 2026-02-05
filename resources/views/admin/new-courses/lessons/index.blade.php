@extends('admin.layouts.master')
@section('title')
دروس الموضوع: {{ $topic->title }}
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
                {{ $topic->title }} </span><span class="text-muted mt-1 tx-13 mr-2 mb-0">/
                الدروس </span>
        </div>
    </div>
    <div class="my-auto">
        <a href="{{ route('admin.new-courses.lessons.create', [$course, $topic]) }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> إضافة درس جديد
        </a>
        <a href="{{ route('admin.new-courses.topics.show', [$course, $topic]) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-right"></i> العودة للموضوع
        </a>
    </div>
</div>
<!-- breadcrumb -->
<!-- row -->
<div class="row row-sm">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">دروس الموضوع: {{ $topic->title }}</h5>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-bordered text-nowrap" id="lessons-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>عنوان الدرس</th>
                                <th>الوصف</th>
                                <th>المدة</th>
                                <th>النوع</th>
                                <th>الترتيب</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($lessons as $index => $lesson)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <strong>{{ $lesson->title }}</strong>
                                    @if($lesson->video_id)
                                        <br><small class="text-muted">Video ID: {{ $lesson->video_id }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="text-muted">{{ Str::limit($lesson->description, 100) }}</span>
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
                                        <a href="{{ route('admin.new-courses.lessons.show', [$course, $topic, $lesson]) }}" class="btn btn-sm btn-primary" title="عرض">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.new-courses.lessons.edit', [$course, $topic, $lesson]) }}" class="btn btn-sm btn-warning" title="تعديل">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button class="btn btn-sm {{ $lesson->is_free ? 'btn-warning' : 'btn-success' }} toggle-free"
                                                data-id="{{ $lesson->id }}"
                                                data-course="{{ $course->id }}"
                                                data-topic="{{ $topic->id }}"
                                                title="{{ $lesson->is_free ? 'جعل مدفوع' : 'جعل مجاني' }}">
                                            <i class="fas {{ $lesson->is_free ? 'fa-lock' : 'fa-unlock' }}"></i>
                                        </button>
                                        <form action="{{ route('admin.new-courses.lessons.destroy', [$course, $topic, $lesson]) }}" method="POST" style="display: inline-block;">
                                            @csrf
                                            {{-- @method('DELETE') --}}
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

    // Toggle free/paid status
    $('.toggle-free').click(function() {
        var btn = $(this);
        var lessonId = btn.data('id');
        var courseId = btn.data('course');
        var topicId = btn.data('topic');

        $.post("{{ route('admin.new-courses.lessons.toggle-free', [':course', ':topic', ':lesson']) }}"
            .replace(':course', courseId)
            .replace(':topic', topicId)
            .replace(':lesson', lessonId), {
            _token: '{{ csrf_token() }}'
        })
        .done(function(response) {
            if(response.success) {
                location.reload();
            }
        })
        .fail(function() {
            alert('حدث خطأ ما');
        });
    });
});
</script>
@endsection
