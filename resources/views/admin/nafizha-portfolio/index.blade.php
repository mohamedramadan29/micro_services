@extends('admin.layouts.master')
@section('title')
اعمال المنصة
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
            <h4 class="content-title mb-0 my-auto">الرئيسية </h4><span class="text-muted mt-1 tx-13 mr-2 mb-0">/ اعمال
                المنصة </span>
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
                @if(Session::has('Success_message'))
                <div class="alert alert-success"> {{Session::get('Success_message')}} </div>
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
                <div class="mb-4 main-content-label"> اعمال المنصة </div>
                <div class="card-header">
                    <a href="{{ url('admin/nafizha-portfolio/add') }}" class="btn btn-primary btn-sm"> اضافة عمل جديد <i class="fa fa-plus"></i>
                    </a>
                </div>
                <div class="card-body">

                    <div class="table-responsive">
                        <table class="table text-md-nowrap" id="example2">
                            <thead>
                                <tr>
                                    <th class="wd-15p border-bottom-0"> #</th>
                                    <th class="wd-15p border-bottom-0"> العنوان </th>
                                    <th class="wd-15p border-bottom-0"> القسم </th>
                                    <th class="wd-15p border-bottom-0"> الصورة </th>
                                    <th class="wd-15p border-bottom-0"> المستخدم </th>
                                    <th class="wd-15p border-bottom-0"> رابط العمل </th>
                                    <th class="wd-15p border-bottom-0"> العمليات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($portfolios as $portfolio)
                                <tr>
                                    <td> {{ $loop->iteration }} </td>
                                    <td> {{$portfolio['title']}} </td>
                                    <td> {{ $portfolio['category']['name'] }} </td>
                                    <td><img width="60px" height="60px"
                                            src="{{ asset('assets/uploads/portfolios/' . $portfolio['image']) }}"
                                            alt="">
                                    </td>
                                    <td> {{ $portfolio['user']['name'] ?? 'admin' }} </td>
                                    <td> <a class="btn btn-warning btn-sm" target="_blank" href="{{ $portfolio['link'] }}"> رابط العمل </a> </td>


                                    <td>
                                        <a class="btn btn-primary btn-sm" href="{{ url('admin/nafizha-portfolio/edit/'.$portfolio['id']) }}" > تعديل <i
                                                class="fa fa-edit"></i></a>
                                        <a onclick="return confirm('هل أنت متأكد أنك تريد حذف هذا العنصر؟')" href="{{ url('admin/nafizha-portfolio/delete/'.$portfolio['id']) }}"
                                            class="btn btn-danger btn-sm"> حذف <i class="fa fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>

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
