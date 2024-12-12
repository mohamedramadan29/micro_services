@extends('website.layouts.master')
@section('title')
    رصيد الحساب
@endsection
@section('content')
    <!-- ============================ Main Section Start ================================== -->
    <section class="gray-bg pt-4 text-right profile_page" dir="rtl">
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

                        <div class="d-navigation">
                            <ul id="metismenu">
                                <li><a href="{{ url('dashboard') }}"><i class="ti-dashboard"></i> الملف الشخصي </a>
                                </li>
                                <li><a href="{{ url('project/index') }}"><i class="ti-user"></i> المشاريع </a></li>
                                <li><a href="{{ url('project/add') }}"><i class="ti-plus"></i> اضف مشروع جديد </a></li>
                                <li><a href="{{ url('service/index') }}"><i class="ti-user"></i> الخدمات </a></li>
                                <li><a href="{{ url('service/add') }}"><i class="ti-plus"></i> اضف خدمة جديدة </a></li>
                                <li><a href="{{ url('chat-main') }}"><i class="ti-email"></i> المحادثات </a></li>
                                <li><a href="{{ url('purches') }}"><i class="ti-email"></i> مشترياتي </a></li>
                                <li><a href="{{ url('orders') }}"><i class="ti-email"></i> الطلبات الواردة </a></li>
                                <li><a href="{{ url('reviews') }}"><i class="ti-email"></i> التقيمات </a></li>
                                <li><a href="{{ url('update-account') }}"><i class="ti-email"></i> تعديل الملف الشخصي
                                    </a></li>
                                <li><a href="{{ url('balance') }}"><i class="ti-email"></i> الرصيد </a></li>
                                <li><a href="{{ url('logout') }}"><i class="ti-power-off"></i> تسجيل خروج </a></li>
                            </ul>
                        </div>

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
                                        <li class="breadcrumb-item active" aria-current="page"> حسابي</li>
                                        <li class="breadcrumb-item active" aria-current="page"> رصيد الحساب</li>
                                    </ol>
                                </nav>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12">

                            <!-- Single Wrap -->
                            <div class="_dashboard_content">
                                <div class="_dashboard_content_header justify-content-between">
                                    <div class="_dashboard__header_flex">
                                        <h4><i class="fa fa-user mr-1"></i> رصيد الحساب </h4>
                                    </div>
                                    <div class="buttons">

                                        <button style="height: 40px" href="#" class="btn btn-primary btn-sm"> سحب رصيد
                                        </button>
                                        <button style="height: 40px" type="button" class="btn btn-danger btn-sm"
                                            data-bs-toggle="modal" data-bs-target="#charge_balance">
                                            شحن رصيد
                                        </button>
                                        <!-- Modal -->
                                        <div class="modal fade buy_services_model" id="charge_balance" tabindex="-1"
                                            aria-labelledby="exampleModalLabel" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h1 class="modal-title fs-5" id="exampleModalLabel"> شحن رصيد
                                                        </h1>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"> X </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <ul>
                                                            <li> منصة نفذها تضمن حقوقك بنسبة 100% .</li>
                                                            <li> لا تتردد ابداً في التواصل معنا إذا احتجت أي مساعدة وسنسعد
                                                                بخدمتك.
                                                            </li>
                                                        </ul>
                                                    </div>
                                                    <div class="modal-footer">
                                                        @if (Auth::check())
                                                            <form style="width: 100%" method="post"
                                                                action="{{ url('charge_balance') }}">
                                                                <div class="form-group">
                                                                    <label> ادخل المبلغ </label>
                                                                    <input style="height: 45px" type="number"
                                                                        class="form-control" name="price" required>
                                                                </div>
                                                                @csrf
                                                                <button type="submit" class="btn global_button"> شحن رصيد
                                                                    <i class="bi bi-bag"></i></button>
                                                            </form>
                                                        @else
                                                            <a href="{{ url('login') }}" type="button"
                                                                class="btn btn-primary"> سجل دخولك الان لتكملة الشحن </a>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                                <div class="_dashboard_content balance_page">
                                    <div class="_dashboard_content_body">
                                        <div class="_dashboard_list_group">
                                            <div class="row">
                                                <div class="col-lg-3 col-6">
                                                    <div class="info">
                                                        <h4> الرصيد القابل للسحب </h4>
                                                        <span> 20 $ </span>
                                                    </div>
                                                </div>
                                                <div class="col-lg-3 col-6">
                                                    <div class="info">
                                                        <h4> الرصيد المعلق </h4>
                                                        <span> 20 $ </span>
                                                    </div>
                                                </div>
                                                <div class="col-lg-3 col-6">
                                                    <div class="info">
                                                        <h4> الرصيد المتاح </h4>
                                                        <span> 20 $ </span>
                                                    </div>
                                                </div>
                                                <div class="col-lg-3 col-6">
                                                    <div class="info">
                                                        <h4> الرصيد الكلي </h4>
                                                        <span> {{ number_format(Auth::user()->balance, 2) }} $ </span>
                                                    </div>
                                                </div>
                                            </div>
                                            {{-- <div class="orders_payments">
                                                <div class="_list_jobs_wraps mng_list shadow_0 border">
                                                    <div class="_list_jobs_f1ex first">
                                                        <div class="_list_110">
                                                            <div class="count_add_balance"> + 20 $ </div>
                                                            <div class="_list_110_caption">
                                                                <h4 class="_jb_title"><a
                                                                        href="#">  الربح من تنفيذ الطلب #2406851 برمجة وتصميم موقع من الصفر   </a>
                                                                </h4>
                                                                <ul class="_grouping_list">
                                                                    <li><span>  200 $   <i
                                                                                class="ti-credit-card"></i> </span>
                                                                    </li>
                                                                    <li><span><i class="ti-timer"></i> 10 / 2012 </span>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="_list_jobs_wraps mng_list shadow_0 border">
                                                    <div class="_list_jobs_f1ex first">
                                                        <div class="_list_110">
                                                            <div class="minues_balance"> - 10 $ </div>
                                                            <div class="_list_110_caption">
                                                                <h4 class="_jb_title"><a
                                                                        href="#">  الربح من تنفيذ الطلب #2406851 برمجة وتصميم موقع من الصفر   </a>
                                                                </h4>
                                                                <ul class="_grouping_list">
                                                                    <li><span>  200 $   <i
                                                                                class="ti-credit-card"></i> </span>
                                                                    </li>
                                                                    <li><span><i class="ti-timer"></i> 10 / 2012 </span>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div> --}}
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
