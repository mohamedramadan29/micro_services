@extends('website.layouts.master')
@section('title')
    اضف خدمة جديدة
@endsection
@section('content')
    <!-- ============================================================== -->
    <!-- Top header  -->
    <!-- ============================================================== -->

    <!-- ============================ Page Title Start================================== -->
    <div class="page-title bg-cover" style="background:url({{asset('assets/website/img/bn-1.jpg')}})no-repeat;"
         data-overlay="5">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-md-12"></div>
            </div>
        </div>
    </div>
    <!-- ============================ Page Title End ================================== -->

    <!-- ============================ Main Section Start ================================== -->
    <section class="gray-bg pt-4 text-right" dir="rtl">
        <div class="container-fluid">
            <div class="row m-0">

                <div class="col-xl-3 col-lg-4 col-md-12 col-sm-12">
                    <div class="dashboard-navbar overlio-top">

                        <div class="d-user-avater">
                            <img src="{{asset('assets/website/img/avatar.png')}}" class="img-fluid rounded" alt="">
                            <h4> Mohamed Ramadan </h4>
                            <span>mohamedramadan2930@gmail.com</span>
                        </div>

                        <div class="d-navigation">
                            <ul id="metismenu">
                                <li><a href="{{url('user/dashboard')}}"><i class="ti-dashboard"></i> الملف الشخصي </a>
                                </li>
                                <li><a href="{{url('service/index')}}"><i class="ti-user"></i> الخدمات </a></li>
                                <li><a href="{{url('service/add')}}"><i class="ti-plus"></i> اضف خدمة جديدة   </a></li>
                                <li><a href="{{url('user/chat')}}"><i class="ti-email"></i>  المحادثات </a></li>
                                <li><a href="{{url('user/reviews')}}"><i class="ti-email"></i> التقيمات </a></li>
                                <li><a href="{{url('user/update')}}"><i class="ti-email"></i> تعديل الملف الشخصي </a></li>
                                <li><a href="messages.html"><i class="ti-email"></i> الرصيد </a></li>
                                <li><a href="#"><i class="ti-power-off"></i> تسجيل خروج </a></li>
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
                                        <li class="breadcrumb-item"><a href="#"> الرئيسية </a></li>
                                        <li class="breadcrumb-item"><a href="#"> لوحة التحكم </a></li>
                                        <li class="breadcrumb-item active" aria-current="page"> اضف خدمة جديدة</li>
                                    </ol>
                                </nav>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12">

                            <!-- Single Wrap -->
                            <div class="_dashboard_content">
                                <div class="_dashboard_content_header">
                                    <div class="_dashboard__header_flex">
                                        <h4><i class="ti-briefcase mr-1"></i> اضف خدمة جديدة </h4>
                                    </div>
                                </div>

                                <div class="_dashboard_content_body">
                                    <div class="row">

                                        <div class="col-xl-12 col-lg-12">
                                            <div class="form-group">
                                                <label> العنوان </label>
                                                <input type="text" class="form-control with-light" name="name" required
                                                       value="{{old('name')}}">
                                            </div>
                                        </div>

                                        <div class="col-xl-6 col-lg-6">
                                            <div class="form-group with-light">
                                                <label> القسم </label>
                                                <select id="jb-category" class="form-control" name="cat_id">
                                                    <option> -- حدد القسم --</option>
                                                    @foreach($categories as $category) @endforeach
                                                    <option value="{{$category['id']}}"> {{$category['name']}} </option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-xl-6 col-lg-6">
                                            <div class="form-group">
                                                <label> السعر </label>
                                                <input type="number" min="5" class="form-control with-light" required
                                                       name="price" value="{{old('price')}}">
                                            </div>
                                        </div>

                                        <div class="col-xl-12 col-lg-12">
                                            <div class="form-group">
                                                <label> وصف الخدمة </label>
                                                <textarea class="form-control with-light" required
                                                          name="description">{{old('description')}}</textarea>
                                            </div>
                                        </div>

                                        <div class="col-xl-12 col-lg-12">
                                            <div class="form-group with-light">
                                                <label> الكلمات المفتاحية </label>
                                                <div class="tg_grouping">
                                                    <input type="text" id="lg-input" name="tags"
                                                           class="form-control with-light"
                                                           placeholder="برمجة , تصميم , ... ">
                                                    <a id="cmd-ChipsAjout" class="btn_groupin_tag"><i
                                                            class="fa fa-plus"></i></a>
                                                </div>
                                                <div id="lg-Chips"></div>
                                            </div>
                                        </div>
                                        <div class="col-xl-12 col-lg-12">
                                            <div class="form-group">
                                                <label> صورة الخدمة </label>
                                                <input type="file" class="form-control with-light" required
                                                       name="image">
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                            <!-- Single Wrap End -->

                            <button type="button" class="btn btn-save"> اضف الخدمة</button>

                        </div>
                    </div>

                </div>

            </div>
        </div>
    </section>
    <!-- ============================ Main Section End ================================== -->

    <!-- ============================ Call To Action Start ================================== -->
    <section class="call-to-act" style="background:#0b85ec url(assets/img/landing-bg.png) no-repeat">
        <div class="container">
            <div class="row justify-content-center">

                <div class="col-lg-7 col-md-8">
                    <div class="clt-caption text-center mb-4">
                        <h2 class="text-light">Subscribe Now!</h2>
                        <p class="text-light">Simple pricing plans. Unlimited web maintenance service</p>
                    </div>
                    <div class="inner-flexible-box subscribe-box">
                        <div class="input-group">
                            <input type="text" class="form-control large" placeholder="Enter your mail here">
                            <button class="btn btn-subscribe bg-dark" type="button"><i class="fa fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- ============================ Call To Action End ================================== -->
@endsection
