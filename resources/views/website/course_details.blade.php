@extends('website.layouts.master')
@section('title')
    {{ $course['title'] }}
@endsection
@section('content')
    <section class="gray-bg text-right" dir="rtl">
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
        <div class="container">
            <div class="main_hero_section">
                <div>
                    <h4> {{ $course['title'] }} </h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="#"> الرئيسية </a></li>
                            <li class="breadcrumb-item"><a href="#"> الكورسات </a></li>
                            <li class="breadcrumb-item active" aria-current="page"> {{ $course['title'] }} </li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <a class="btn btn-global-button" href="{{ url('my/course/add') }}"> اضف كورس الان <i
                            class="fa fa-plus"></i> </a>
                </div>
            </div>
            <div class="project_details">
                <div class="row">
                    <div class="col-lg-8">
                        <div class="project-details">
                            <h3> {{ $course['title'] }} </h3>
                            <p> {{ $course['desc'] }} </p>
                        </div>
                        <div class="project_offers">
                            <h3> مميزات الكورس </h3>
                            @php
                                $advs = explode(',', $course['adv']);
                            @endphp
                            @foreach ($advs as $adv)
                                @if (trim($adv) != '')
                                    <!-- التحقق من أن العنصر غير فارغ -->
                                    <p><i style="color: var(--main-color)" class="fa fa-check-circle"></i>
                                        {{ $adv }} </p>
                                @endif
                            @endforeach
                        </div>
                        <div class="project_offers">
                            <h3> ماذا سوف تتعلم </h3>
                            @php
                                $learns = explode(',', $course['learn']);
                            @endphp
                            @foreach ($learns as $learn)
                                @if (trim($learn) != '')
                                    <!-- التحقق من أن العنصر غير فارغ -->
                                    <p><i style="color: var(--main-color)" class="fa fa-check-circle"></i>
                                        {{ $learn }} </p>
                                @endif
                            @endforeach
                        </div>
                        @if ($course['terms'] != '')
                            <div class="project_offers">
                                <h3> هل هناك شروط لدخول الكورس </h3>
                                @php
                                    $terms = explode(',', $course['terms_course']);
                                @endphp
                                @foreach ($terms as $term)
                                    @if (trim($term) != '')
                                        <!-- التحقق من أن العنصر غير فارغ -->
                                        <p><i style="color: var(--main-color)" class="fa fa-check-circle"></i>
                                            {{ $term }} </p>
                                    @endif
                                @endforeach
                            </div>
                        @endif

                        @if(Auth::user()->id != $course['user_id'])
                        <div class="project_offers ">
                            <h3> اشتراك في الكورس </h3>

                            @if (Auth::check())
                                @php
                                    // تحقق مما إذا كان المستخدم مسجلًا بالفعل في الدورة
                                    $isRegistered = \App\Models\front\CourseRegister::where('course_id', $course['id'])
                                        ->where('user_id', Auth::id())
                                        ->exists();
                                @endphp
                                @if ($isRegistered)
                                    <div class="alert alert-success" role="alert">
                                        تم الاشتراك في هذه الدورة مسبقًا.
                                    </div>
                                @else
                                    <button data-bs-toggle="modal" data-bs-target="#course_register"
                                        class="btn btn-primary m-auto text-center mt-4"> الاشتراك في الكورس </button>
                                @endif
                                <!-- Modal -->
                                <div class="modal fade buy_services_model" id="course_register" tabindex="-1"
                                    aria-labelledby="exampleModalLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h1 class="modal-title fs-5" id="exampleModalLabel"> هل انت متاكد من اشتراكك
                                                    في الكورس </h1>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"> X
                                                </button>
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
                                                    @if ($isRegistered)
                                                        <div class="alert alert-success" role="alert">
                                                            تم الاشتراك في هذه الدورة مسبقًا.
                                                        </div>
                                                    @else
                                                        <form style="width: 100%" method="post"
                                                            action="{{ url('course_regitser/' . $course['id']) }}">
                                                            <div class="form-group">
                                                                <label> السعر </label>
                                                                <input disabled readonly style="height: 45px"
                                                                    value="{{ $course['price'] }} $" class="form-control"
                                                                    name="country" required>
                                                            </div>
                                                            @csrf
                                                            <button type="submit" class="btn global_button"> اشترك الان <i
                                                                    class="bi bi-bag"></i></button>
                                                        </form>
                                                    @endif
                                                @else
                                                    <a href="{{ url('login') }}" type="button" class="btn btn-primary">
                                                        سجل دخولك الان لتكملة الاشتراك </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="logins_buttons">
                                    <a href="{{ url('register') }}" class="btn btn-primary"> حساب جديد </a>
                                    <a href="{{ url('login') }}" class="btn btn-outline-primary"> تسجيل دخول </a>
                                </div>
                            @endif
                        </div>
                        @endif


                    </div>
                    <div class="col-lg-4">
                        <div class="project-status">
                            <h3>بطاقة الكورس </h3>
                            <ul>
                                <li><span> عدد الساعات :</span> {{ $course['course_hourse'] }} ساعة </li>
                                <li><span> عدد المحاضرات :</span> {{ $course['leason_numbers'] }} محاضرة </li>
                                <li><span> سعر الاشتراك :</span> {{ $course['price'] }} $ </li>
                                <li><span> عدد الافراد :</span> {{ $course['student_num'] }} فرد</li>
                                <li><span> عدد المشتركين :</span> {{ $course['current_student_num'] }} </li>
                                <li><span>تاريخ النشر:</span> {{ $course->created_at->diffForHumans() }} </li>
                            </ul>
                            <div class="project_owner">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6> صاحب الكورس </h6>
                                </div>
                                <div class="user_info">
                                    <div>
                                        @if ($course['User']['image'] != '')
                                            <img
                                                src="{{ asset('assets/uploads/users_image/' . $course['User']['image']) }}">
                                        @else
                                            <img src="{{ asset('assets/website/img/favicon.png') }}"
                                                class="img-fluid rounded" alt="">
                                        @endif
                                    </div>
                                    <div>
                                        <p> {{ $course['User']['name'] }} </p>
                                        <span>
                                            {{ optional($course->User)->job_title }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <hr>
                        </div>
                        <div class="project-status">
                            <h3> شارك </h3>
                            <!-- AddToAny BEGIN -->
                            <div class="sharing">
                                <div class="a2a_kit a2a_kit_size_32 a2a_default_style">
                                    <a class="a2a_dd" href="https://www.addtoany.com/share"></a>
                                    <a class="a2a_button_facebook"></a>
                                    <a class="a2a_button_whatsapp"></a>
                                    <a class="a2a_button_telegram"></a>
                                    <a class="a2a_button_linkedin"></a>
                                    <a class="a2a_button_threads"></a>
                                    <a class="a2a_button_x"></a>
                                    <a class="a2a_button_twitter"></a>
                                    <a class="a2a_button_snapchat"></a>
                                </div>
                                <script defer src="https://static.addtoany.com/menu/page.js"></script>
                                <!-- AddToAny END -->
                            </div>

                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
    <!-- ============================ Main Section End ================================== -->
@endsection
