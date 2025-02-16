@extends('website.layouts.master')
@section('title')
    تعديل الحساب
@endsection
@section('content')

    <!-- ============================ Main Section Start ================================== -->
    <section class="gray-bg pt-4 text-right profile_page" dir="rtl">
        <div class="container-fluid">
            <div class="row m-0">
                <div class="col-xl-3 col-lg-4 col-md-12 col-sm-12">
                    <div class="dashboard-navbar">

                        <div class="d-user-avater">
                            @if(Auth::user()->image !='')
                                <img src="{{asset('assets/uploads/users_image/'.Auth::user()->image)}}" class="img-fluid rounded" alt="">
                            @else
                                <img src="{{asset('assets/website/img/avatar.png')}}" class="img-fluid rounded" alt="">
                            @endif

                            <h4> {{Auth::user()->user_name}} </h4>
                            <span> {{Auth::user()->email}} </span>
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
                                        <li class="breadcrumb-item"><a href="{{url('/')}}"> الرئيسية </a></li>

                                        <li class="breadcrumb-item active" aria-current="page"> تعديل الحساب</li>
                                    </ol>
                                </nav>
                            </div>
                        </div>
                    </div>
                    @if (Session::has('Success_message'))
                        @php
                            emotify('success', \Illuminate\Support\Facades\Session::get('Success_message'));
                        @endphp
                    @endif
                    @if ($errors->any())
                        @foreach ($errors->all() as $error)
                            @php
                                emotify('error', $error);
                            @endphp
                        @endforeach
                    @endif
                    <form method="post" enctype="multipart/form-data" action="{{url('update-account')}}" autocomplete="off">
                        @csrf
                        <div class="row">
                            <div class="col-lg-12 col-md-12 col-sm-12">

                                <!-- Single Wrap -->
                                <div class="_dashboard_content">
                                    <div class="_dashboard_content_header">
                                        <div class="_dashboard__header_flex">
                                            <h4><i class="fa fa-user mr-1"></i> معلومات الحساب </h4>
                                        </div>
                                    </div>

                                    <div class="_dashboard_content_body">
                                        <div class="row">
                                            <div class="col-auto">
                                                <div class="custom-file avater_uploads">
                                                    <input type="file" class="custom-file-input" name="image" id="customFile" accept="image/*">
                                                    <label class="custom-file-label" for="customFile"><i
                                                            class="fa fa-user"></i></label>
                                                </div>
                                            </div>
                                            <div class="col">
                                                <div class="row">
                                                    <div class="col-xl-12 col-lg-12">
                                                        <div class="form-group">
                                                            <label> الاسم </label>
                                                            <input required type="text" name="name"
                                                                   class="form-control with-light"
                                                                   value="{{Auth::user()->name}}">
                                                        </div>
                                                    </div>

                                                    <div class="col-xl-6 col-lg-6">
                                                        <div class="form-group">
                                                            <label> نوع الحساب </label>
                                                            <select required class="form-control with-light select2"
                                                                    name="account_type">
                                                                <option value=""> -- حدد نوع الحساب --</option>
                                                                <option
                                                                    @if(Auth::user()->account_type == 'مشتري') selected
                                                                    @endif value="مشتري"> مشتري
                                                                </option>
                                                                <option
                                                                    @if(Auth::user()->account_type == 'بائع') selected
                                                                    @endif value="بائع"> بائع
                                                                </option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-xl-6 col-lg-6">
                                                        <div class="form-group">
                                                            <label> البريد الالكتروني </label>
                                                            <input required type="email" class="form-control with-light"
                                                                   name="email" value="{{Auth::user()->email}}">
                                                        </div>
                                                    </div>
                                                    <div class="col-xl-6 col-lg-6">
                                                        <div class="form-group">
                                                            <label> رقم الهاتف  </label>
                                                            <input required type="phone" class="form-control with-light"
                                                                   name="phone" value="{{Auth::user()->phone}}">
                                                        </div>
                                                    </div>
                                                    <div class="col-xl-6 col-lg-6">
                                                        <div class="form-group">
                                                            <label> المسمي الوظيفي  </label>
                                                            <input required type="text" class="form-control with-light"
                                                                   name="job_title" value="{{Auth::user()->job_title}}">
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
                                                    <label> النبذة التعريفية </label>
                                                    <textarea name="info" required
                                                              class="form-control with-light">{{Auth::user()->info}}</textarea>
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
                                            <h4><i class="ti-lock mr-1"></i> كلمة المرور </h4>
                                        </div>
                                    </div>

                                    <div class="_dashboard_content_body">
                                        <div class="row">
                                            <div class="col-xl-4 col-lg-4">
                                                <div class="form-group">
                                                    <label> كلمة المرور القديمة </label>
                                                    <input type="password" name="old_password"
                                                           class="form-control with-light">
                                                </div>
                                            </div>
                                            <div class="col-xl-4 col-lg-4">
                                                <div class="form-group">
                                                    <label> كلمة المرور الجديدة </label>
                                                    <input type="password" name="new_password"
                                                           class="form-control with-light">
                                                </div>
                                            </div>
                                            <div class="col-xl-4 col-lg-4">
                                                <div class="form-group">
                                                    <label> تاكيد كلمة المرور </label>
                                                    <input type="password" name="confirm_password"
                                                           class="form-control with-light">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Single Wrap End -->
                                <button type="submit" class="btn btn-save"> حفظ التغيرات  <i class="fa fa-save"></i>  </button>
                            </div>
                        </div>
                    </form>

                </div>

            </div>
        </div>
    </section>
    <!-- ============================ Main Section End ================================== -->
@endsection
