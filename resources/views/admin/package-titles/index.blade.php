@extends('admin.layouts.master')
@section('title')
تصنيف الباقات
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
            <h4 class="content-title mb-0 my-auto">الرئيسية </h4><span class="text-muted mt-1 tx-13 mr-2 mb-0">/ ادارة
                تصنيف الباقات </span>
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
                <div class="mb-4 main-content-label"> ادارة تصنيف الباقات </div>
                <div class="card-header">
                    <button data-target="#add_title" data-toggle="modal" class="btn btn-primary btn-sm"> اضافة تصنيف <i
                            class="fa fa-plus"></i>
                    </button>
                    @include('admin.package-titles._add')
                </div>
                <!-- Add New Section -->

                <div class="card-body">

                    <div class="table-responsive">
                        <table class="table text-md-nowrap" id="example2">
                            <thead>
                                <tr>
                                    <th class="wd-15p border-bottom-0"> #</th>
                                    <th class="wd-15p border-bottom-0"> التصنيف </th>

                                    <th class="wd-15p border-bottom-0"> العمليات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($titles as $title)
                                <tr>
                                    <td> {{ $loop->iteration }} </td>
                                    <td> {{ $title['title'] }} </td>
                                    <td>

                                        <button data-target="#edit_title_{{ $title['id'] }}" data-toggle="modal"
                                            class="btn btn-primary btn-sm"> <i class="fa fa-edit"></i> تعديل
                                        </button>

                                        <form action="{{ url('admin/package-title/delete/' . $title->id) }}" method="post">
                                            @csrf
                                            <button onclick="return confirm(' هل انت متاكد من الحذف ! ')" type="submit" class="btn btn-danger btn-sm"> حذف </button>
                                        </form>
                                    </td>
                                </tr>

                                @include('admin.package-titles._edit')

                                <!-- Delete Section Model  -->
                                {{-- @include('admin.categories.delete') --}}
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
