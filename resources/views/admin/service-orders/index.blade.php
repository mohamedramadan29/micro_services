@extends('admin.layouts.master')
@section('title')
طلبات شراء الخدمات
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
            <h4 class="content-title mb-0 my-auto">الرئيسية </h4><span class="text-muted mt-1 tx-13 mr-2 mb-0">/ طلبات
                شراء الخدمات </span>
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
                <div class="mb-4 main-content-label"> طلبات شراء الخدمات </div>
                <div class="card-body">

                    <div class="table-responsive">
                        <table class="table text-md-nowrap" id="example2">
                            <thead>
                                <tr>
                                    <th class="wd-15p border-bottom-0"> # </th>
                                    <th class="wd-15p border-bottom-0"> رقم الطلب </th>
                                    <th class="wd-15p border-bottom-0"> اسم الخدمة </th>
                                    <th class="wd-15p border-bottom-0"> صاحب الخدمة </th>
                                    <th class="wd-15p border-bottom-0"> صاحب الطلب </th>
                                    <th class="wd-15p border-bottom-0"> سعر الخدمة </th>
                                    <th class="wd-15p border-bottom-0"> عمولة الموقع </th>
                                    <th class="wd-15p border-bottom-0"> صافي الربح </th>
                                    <th class="wd-15p border-bottom-0"> الحالة </th>
                                    <th class="wd-15p border-bottom-0"> توقيت الطلب </th>
                                </tr>
                            </thead>
                            <tbody>

                                @foreach($orders as $order)
                                <tr>
                                    <td> {{$loop->iteration}} </td>
                                    <td> {{$order['order_number']}} </td>
                                    <td> {{$order['OrderDetail']['service_name']?? ' غير متاح '}} </td>
                                    <td> {{$order['Seller']['user_name'] ?? ' غير متاح '}} </td>
                                    <td> {{$order['Buyer']['user_name'] ?? ' غير متاح '}} </td>
                                    <td> {{ number_format($order['order_price'],2) }} $</td>
                                    <td> {{ number_format($order['website_commission'],2) }} $ </td>
                                    <td> {{ number_format($order['seller_commission'],2)}} $ </td>
                                    <td> {{$order['status']}} </td>
                                    <td> {{$order['created_at']->format('Y-m-d H:i')}} </td>
                                    <td>
                                        {{-- <a href="{{url('admin/service/update/'.$service['id'])}}"
                                            class="btn btn-primary btn-sm"> تعديل <i class="fa fa-edit"></i> </a>
                                        <button data-target="#delete_model_{{$service['id']}}" data-toggle="modal"
                                            class="btn btn-danger btn-sm"> حذف <i class="fa fa-trash"></i>
                                        </button> --}}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        {{ $orders->links() }}
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
