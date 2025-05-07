@extends('admin.layouts.master')
@section('title')
    تفاصيل طلب السحب
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="breadcrumb-header justify-content-between">
        <div class="my-auto">
            <div class="d-flex">
                <h4 class="content-title mb-0 my-auto">الرئيسية </h4><span class="text-muted mt-1 tx-13 mr-2 mb-0">/ تفاصيل طلبات السحب </span>
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
                    <div class="mb-4 main-content-label"> تفاصيل طلب السحب </div>
                    <div class="card-body">

                        <div class="table-responsive">
                            <table class="table table-bordered text-md-nowrap" id="example2">

                                <tbody>
                                    <tr>
                                        <th>
                                            المستخدم
                                        </th>
                                        <td>
                                            {{ $withdraw->user->name }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>
                                            المبلغ
                                        </th>
                                        <td>
                                            {{ $withdraw['amount'] }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>
                                            طريقة السحب
                                        </th>
                                        <td>
                                            {{ $withdraw['method'] }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>
                                            اسم الحساب
                                        </th>
                                        <td>
                                            {{ $withdraw['account_name'] }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>
                                            رقم الحساب
                                        </th>
                                        <td>
                                            {{ $withdraw['account_number'] }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>
                                            اسم البنك
                                        </th>
                                        <td>
                                            {{ $withdraw['bank_name'] }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>
                                            الدولة
                                        </th>
                                        <td>
                                            {{ $withdraw['country'] }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>
                                            swift code
                                        </th>
                                        <td>
                                            {{ $withdraw['swift_code'] }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>
                                            IBAN
                                        </th>
                                        <td>
                                            {{ $withdraw['iban_code'] }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>
                                            الحالة
                                        </th>
                                        <td>
                                            @if ($withdraw['status'] == 1)
                                                <span class="badge badge-success"> مقبول </span>
                                            @elseif($withdraw['status'] == 0)
                                                <span class="badge badge-danger"> لم تتم الموافقة </span>
                                            @elseif($withdraw['status'] == 2)
                                                <span class="badge badge-danger"> رفض </span>
                                            @endif
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <form action="{{ url('admin/withdraw/update/' . $withdraw['id']) }}" method="POST">
                                @csrf 
                                <div class="form-group">
                                    <label for="status"> تحديث حالة الطلب  </label>
                                    <select name="status" id="status" class="form-control">
                                        <option value="0" @if ($withdraw['status'] == 0) selected @endif>لم تتم الموافقة</option>
                                        <option value="1" @if ($withdraw['status'] == 1) selected @endif> تم تحويل المبلغ  </option>
                                        <option value="2" @if ($withdraw['status'] == 2) selected @endif>رفض</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary">حفظ</button>
                            </form>
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
