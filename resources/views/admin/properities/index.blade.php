@extends('admin.layouts.master')
@section('title')
    العقارات
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
                    العقارات </span>
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
                    <div class="mb-4 main-content-label"> العقارات </div>
                    <div class="card-body">

                        <div class="table-responsive">
                            <table class="table text-md-nowrap" id="example2">
                                <thead>
                                    <tr>
                                        <th class="wd-15p border-bottom-0"> #</th>
                                        <th class="wd-15p border-bottom-0"> المستخدم  </th>
                                        <th class="wd-15p border-bottom-0"> العنوان  </th>
                                        <th class="wd-15p border-bottom-0"> النوع  </th>
                                        <th class="wd-15p border-bottom-0"> القسم   </th>
                                        <th class="wd-15p border-bottom-0"> السعر    </th>
                                        <th class="wd-15p border-bottom-0"> التفعيل </th>
                                        <th class="wd-15p border-bottom-0"> الحالة </th>
                                        <th class="wd-15p border-bottom-0"> العمليات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($properities as $properity)
                                        <tr>
                                            <td> {{ $loop->iteration }} </td>
                                            <td> {{ $properity->User->name }} </td>
                                            <td> {{ $properity->title }} </td>
                                            <td> {{ $properity->type }} </td>
                                            <td> {{ $properity->category }} </td>
                                            <td> {{ number_format($properity->price,2) }} دولار </td>
                                            <td>
                                                @if ($properity['active'] == 1)
                                                    <span class="badge badge-success"> مفعل  </span>
                                                @else
                                                    <span class="badge badge-danger"> غير مفعل </span>
                                                    <form action="{{ url('admin/properit/active/' . $properity['id']) }}"
                                                        method="POST">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-primary"> تفعيل الاعلان
                                                        </button>
                                                    </form>
                                                @endif
                                            </td>
                                            <td> {{ $properity['status'] }} </td>
                                            <td>
                                                <a href="{{ url('admin/properity/update/' . $properity['id']) }}"
                                                    class="btn btn-primary btn-sm"> تعديل <i class="fa fa-edit"></i> </a>
                                                <button data-target="#delete_properity_{{ $properity['id'] }}"
                                                    data-toggle="modal" class="btn btn-danger btn-sm"> حذف <i
                                                        class="fa fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        @include('admin.properities._delete')
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
