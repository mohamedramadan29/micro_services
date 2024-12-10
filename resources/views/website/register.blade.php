@extends('website.layouts.master')
@section('title')
    حساب جديد
@endsection
@section('content')

    <!-- ============================ Main Section Start ================================== -->
    <section class="gray-bg text-right register_page" dir="rtl">
        <div class="container">
            <div class="row">
                <div class="col-lg-2 col-0"></div>
                <div class="col-lg-8 col-12">
                    <div class="modal-content" id="registermodal">
                        <div class="modal-header">
                            <h4> انشاء حساب جديد </h4>
                        </div>
                        <div class="modal-body">
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
                            <div class="social_login">
                                <h4> يمكنك التسجيل بإستخدام </h4>
                                <a class="google_login" href="{{route('auth.google.redirect','google')}}"> <i class="bi bi-google"></i> </a>
                                <a class="facebook_login" href="{{route('auth.google.redirect','facebook')}}"> <i class="bi bi-facebook"></i> </a>
                            </div>
                            <hr>
                            <br>
                            <div class="login-form">
                                <form method="post" action="{{ url('/register') }}">
                                    @csrf
                                    <div class="form-group">
                                        <label> الاسم </label>
                                        <input required type="text" class="form-control" name="name"
                                            value="{{ old('name') }}">
                                    </div>
                                    <div class="form-group">
                                        <label> اسم المستخدم </label>
                                        <input required type="text" class="form-control" name="username"
                                            value="{{ old('username') }}">
                                    </div>
                                    <div class="form-group">
                                        <label> البريد الالكتروني </label>
                                        <input required type="email" class="form-control" name="email"
                                            value="{{ old('email') }}">
                                    </div>

                                    <div class="form-group">
                                        <label> كلمة المرور </label>
                                        <input required type="password" class="form-control" placeholder="*******"
                                            name="password">
                                    </div>

                                    <div class="form-group">
                                        <label>تاكيد كلمة المرور </label>
                                        <input required type="password" class="form-control" placeholder="*******"
                                            name="confirm-password">
                                    </div>
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary dark-2 btn-md full-width pop-login">
                                            حساب جديد
                                        </button>
                                    </div>

                                </form>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <div class="mf-link"><i class="ti-user"></i> لديك حساب ؟ <a href="{{ url('login') }}"
                                    class="theme-cl"> تسجيل
                                    دخول </a></div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-0"></div>

            </div>
        </div>
    </section>
@endsection
