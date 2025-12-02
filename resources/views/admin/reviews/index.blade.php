@extends('admin.layouts.master')
@section('title')
    التقيمات
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
                    تقيمات العملاء </span>
            </div>
        </div>
    </div>
    <!-- breadcrumb -->
    <!-- ==================================================== -->
    <div class="row row-sm">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
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
                        <div>
                            <div class="table-responsive">
                                <table id="table-search"
                                    class="table mb-0 align-middle table-bordered gridjs-table table-hover table-centered">
                                    <thead class="bg-light-subtle table-primary-custome">
                                        <tr>
                                            <th> # </th>
                                            <th> اسم العميل </th>
                                            <th> التقيم </th>
                                            <th> التوقيت </th>
                                            <th> ملاحظات </th>
                                            <th> اجراءت متقدمة </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($reviews as $review)
                                            <tr>
                                                <td>
                                                    {{ $loop->iteration }}
                                                </td>
                                                <td> {{ $review['name'] }} </td>
                                                <td> {{ $review['reviews_count'] }} </td>
                                                <td>
                                                    {{ $review->created_at->format('Y m d') }}
                                                </td>
                                                <td> {{ $review['more_notes'] }} </td>

                                                <td>


                                                    <div class="gap-2 d-flex">
                                                        {{-- <a href="{{ url('admin/blog/update/' . $blog['id']) }}"
                                                            class="color-success btn btn-success btn-sm">
                                                            <i class="ti ti-eye"></i>
                                                        </a>
  --}}
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach

                                    </tbody>
                                </table>
                            </div>
                            <!-- end table-responsive -->
                        </div>

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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    {{--    <!-- DataTables JS --> --}}
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

    <script>
        $(document).ready(function() {
            // تحقق ما إذا كان الجدول قد تم تهيئته من قبل
            if ($.fn.DataTable.isDataTable('#table-search')) {
                $('#table-search').DataTable().destroy(); // تدمير التهيئة السابقة
            }

            // تهيئة DataTables من جديد
            $('#table-search').DataTable({
                "ordering": false,
                "language": {
                    "search": "بحث:",
                    "lengthMenu": "عرض _MENU_ عناصر لكل صفحة",
                    "zeroRecords": "لم يتم العثور على سجلات",
                    "info": "عرض _PAGE_ من _PAGES_",
                    "infoEmpty": "لا توجد سجلات متاحة",
                    "infoFiltered": "(تمت التصفية من إجمالي _MAX_ سجلات)",
                    "paginate": {
                        "previous": "السابق",
                        "next": "التالي"
                    }
                }
            });
        });
    </script>
@endsection
