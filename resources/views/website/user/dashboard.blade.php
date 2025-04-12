@extends('website.layouts.master')
@section('title')
    {{ \Illuminate\Support\Facades\Auth::user()->name }}
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
                        <div class="d-navigation">
                            <ul id="metismenu">
                                <li><a href="{{ url('dashboard') }}"><i class="ti-dashboard"></i> الملف الشخصي </a>
                                </li>
                                <li><a href="{{ url('balance') }}"><i class="bi bi-credit-card"></i> الرصيد </a></li>
                                <li><a href="{{ url('my/project/index') }}"><i class="bi bi-cast"></i> المشاريع </a></li>
                                <li><a href="{{ url('my/project/add') }}"><i class="ti-plus"></i> اضف مشروع جديد </a></li>
                                <li><a href="{{ url('my/courses') }}"> <i class="bi bi-mortarboard-fill"></i> الكورسات </a>
                                </li>
                                <li><a href="{{ url('my/course/add') }}"><i class="ti-plus"></i> اضف كورس جديد </a></li>
                                <li><a href="{{ url('service/index') }}"><i class="bi bi-database-fill-check"></i> الخدمات
                                    </a>
                                </li>
                                <li><a href="{{ url('my/course/add') }}"><i class="ti-plus"></i> اضف كورس جديد </a></li>
                                <li>
                                    <a href="{{ url('my/properties/index') }}"><i class="bi bi-building"></i> العقارات
                                    </a>
                                </li>
                                <li><a href="{{ url('my/property/add') }}"><i class="ti-plus"></i> اضف عقار جديد </a></li>
                                <li><a href="{{ url('my/property/maintain/add') }}"><i class="ti-plus"></i> اضف خدمة صيانة
                                        للعقارات </a></li>
                                <li><a href="{{ url('my/property/maintain/index') }}"><i class="ti-plus"></i> خدمات الصيانة
                                    </a></li>
                                <li><a href="{{ url('my/job/add') }}"><i class="ti-plus"></i> اضافة وظيفة جديدة
                                    </a></li>
                                <li><a href="{{ url('my/jobs') }}"><i class="ti-plus"></i> وظائفي
                                    </a></li>
                                <li><a href="{{ url('chats') }}"> <i class="bi bi-chat-dots-fill"></i> المحادثات </a>
                                </li>
                                <li><a href="{{ url('tickets') }}"><i class="bi bi-ticket"></i> تذاكري </a></li>
                                {{-- <li><a href="{{ url('reviews') }}"><i class="ti-email"></i> التقيمات </a></li> --}}
                                <li><a href="{{ url('update-account') }}"> <i class="bi bi-gear-fill"></i> تعديل الملف
                                        الشخصي
                                    </a></li>
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
                                    </ol>
                                </nav>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <!-- Single Wrap -->
                            <div class="_dashboard_content public_alerts">
                                <div class="_dashboard_content_header">
                                    <div class="_dashboard__header_flex">
                                        <h4><i class="fa fa-bell mr-1"></i> تذكير </h4>
                                    </div>
                                </div>
                                <div class="_dashboard_content public_alerts">
                                    <div class="_dashboard_content_body">
                                        <div class="row">
                                            <div class="col-xl-12 col-lg-12">
                                                <h4> نذكركم بأن النجاح المستدام يعتمد على بناء الثقة والالتزام بأعلى
                                                    معايير الأمانة والمصداقية. لذا نرجو منكم:
                                                </h4>
                                                <ul class="unstyled">
                                                    <li>
                                                        1. التحلي بالأمانة والمصداقية: كونوا واضحين ودقيقين في وصف
                                                        خدماتكم. الأمانة هي أساس بناء الثقة مع عملائكم.
                                                    </li>
                                                    <li>
                                                        2. الدفع من خلال الموقع: لضمان الشفافية وحماية حقوق الجميع، نرجو
                                                        الالتزام باستخدام نظام الدفع الخاص بالموقع.
                                                    </li>
                                                    <li>
                                                        3. التواصل مع العملاء عبر زوم: نوصي بأن يتم عقد جلسة تواصل لمدة
                                                        15 دقيقة (مجانية) مع العملاء لشرح الخدمات، والإجابة على أسئلتهم،
                                                        وبناء علاقة جيدة معهم.
                                                    </li>
                                                    <li>
                                                        4. الاحترام والاحترافية: تعاملوا مع العملاء بروح إيجابية واحترام
                                                        لضمان تجربة مميزة ومريحة.
                                                    </li>
                                                </ul>
                                                <p>
                                                    نود إعلامكم بأن الموقع يقتطع نسبة 8% فقط من إجمالي الخدمات المقدمة،
                                                    وهي أقل نسبة مقارنة بالعديد من المنصات المنافسة، لضمان تعزيز أرباحكم
                                                    ودعم نجاحكم.
                                                </p>
                                                <p>
                                                    نحن نؤمن بأن هذه الخطوات ستساعد في بناء سمعة قوية وزيادة ثقة العملاء
                                                    بالموقع وبكم كبائعين.
                                                </p>
                                                <p>
                                                    شكرًا لتعاونكم، ونتطلع إلى نجاحكم المستمر معنا.
                                                    <br> مع أطيب التحيات،
                                                    <br> إدارة الموقع
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
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
                                                    <textarea class="form-control with-light">{{ Auth::user()->info }}</textarea>
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
                                            @if ($services->count() > 0)
                                                @foreach ($services as $serv)
                                                    <div class="col-lg-4 col-md-6 col-sm-12">
                                                        <div class="ser_110">
                                                            <div class="ser_110_thumb">
                                                                <a href="{{ url('service/' . $serv['id'] . '-' . $serv['slug']) }}"
                                                                    class="ser_100_link"><img
                                                                        src=" {{ asset('assets/uploads/services/' . $serv['image']) }}"
                                                                        class="img-fluid" alt=""></a>
                                                            </div>
                                                            <div class="ser_110_footer bott">
                                                                <div class="_110_foot_left">
                                                                    <div>
                                                                        <h5>
                                                                            <a
                                                                                href="{{ url('service/' . $serv['id'] . '-' . $serv['slug']) }}">
                                                                                {{ $serv['name'] }} </a>
                                                                        </h5>
                                                                        <span> {{ $serv['category']['name'] }} <span>
                                                                                {{-- <div class="_dash_usr_rates mb-1">
                                                                                    <span class="good">
                                                                                        {{ $serv['rate'] }} </span>
                                                                                    @for ($i = 0; $i < 5; $i++)
                                                                                        @if ($i < $serv['rate'])
                                                                                            <i class="fa fa-star"></i>
                                                                                        @else
                                                                                            <i class="fa fa-star-o"></i>
                                                                                        @endif
                                                                                    @endfor
                                                                                </div> --}}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="buttons" style="text-align: center;padding:10px">
                                                                <a href="{{ url('service/update/' . $serv['id']) }}"
                                                                    class="btn btn-primary btn-sm"> تعديل <i
                                                                        class="fa fa-edit"></i> </a>
                                                                <a style="height: 30px"
                                                                    href="{{ url('service/delete/' . $serv['id']) }}"
                                                                    class="btn btn-danger btn-sm"
                                                                    onclick="return confirm('هل أنت متأكد أنك تريد حذف هذا العنصر؟')">
                                                                    حذف <i class="fa fa-trash"></i> </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @else
                                                <div class="alert alert-info"> لا يوجد لديك اي خدمات :: ادخل خدمتك
                                                    الاولي
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
