@extends('admin.layouts.master')
@section('title')
    الرئيسية
@endsection

@section('css')
    <!--  Owl-carousel css-->
    <link href="{{ URL::asset('assets/admin.php/plugins/owl-carousel/owl.carousel.css') }}" rel="stylesheet"/>
    <!-- Maps css -->
    <link href="{{ URL::asset('assets/admin.php/plugins/jqvmap/jqvmap.min.css') }}" rel="stylesheet">
@endsection
@section('page-header')
    <!-- breadcrumb -->
    <div class="breadcrumb-header justify-content-between">
        <div class="left-content">
            <div>
                <h2 class="main-content-title tx-24 mg-b-1 mg-b-lg-1">مرحبا ,  {{Auth::user()->name}} </h2>
                <p class="mg-b-0"> لوحة التحكم الخاصة بك </p>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->
@endsection
@section('content')
    <!-- row -->
    <div class="row row-sm">
        <!--======================================================== Admin Dashboard ==================================================-->
        @if(\Illuminate\Support\Facades\Auth::user()->account_type == 'admin')
            <div class="col-xl-3 col-lg-6 col-md-6 col-xm-12">
                <div class="card overflow-hidden sales-card bg-primary-gradient">
                    <div class="pl-3 pt-3 pr-3 pb-2 pt-0">
                        <div class="">
                            <h6 class="mb-3 tx-12 text-white"> الاقسام الرئيسية  </h6>
                        </div>
                        <div class="pb-0 mt-0">
                            <div class="d-flex">
                                <div class="">
                                    <h4 class="tx-20 font-weight-bold mb-1 text-white"> @php //echo count(\App\Models\Order::all()) @endphp  </h4>
                                    <a href="{{url('admin.php/orders')}}" class="mb-0 tx-12 text-white op-7"> مشاهدة التفاصيل  </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 col-xm-12">
                <div class="card overflow-hidden sales-card bg-danger-gradient">
                    <div class="pl-3 pt-3 pr-3 pb-2 pt-0">
                        <div class="">
                            <h6 class="mb-3 tx-12 text-white"> الخدمات  </h6>
                        </div>
                        <div class="pb-0 mt-0">
                            <div class="d-flex">
                                <div class="">
                                    <h4 class="tx-20 font-weight-bold mb-1 text-white"> @php //echo count(\App\Models\Order::where('status','في انتظار الموافقة')->get()) @endphp </h4>
                                    <a href="{{url('admin.php/orders/pending')}}" class="mb-0 tx-12 text-white op-7"> مشاهدة التفاصيل  </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 col-xm-12">
                <div class="card overflow-hidden sales-card bg-success-gradient">
                    <div class="pl-3 pt-3 pr-3 pb-2 pt-0">
                        <div class="">
                            <h6 class="mb-3 tx-12 text-white">  المستخدمين  </h6>
                        </div>
                        <div class="pb-0 mt-0">
                            <div class="d-flex">
                                <div class="">
                                    <h4 class="tx-20 font-weight-bold mb-1 text-white"> @php //echo count(\App\Models\User::where('type','driver')->get()) @endphp </h4>
                                    <a href="{{url('admin.php/drivers')}}" class="mb-0 tx-12 text-white op-7"> مشاهدة التفاصيل  </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 col-xm-12">
                <div class="card overflow-hidden sales-card bg-warning-gradient">
                    <div class="pl-3 pt-3 pr-3 pb-2 pt-0">
                        <div class="">
                            <h6 class="mb-3 tx-12 text-white">  الصفقات  </h6>
                        </div>
                        <div class="pb-0 mt-0">
                            <div class="d-flex">
                                <div class="">
                                    <h4 class="tx-20 font-weight-bold mb-1 text-white"> @php //echo count(\App\Models\User::where('type','supervisor')->get()) @endphp </h4>
                                    <a href="{{url('admin.php/supervisors')}}" class="mb-0 tx-12 text-white op-7"> مشاهدة التفاصيل  </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

    </div>
    <!-- row closed -->

    <!-- /row -->
    </div>
    </div>
    <!-- Container closed -->
@endsection
@section('js')
    <!--Internal  Chart.bundle js -->
    <script src="{{ URL::asset('assets/admin.php/plugins/chart.js/Chart.bundle.min.js') }}"></script>
    <!-- Moment js -->
    <script src="{{ URL::asset('assets/admin.php/plugins/raphael/raphael.min.js') }}"></script>
    <!--Internal  Flot js-->
    <script src="{{ URL::asset('assets/admin.php/plugins/jquery.flot/jquery.flot.js') }}"></script>
    <script src="{{ URL::asset('assets/admin.php/plugins/jquery.flot/jquery.flot.pie.js') }}"></script>
    <script src="{{ URL::asset('assets/admin.php/plugins/jquery.flot/jquery.flot.resize.js') }}"></script>
    <script src="{{ URL::asset('assets/admin.php/plugins/jquery.flot/jquery.flot.categories.js') }}"></script>
    <script src="{{ URL::asset('assets/admin.php/js/dashboard.sampledata.js') }}"></script>
    <script src="{{ URL::asset('assets/admin.php/js/chart.flot.sampledata.js') }}"></script>
    <!--Internal Apexchart js-->
    <script src="{{ URL::asset('assets/admin.php/js/apexcharts.js') }}"></script>
    <!-- Internal Map -->
    <script src="{{ URL::asset('assets/admin.php/plugins/jqvmap/jquery.vmap.min.js') }}"></script>
    <script src="{{ URL::asset('assets/admin.php/plugins/jqvmap/maps/jquery.vmap.usa.js') }}"></script>
    <script src="{{ URL::asset('assets/admin.php/js/modal-popup.js') }}"></script>
    <!--Internal  index js -->
    <script src="{{ URL::asset('assets/admin.php/js/index.js') }}"></script>
    <script src="{{ URL::asset('assets/admin.php/js/jquery.vmap.sampledata.js') }}"></script>
@endsection

