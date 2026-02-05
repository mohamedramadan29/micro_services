@extends('admin.layouts.master')
@section('title')
    مواضيع الكورس: {{ $course->title }}
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
                    المواضيع </span>
            </div>
        </div>
        <div class="my-auto">
            <a href="{{ route('admin.new-courses.show', $course) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-right"></i> العودة للكورس
            </a>
            <a href="{{ route('admin.new-courses.topics.create', $course) }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> إضافة موضوع جديد
            </a>
        </div>
    </div>
    <!-- breadcrumb -->
    <!-- row -->
    <div class="row row-sm">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">مواضيع الكورس: {{ $course->title }}</h5>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered text-nowrap" id="topics-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>عنوان الموضوع</th>
                                    <th>الوصف</th>
                                    <th>عدد الدروس</th>
                                    <th>المدة الإجمالية</th>
                                    <th>الترتيب</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($topics as $index => $topic)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <strong>{{ $topic->title }}</strong>
                                        </td>
                                        <td>
                                            <span class="text-muted">{{ Str::limit($topic->description, 100) }}</span>
                                        </td>
                                        <td>
                                            <span class="badge badge-info">{{ $topic->lessons_count }}</span>
                                        </td>
                                        <td>
                                            <span class="badge badge-primary">{{ $topic->total_duration }} دقيقة</span>
                                        </td>
                                        <td>{{ $topic->sort_order }}</td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="{{ route('admin.new-courses.lessons.index', [$course, $topic]) }}"
                                                    class="btn btn-sm btn-primary" title="الدروس">
                                                    <i class="fas fa-list"></i>
                                                </a>
                                                <a href="{{ route('admin.new-courses.topics.show', [$course, $topic]) }}"
                                                    class="btn btn-sm btn-info" title="عرض">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.new-courses.topics.edit', [$course, $topic]) }}"
                                                    class="btn btn-sm btn-warning" title="تعديل">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form
                                                    action="{{ route('admin.new-courses.topics.destroy', [$course, $topic]) }}"
                                                    method="POST" style="display: inline-block;">
                                                    @csrf
                                                    {{-- @method('DELETE') --}}
                                                    <button type="submit" class="btn btn-sm btn-danger"
                                                        onclick="return confirm('هل أنت متأكد من حذف هذا الموضوع؟')"
                                                        title="حذف">
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
            $('#topics-table').DataTable({
                responsive: true,
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.10.19/i18n/Arabic.json"
                }
            });
        });
    </script>
@endsection
