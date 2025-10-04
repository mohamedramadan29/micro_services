@extends('admin.layouts.master')
@section('title')
    ادارة صغحات الكورسات العامة
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
                    ادارة صغحات الكورسات العامة </span>
            </div>
        </div>
    </div>
    <!-- breadcrumb -->
    <!-- row -->
    <div class="row row-sm">

        <!-- Col -->
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    @if (Session::has('Success_message'))
                        <div class="alert alert-success"> {{ Session::get('Success_message') }} </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <div class="mb-4 main-content-label"> ادارة صغحات الكورسات العامة </div>
                    <div class="card-header">
                        <a href="{{ route('public-courses.add') }}" class="btn btn-primary btn-sm"> اضافة كورس
                            جديد <i class="fa fa-plus"></i></a>
                    </div>
                    <div class="card-body">

                        <div class="table-responsive">
                            <table class="table text-md-nowrap" id="example2">
                                <thead>
                                    <tr>
                                        <th class="wd-15p border-bottom-0"> # </th>
                                        <th class="wd-15p border-bottom-0"> اسم الكورس </th>
                                        <th class="wd-15p border-bottom-0"> رابط الكورس </th>
                                        <th class="wd-15p border-bottom-0"> صورة الكورس </th>
                                        <th class="wd-15p border-bottom-0"> التفعيل </th>
                                        <th class="wd-15p border-bottom-0"> عدد المشتركين </th>
                                        <th class="wd-15p border-bottom-0"> العمليات </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($courses as $course)
                                        <tr>
                                            <td> {{ $loop->iteration }} </td>
                                            <td> {{ $course['name'] }} </td>
                                            <td> <a target="_blank" href="{{ url('course/public/' . $course['url']) }}">
                                                    {{ $course['url'] }} </a> </td>
                                            <td> <img style="width: 40px"
                                                    src="{{ asset('assets/uploads/public-courses/' . $course['image']) }}"
                                                    alt=""> </td>
                                            <td>
                                                @if ($course['status'] == 1)
                                                    <span class="badge badge-success"> تم النشر </span>
                                                @else
                                                    <span class="badge badge-danger"> ارشيف </span>
                                                    <form action="{{ url('admin/course/update_status/' . $course['id']) }}"
                                                        method="POST">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-primary"> تفعيل
                                                        </button>
                                                    </form>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $course->registers->count() }} مشترك
                                            </td>

                                            <td>
                                                <a href="{{ url('admin/public-courses/registers/' . $course['id']) }}"
                                                    class="btn btn-warning btn-sm"> مشاهدة التسجيلات <i
                                                        class="fa fa-edit"></i>
                                                </a>

                                                <a href="{{ url('admin/public-courses/update/' . $course['id']) }}"
                                                    class="btn btn-primary btn-sm"> تعديل <i class="fa fa-edit"></i>
                                                </a>

                                                <a onclick="return confirm('هل تريد حذف هذا الكورس؟')"
                                                    href="{{ url('admin/public-courses/delete/' . $course['id']) }}"
                                                    class="btn btn-danger btn-sm"> حذف <i class="fa fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        {{-- @include('admin.courses.delete') --}}
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div><!-- bd -->
                </div>

            </div>
        </div>
        <!-- /Col -->
    </div>
    <!-- row closed -->
    </div>
    <!-- Container closed -->
    </div>
    <!-- main-content closed -->
@endsection

@section('js')
    <script src="{{ URL::asset('assets/admin/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ URL::asset('assets/admin/plugins/datatable/js/dataTables.dataTables.min.js') }}"></script>
    <script src="{{ URL::asset('assets/admin/plugins/datatable/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ URL::asset('assets/admin/plugins/datatable/js/responsive.dataTables.min.js') }}"></script>
    <script src="{{ URL::asset('assets/admin/plugins/datatable/js/jquery.dataTables.js') }}"></script>
    <script src="{{ URL::asset('assets/admin/plugins/datatable/js/dataTables.bootstrap4.js') }}"></script>
    <script src="{{ URL::asset('assets/admin/plugins/datatable/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ URL::asset('assets/admin/plugins/datatable/js/buttons.bootstrap4.min.js') }}"></script>
    <script src="{{ URL::asset('assets/admin/plugins/datatable/js/jszip.min.js') }}"></script>
    <script src="{{ URL::asset('assets/admin/plugins/datatable/js/pdfmake.min.js') }}"></script>
    <script src="{{ URL::asset('assets/admin/plugins/datatable/js/vfs_fonts.js') }}"></script>
    <script src="{{ URL::asset('assets/admin/plugins/datatable/js/buttons.html5.min.js') }}"></script>
    <script src="{{ URL::asset('assets/admin/plugins/datatable/js/buttons.print.min.js') }}"></script>
    <script src="{{ URL::asset('assets/admin/plugins/datatable/js/buttons.colVis.min.js') }}"></script>
    <script src="{{ URL::asset('assets/admin/plugins/datatable/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ URL::asset('assets/admin/plugins/datatable/js/responsive.bootstrap4.min.js') }}"></script>
    <!--Internal  Datatable js -->
    <script src="{{ URL::asset('assets/admin/js/table-data.js') }}"></script>
@endsection
