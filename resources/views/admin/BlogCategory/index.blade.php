@extends('admin.layouts.master')
@section('title')
    اقسام المدونة
@endsection
@section('blog-active', 'active')
@section('blog-collapse', 'show')
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
                <h4 class="content-title mb-0 my-auto">الرئيسية </h4><span class="text-muted mt-1 tx-13 mr-2 mb-0">/ اقسام
                    المدونة </span>
            </div>
        </div>
    </div>
    <!-- breadcrumb -->
    <!-- ==================================================== -->
    <div class="row row-sm">
        @if (Session::has('Success_message'))
            @php
                toastify()->success(\Illuminate\Support\Facades\Session::get('Success_message'));
            @endphp
        @endif
        @if ($errors->any())
            @foreach ($errors->all() as $error)
                @php
                    toastify()->error($error);
                @endphp
            @endforeach
        @endif
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="gap-1 card-header d-flex justify-content-between align-items-center">



                        <a href="{{ url('admin/blog_category/add') }}" class="btn btn-sm btn-primary">
                            اضافة قسم جديد
                            <i class="ti ti-plus"></i>
                        </a>
                    </div>


                    <div>
                        <div class="table-responsive">
                            <table class="table text-md-nowrap" id="example2">
                                <thead class="bg-light-subtle table-primary-custome">
                                    <tr>
                                        <th> # </th>
                                        <th> اسم القسم </th>
                                        <th> الصورة </th>
                                        <th> تاريخ النشر </th>
                                        <th> الحالة </th>
                                        <th> العمليات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php

                                        $i = 1;
                                    @endphp
                                    @foreach ($categories as $category)
                                        <tr>
                                            <td>
                                                {{ $loop->iteration }}
                                            </td>
                                            <td> {{ $category['name'] }} </td>
                                            <td> <img width="60px" height="60px" src="{{ $category->Image() }}">
                                            </td>
                                            <td>{{ $category['created_at']->format('Y-m-d') }}</td>
                                            <td>
                                                @if ($category['status'] == 1)
                                                    <span class="badge bg-success">نشط</span>
                                                @else
                                                    <span class="badge bg-danger">غير نشط</span>
                                                @endif
                                            </td>
                                            <td>


                                                <div class="gap-2 d-flex">
                                                    <a href="{{ url('admin/blog_category/update/' . $category['id']) }}"
                                                        class="color-success btn btn-success btn-sm">
                                                        <i class="ti ti-eye"></i>
                                                    </a> 
                                                    <button data-target="#delete_category_{{$category['id']}}"
                                                    data-toggle="modal" class="btn btn-danger btn-sm"> حذف <i
                                                        class="fa fa-trash"></i>
                                            </button>
                                                </div>
                                            </td>

                                        </tr>
                                        <!-- Modal -->
                                        @include('admin.BlogCategory.delete')
                                    @endforeach

                                </tbody>
                            </table>
                        </div>
                        <!-- end table-responsive -->
                    </div>
                </div>

            </div>
        </div>


        <!-- End Container Fluid -->

    </div>
    <!-- ==================================================== -->
    <!-- End Page Content -->
    <!-- ==================================================== -->

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
