@extends('admin.layouts.master')
@section('title')
    المدونة
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
                    المقالات </span>
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

                    <div class="gap-1 card-header d-flex justify-content-between align-items-center">

                        <a href="{{ url('admin/blog/add') }}" class="btn btn-sm btn-primary">
                            اضافة مقال جديد
                            <i class="ti ti-plus"></i>
                        </a>
                    </div>
                    <div>
                        <div class="table-responsive">
                            <table id="table-search"
                                class="table mb-0 align-middle table-bordered gridjs-table table-hover table-centered">
                                <thead class="bg-light-subtle table-primary-custome">
                                    <tr>
                                        <th> # </th>
                                        <th> عنوان المقال </th>
                                        <th> الصورة </th>
                                        <th> القسم </th>
                                        <th> جدولة نشر المقال </th>
                                        <th> اسم الكاتب </th>
                                        <th> حالة المقال </th>
                                        <th> اجراءت متقدمة </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($blogs as $blog)
                                        <tr>
                                            <td>
                                                {{ $loop->iteration }}
                                            </td>
                                            <td> {{ $blog['name'] }} </td>
                                            <td><img width="60" height="60" class="img-thumbnail"
                                                    src="{{ $blog->Image() }}" alt="">
                                            </td>
                                            <td> {{ $blog['category']['name'] }} </td>
                                            <td> {{ $blog['publish_date'] }} </td>
                                            <td> {{ $blog['author']['name'] ?? 'admin' }} </td>

                                            <td>
                                                @if ($blog['status'] == 1)
                                                    <span class="badge bg-success">نشط</span>
                                                @else
                                                    <span class="badge bg-danger"> ارشيف </span>
                                                @endif
                                            </td>
                                            <td>


                                                <div class="gap-2 d-flex">
                                                    <a href="{{ url('admin/blog/update/' . $blog['id']) }}"
                                                        class="color-success btn btn-success btn-sm">
                                                        <i class="ti ti-eye"></i>
                                                    </a>

                                                    <button data-target="#delete_category_{{$blog['id']}}"
                                                    data-toggle="modal" class="btn btn-danger btn-sm"> حذف <i
                                                        class="fa fa-trash"></i>
                                            </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <!-- Modal -->
                                        @include('admin.Blogs.delete')
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
