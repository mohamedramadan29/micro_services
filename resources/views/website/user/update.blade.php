@extends('website.layouts.master')
@section('title')
     تعديل الحساب
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
                                <li><a href="#"><i class="ti-email"></i> الرصيد </a></li>
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
                                        <li class="breadcrumb-item"><a href="{{url('/')}}"> الرئيسية  </a></li>

                                        <li class="breadcrumb-item active" aria-current="page"> تعديل الحساب  </li>
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
                                        <h4><i class="fa fa-user mr-1"></i> معلومات الحساب  </h4>
                                    </div>
                                </div>

                                <div class="_dashboard_content_body">
                                    <div class="row">
                                        <div class="col-auto">
                                            <div class="custom-file avater_uploads">
                                                <input type="file" class="custom-file-input" id="customFile">
                                                <label class="custom-file-label" for="customFile"><i class="fa fa-user"></i></label>
                                            </div>
                                        </div>

                                        <div class="col">
                                            <div class="row">
                                                <div class="col-xl-12 col-lg-12">
                                                    <div class="form-group">
                                                        <label> الاسم  </label>
                                                        <input type="text" class="form-control with-light" value="Adam">
                                                    </div>
                                                </div>

                                                <div class="col-xl-6 col-lg-6">
                                                    <div class="form-group">
                                                        <label> نوع الحساب  </label>
                                                        <select class="form-control with-light">
                                                            <option value=""> -- حدد نوع الحساب  -- </option>
                                                            <option> مشتري  </option>
                                                            <option> بائع  </option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-xl-6 col-lg-6">
                                                    <div class="form-group">
                                                        <label> البريد الالكتروني  </label>
                                                        <input type="email" class="form-control with-light" value="uppcl@gmail.com">
                                                    </div>
                                                </div>
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
                                        <h4><i class="ti-settings mr-1"></i> نبذة عنك </h4>
                                    </div>
                                </div>

                                <div class="_dashboard_content_body">
                                    <div class="row">

                                        <div class="col-xl-12 col-lg-12">
                                            <div class="form-group">
                                                <label> المسمي الوظيفي  </label>
                                                <input type="text" class="form-control with-light" value="full Stack developer ">
                                            </div>
                                        </div>
                                        <div class="col-xl-12 col-lg-12">
                                            <div class="form-group">
                                                <label> النبذة التعريفية </label>
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
                                        <h4><i class="ti-lock mr-1"></i> كلمة المرور  </h4>
                                    </div>
                                </div>

                                <div class="_dashboard_content_body">
                                    <div class="row">
                                        <div class="col-xl-4 col-lg-4">
                                            <div class="form-group">
                                                <label> كلمة المرور القديمة  </label>
                                                <input type="password" class="form-control with-light">
                                            </div>
                                        </div>
                                        <div class="col-xl-4 col-lg-4">
                                            <div class="form-group">
                                                <label> كلمة المرور الجديدة  </label>
                                                <input type="password" class="form-control with-light">
                                            </div>
                                        </div>
                                        <div class="col-xl-4 col-lg-4">
                                            <div class="form-group">
                                                <label> تاكيد كلمة المرور </label>
                                                <input type="password" class="form-control with-light">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Single Wrap End -->

                            <button type="button" class="btn btn-save"> حفظ التغيرات  </button>

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
                            <button class="btn btn-subscribe bg-dark" type="button"><i class="fa fa-arrow-right"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- ============================ Call To Action End ================================== -->

@endsection
