@extends('website.layouts.master')
@section('title')
    الملف الشخصي
@endsection
@section('content')
    <!-- ============================ Page Title Start================================== -->
    <div class="page-title bg-cover" style="background:url({{asset('assets/website/img/bn-1.jpg')}})no-repeat;" data-overlay="5">
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
                                        <li class="breadcrumb-item"><a href="{{url("/")}}"> الرئيسية </a></li>
                                        <li class="breadcrumb-item active" aria-current="page"> حسابي</li>
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
                                        <h4><i class="fa fa-user mr-1"></i> حسابي </h4>
                                    </div>
                                </div>
                                <div class="_dashboard_content">
                                    <div class="_dashboard_content_body">
                                        <div class="row">
                                            <div class="col-xl-12 col-lg-12">
                                                <div class="form-group">
                                                    <label> نبذة عني </label>
                                                    <textarea class="form-control with-light">السلام عليكم ورحمة الله
انا اخوكم محمد
مهندس برمجة وعلوم الحاسب
خبرة فى مجال البرمجة وبناء المواقع الالكترونية من الصفر
خبرة اكثر من 6 اعوام
خبرة فى بناء المتاجر الالكترونية بمواصفات عالمية
احتراف الوردبريس واستخدام الووكومرس
html / css / javascript
bootstrap / jquery /
sass/ php / wordpress / sql /
photoshop / woocommerce /
*لماذا أنا:
1_أكمل كل شيء في موعد التسليم
2_وقت استجابة سريع
3_اهتمام كبير بالتفاصيل والاستماع الى طلب الزبون
4_التعديلات بكل سرور خلال وبعد انجاز العمل
5_كما اقدم دعماً اضافياً لعملائي وارشدهم خلال مراحل العمل وبعد التسليم أيضاً(المتابعة مع العملاء بشكل مستمر)
يشرفنى العمل معكم
اتمنى التوفيق
                                                </textarea>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                                <!-- Single Wrap End -->

                                <!-- Single Wrap -->
                                <div class="_dashboard_content">
                                    <div class="_dashboard_content_header">
                                        <div class="_dashboard__header_flex">
                                            <h4><i class="ti-lock mr-1"></i> خدماتي </h4>
                                        </div>
                                    </div>

                                    <div class="_dashboard_content_body">
                                        <div class="row">
                                            <!-- Single Item -->
                                            <div class="col-lg-4 col-md-6 col-sm-12">
                                                <div class="ser_110">
                                                    <div class="ser_110_thumb">
                                                        <a href="#" class="ser_100_link"><img
                                                                src=" {{asset('assets/website/img/co-2.jpg')}}"
                                                                class="img-fluid" alt=""></a>
                                                    </div>
                                                    <div class="ser_110_footer bott">
                                                        <div class="_110_foot_left">
                                                            <div>
                                                                <h5>
                                                                    <a href="#"> برمجة وتصميم موقع من الصفر </a>
                                                                </h5>
                                                                <span> برمجة وتطوير / إنشاء موقع إلكتروني  <span>
                                                              <div class="_dash_usr_rates mb-1">
															<span class="good">4.5</span>
															<i class="fa fa-star"></i>
															<i class="fa fa-star"></i>
															<i class="fa fa-star"></i>
															<i class="fa fa-star"></i>
															<i class="fa fa-star"></i>
														       </div>
                                         </span>
                                    </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-lg-4 col-md-6 col-sm-12">
                                                <div class="ser_110">
                                                    <div class="ser_110_thumb">
                                                        <a href="#" class="ser_100_link"><img
                                                                src=" {{asset('assets/website/img/co-3.jpg')}}"
                                                                class="img-fluid" alt=""></a>
                                                    </div>
                                                    <div class="ser_110_footer bott">
                                                        <div class="_110_foot_left">
                                                            <div>
                                                                <h5>
                                                                    <a href="#">تحويل تصميم من psd الى html </a>
                                                                </h5>
                                                                <span> برمجة وتطوير / إنشاء موقع إلكتروني   <span>
                                                                         <div class="_dash_usr_rates mb-1">
															<span class="good">5</span>
															<i class="fa fa-star"></i>
															<i class="fa fa-star"></i>
															<i class="fa fa-star"></i>
															<i class="fa fa-star"></i>
															<i class="fa fa-star"></i>
														       </div>
                                         </span>
                                    </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>


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
