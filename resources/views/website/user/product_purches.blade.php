@extends('website.layouts.master')
@section('title')
    مشتريات المنتجات
@endsection
@section('content')
    <!-- ============================ Main Section Start ================================== -->
    <section class="gray-bg pt-4 text-right profile_page" dir="rtl">
        <div class="container-fluid">
            <div class="row m-0">

                <div class="col-xl-3 col-lg-4 col-md-12 col-sm-12">
                    <div class="dashboard-navbar">

                        <div class="d-user-avater">
                            @if (Auth::user()->image != '')
                                <img src="{{ asset('assets/uploads/users_image/' . Auth::user()->image) }}"
                                    class="img-fluid rounded" alt="">
                            @else
                                <img src="{{ asset('assets/website/img/avatar.png') }}" class="img-fluid rounded"
                                    alt="">
                            @endif

                            <h4> {{ Auth::user()->user_name }} </h4>
                            <span> {{ Auth::user()->email }} </span>
                        </div>
                        @include('website.layouts.dashboard-sidebar')

                    </div>
                </div>

                <!-- Item Wrap Start -->
                <div class="col-xl-9 col-lg-8 col-md-12 col-sm-12">
                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <!-- Breadcrumbs -->
                            <div class="bredcrumb_wrap">
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="{{ url('/') }}"> الرئيسية </a></li>
                                        <li class="breadcrumb-item active" aria-current="page"> مشتريات المنتجات </li>
                                    </ol>
                                </nav>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12">

                            <!-- Single Wrap -->
                            <div class="_dashboard_content">
                                <!-- Single Wrap -->
                                <div class="_dashboard_content">
                                    <div class="_dashboard_content_header">
                                        <div class="_dashboard__header_flex">
                                            <h4><i class="ti-lock mr-1"></i> مشتريات المنتجات </h4>
                                        </div>
                                    </div>

                                    <div class="_dashboard_content_body project_page">
                                        <div class="row">
                                            <!-- Single Item -->
                                            @if ($purches->count() > 0)
                                                <div class="table-responsive">
                                                    <table class="table table-bordered">
                                                        <thead>
                                                            <tr style="border-color: #eaeff5">
                                                                <th style="border-color: #eaeff5">المنتج</th>
                                                                <th style="border-color: #eaeff5">السعر</th>
                                                                <th style="border-color: #eaeff5"> الدولة </th>
                                                                <th style="border-color: #eaeff5"> المدينة </th>
                                                                <th style="border-color: #eaeff5"> العنوان </th>
                                                                <th style="border-color: #eaeff5"> الحالة</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach ($purches as $purch)
                                                                <tr>
                                                                    <td>{{ $purch->product_name }}</td>
                                                                    <td>{{ $purch->price }} $</td>
                                                                    <td>{{ $purch->country }}</td>
                                                                    <td>{{ $purch->city }}</td>
                                                                    <td>{{ $purch->address }}</td>
                                                                    <td>

                                                                        @if ($purch->product->type == 'digital' && $purch->product->digital_file)
                                                                            <a href="{{ route('download.product', $purch->id) }}"
                                                                                class="btn btn-success">
                                                                                <i class="bi bi-download"></i> تنزيل الملف
                                                                                الرقمي
                                                                            </a>
                                                                        @else
                                                                            {{ $purch->order_status }}
                                                                        @endif


                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @else
                                                <div class="alert alert-info">
                                                    لا يوجد لديك مشتريات في الوقت الحالي
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>
            </div>
    </section>
    <!-- ============================ Main Section End ================================== -->

@endsection
