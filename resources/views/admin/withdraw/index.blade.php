@extends('admin.layouts.master')
@section('title')
    طلبات السحب
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
                    السحب </span>
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
                    <div class="mb-4 main-content-label"> طلبات السحب</div>
                    <div class="card-body">

                        <div class="table-responsive">
                            <table class="table text-md-nowrap" id="example2">
                                <thead>
                                    <tr>
                                        <th class="wd-15p border-bottom-0"> #</th>
                                        <th class="wd-15p border-bottom-0"> المستخدم </th>
                                        <th class="wd-15p border-bottom-0"> المبلغ </th>
                                        <th class="wd-20p border-bottom-0"> طريقة السحب </th>
                                        <th class="wd-20p border-bottom-0"> اسم الحساب </th>
                                        <th class="wd-20p border-bottom-0"> رقم الحساب </th>
                                        <th class="wd-20p border-bottom-0"> اسم البنك </th>
                                        <th class="wd-20p border-bottom-0"> الدولة </th>
                                        <th class="wd-20p border-bottom-0"> swift code </th>
                                        <th class="wd-20p border-bottom-0"> IBAN </th>
                                        <th class="wd-15p border-bottom-0"> الحالة</th>
                                        <th class="wd-15p border-bottom-0"> العمليات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $i = 1;
                                    @endphp
                                    @foreach ($withdraws as $withdraw)
                                        <tr>
                                            <td> {{ $i++ }} </td>
                                            <td> {{ $withdraw->user->name }} </td>
                                            <td> {{ $withdraw['amount'] }} </td>
                                            <td> {{ $withdraw['method'] }} </td>
                                            <td> {{ $withdraw['account_name'] }} </td>
                                            <td> {{ $withdraw['account_number'] }} </td>
                                            <td> {{ $withdraw['bank_name'] }} </td>
                                            <td> {{ $withdraw['country'] }} </td>
                                            <td> {{ $withdraw['swift_code'] }} </td>
                                            <td> {{ $withdraw['iban_code'] }} </td>
                                            <td>
                                                @if ($withdraw['status'] == 1)
                                                    <span class="badge badge-success"> مقبول </span>
                                                @elseif($withdraw['status'] == 0)
                                                    <span class="badge badge-danger"> لم تتم الموافقة </span>
                                                @elseif($withdraw['status'] == 2)
                                                    <span class="badge badge-danger"> رفض </span>
                                                @endif
                                            </td>
                                            <td>
                                               <a href="{{url('admin/withdraw/update/'.$withdraw['id'])}}" class="btn btn-primary btn-sm"><i class="fa fa-edit"></i> تفاصيل الطلب  </a>
                                                <button data-target="#delete_withdraw_{{ $withdraw['id'] }}" data-toggle="modal"
                                                    class="btn btn-danger btn-sm"><i class="fa fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <!-- Delete Section Model  -->
                                        @include('admin.withdraw.delete')
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
