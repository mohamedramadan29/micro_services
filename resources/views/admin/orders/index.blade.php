@extends('admin.layouts.master')
@section('title')
    الطلبات
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
    <!-- ==================================================== -->
    <!-- breadcrumb -->
    <div class="breadcrumb-header justify-content-between">
        <div class="my-auto">
            <div class="d-flex">
                <h4 class="content-title mb-0 my-auto">الرئيسية </h4><span class="text-muted mt-1 tx-13 mr-2 mb-0">/ الطلبات
                </span>
            </div>
        </div>
    </div>
    <!-- breadcrumb -->
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
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center gap-1">
                    <h4 class="card-title flex-grow-1"> الطلبات </h4>
                </div>


                <div>
                    <div class="table-responsive">
                        <table class="table text-md-nowrap" id="example2">
                            <thead class="bg-light-subtle">
                                <tr>
                                    <th style="width: 20px;">
                                    </th>
                                    <th> الاسم </th>
                                    <th> البريد الالكتروني </th>
                                    <th> رقم الهاتف </th>
                                    <th>اسم المنتج </th>
                                    <th> السعر </th>
                                    <th> الدولة </th>
                                    <th> المدينة </th>
                                    <th> العنوان </th>
                                    <th> تاريخ الطلب </th>
                                    <th> حالة الطلب </th>
                                    <th> العمليات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($orders as $order)
                                    <tr>
                                        <td>
                                            {{ $loop->iteration }}
                                        </td>
                                        <td>{{ $order['name'] }}</td>
                                        <td>{{ $order['email'] }}</td>
                                        <td>{{ $order['phone'] }}</td>
                                        <td>{{ $order['product_name'] }}</td>
                                        <td>{{ $order['price'] }}</td>
                                        <td>{{ $order['country'] }}</td>
                                        <td>{{ $order['city'] }}</td>
                                        <td>{{ $order['address'] }}</td>
                                        <td>{{ $order['created_at'] }}</td>
                                        <td> <span class="badge badge-info"> {{ $order['order_status'] }} </span></td>
                                        <td>
                                            <button data-target="#update_order_{{ $order['id'] }}" data-toggle="modal"
                                                class="btn btn-primary btn-sm"> <i class="fa fa-edit"></i>
                                            </button>
                                            <button data-target="#delete_order_{{ $order['id'] }}" data-toggle="modal"
                                                class="btn btn-danger btn-sm"> <i class="fa fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <!-- Modal -->
                                    @include('admin.orders.update')
                                    @include('admin.orders.delete')
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
