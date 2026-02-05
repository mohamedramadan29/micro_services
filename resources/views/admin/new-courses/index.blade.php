@extends('admin.layouts.master')
@section('title')
الكورسات الجديدة
@endsection
@section('css')
<link href="{{ URL::asset('assets/admin/plugins/datatable/css/dataTables.bootstrap4.min.css') }}" rel="stylesheet" />
<link href="{{ URL::asset('assets/admin/plugins/datatable/css/buttons.bootstrap4.min.css') }}" rel="stylesheet">
<link href="{{ URL::asset('assets/admin/plugins/datatable/css/responsive.bootstrap4.min.css') }}" rel="stylesheet" />
<link href="{{ URL::asset('assets/admin/plugins/datatable/css/jquery.dataTables.min.css') }}" rel="stylesheet">
<link href="{{ URL::asset('assets/admin/plugins/datatable/css/responsive.dataTables.min.css') }}" rel="stylesheet">
<link href="{{ URL::asset('assets/admin/plugins/select2/css/select2.min.css') }}" rel="stylesheet">
@endsection
@section('content')
<!-- breadcrumb -->
<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto">الرئيسية </h4><span class="text-muted mt-1 tx-13 mr-2 mb-0">/
                الكورسات الجديدة </span>
        </div>
    </div>
    <div class="my-auto">
        <a href="{{ route('admin.new-courses.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> إضافة كورس جديد
        </a>
    </div>
</div>
<!-- breadcrumb -->
<!-- row -->
<div class="row row-sm">

    <!-- Col -->
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">الكورسات الجديدة</h5>
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
                    <table class="table table-bordered text-nowrap" id="new-courses-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>صورة الكورس</th>
                                <th>عنوان الكورس</th>
                                <th>السعر</th>
                                <th>عدد المواضيع</th>
                                <th>عدد الدروس</th>
                                <th>الحالة</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($courses as $index => $course)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    @if($course->image)
                                        <img src="{{ asset($course->image) }}" alt="{{ $course->title }}" style="width: 60px; height: 40px; object-fit: cover;">
                                    @else
                                        <img src="{{ asset('assets/admin/img/default-course.jpg') }}" alt="default" style="width: 60px; height: 40px; object-fit: cover;">
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $course->title }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $course->slug }}</small>
                                </td>
                                <td>
                                    @if($course->is_free)
                                        <span class="badge badge-success">مجاني</span>
                                    @else
                                        <span class="badge badge-info">{{ number_format($course->price, 2) }} ريال</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-primary">{{ $course->topics_count }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-info">{{ $course->lessons_count }}</span>
                                </td>
                                <td>
                                    @if($course->is_active)
                                        <button class="btn btn-sm btn-success toggle-status" data-id="{{ $course->id }}" data-status="1">
                                            <i class="fas fa-check"></i> نشط
                                        </button>
                                    @else
                                        <button class="btn btn-sm btn-warning toggle-status" data-id="{{ $course->id }}" data-status="0">
                                            <i class="fas fa-times"></i> غير نشط
                                        </button>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('admin.new-courses.show', $course) }}" class="btn btn-sm btn-info" title="عرض">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.new-courses.topics.index', $course) }}" class="btn btn-sm btn-primary" title="المواضيع">
                                            <i class="fas fa-list"></i>
                                        </a>
                                        <a href="{{ route('admin.new-courses.edit', $course) }}" class="btn btn-sm btn-warning" title="تعديل">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.new-courses.destroy', $course) }}" method="POST" style="display: inline-block;">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('هل أنت متأكد من حذف هذا الكورس؟')" title="حذف">
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
<script src="{{ URL::asset('assets/admin/plugins/datatable/js/dataTables.buttons.min.js') }}"></script>
<script src="{{ URL::asset('assets/admin/plugins/datatable/js/buttons.bootstrap4.min.js') }}"></script>
<script src="{{ URL::asset('assets/admin/plugins/datatable/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ URL::asset('assets/admin/plugins/datatable/js/responsive.bootstrap4.min.js') }}"></script>

<script>
$(document).ready(function() {
    $('#new-courses-table').DataTable({
        responsive: true,
        language: {
            url: "//cdn.datatables.net/plug-ins/1.10.19/i18n/Arabic.json"
        }
    });

    // Toggle status
    $('.toggle-status').click(function() {
        var btn = $(this);
        var courseId = btn.data('id');

        $.post("{{ route('admin.new-courses.toggle-status', ':id') }}".replace(':id', courseId), {
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
