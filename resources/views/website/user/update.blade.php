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

                                        <li class="breadcrumb-item active" aria-current="page"> تعديل الحساب</li>
                                    </ol>
                                </nav>
                            </div>
                        </div>
                    </div>
                    <form method="post" enctype="multipart/form-data" action="{{ url('update-account') }}"
                        autocomplete="off">
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
                                                    @if (Auth::user()->image != '')
                                                        <img src="{{ asset('assets/uploads/users_image/' . Auth::user()->image) }}"
                                                            class="img-fluid rounded" alt="">
                                                    @else
                                                        <img src="{{ asset('assets/website/img/avatar.png') }}"
                                                            class="img-fluid rounded" alt="">
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col">
                                                <div class="row">
                                                    <div class="col-xl-12 col-lg-12">
                                                        <div class="form-group">
                                                            <label> تعديل الصورة الشخصية </label>
                                                            <input type="file" name="image"
                                                                class="form-control with-light">
                                                        </div>
                                                    </div>
                                                    <div class="col-xl-12 col-lg-12">
                                                        <div class="form-group">
                                                            <label> الاسم </label>
                                                            <input required type="text" name="name"
                                                                class="form-control with-light"
                                                                value="{{ old('name', Auth::user()->name) }}">
                                                        </div>
                                                    </div>

                                                    <div class="col-xl-6 col-lg-6">
                                                        <div class="form-group">
                                                            <label> نوع الحساب </label>
                                                            <select required class="form-control with-light select2"
                                                                name="account_type">
                                                                <option value=""> -- حدد نوع الحساب --</option>
                                                                <option @if (old('account_type', Auth::user()->account_type) == 'مشتري') selected @endif
                                                                    value="مشتري"> مشتري
                                                                </option>
                                                                <option @if (old('account_type', Auth::user()->account_type) == 'بائع') selected @endif
                                                                    value="بائع"> بائع
                                                                </option>
                                                                <option @if (old('account_type', Auth::user()->account_type) == 'موظف') selected @endif
                                                                    value="موظف"> موظف
                                                                </option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-xl-6 col-lg-6">
                                                        <div class="form-group">
                                                            <label> البريد الالكتروني </label>
                                                            <input style="text-align: right;direction:rtl" required type="email" class="form-control with-light"
                                                                name="email" value="{{ old('email', Auth::user()->email) }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-xl-6 col-lg-6">
                                                        <div class="form-group">
                                                            <label> رقم الهاتف </label>
                                                            <input required type="phone" class="form-control with-light"
                                                                name="phone" value="{{ old('phone', Auth::user()->phone) }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-xl-6 col-lg-6">
                                                        <div class="form-group">
                                                            <label> المسمي الوظيفي </label>
                                                            <input required type="text" class="form-control with-light"
                                                                name="job_title" value="{{ old('job_title', Auth::user()->job_title) }}">
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
                                                    <textarea name="info" required class="form-control with-light">{{ old('info', Auth::user()->info) }}</textarea>
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
                                <button type="submit" class="btn btn-save"> حفظ التغيرات <i class="fa fa-save"></i>
                                </button>
                            </div>
                        </div>
                    </form>

                </div>

            </div>
        </div>
    </section>
    <!-- ============================ Main Section End ================================== -->
@endsection
