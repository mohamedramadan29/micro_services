@extends('website.layouts.master')
@section('title')
    تسجيل دخول
@endsection
@section('content')
    <!-- ============================ Page Title Start================================== -->
    <div class="page-title" style="height: 350px;text-align: right">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-md-12">

                    <h2 class="ipt-title"> تسجيل دخول </h2>
                    <span class="ipn-subtitle"> سجل دخولك الان   </span>

                </div>
            </div>
        </div>
    </div>
    <!-- ============================ Page Title End ================================== -->

    <!-- ============================ Main Section Start ================================== -->
    <section class="gray-bg text-right" dir="rtl">
        <div class="container">
            <div class="row">
                <div class="col-lg-2 col-0"></div>
                <div class="col-lg-8 col-12">
                    <div class="modal-content" id="registermodal">
                        <div class="modal-header">
                            <h4> تسجيل دخول </h4>

                        </div>
                        <div class="modal-body">
                            @if(Session::has('Success_message'))
                                <div
                                    class="alert alert-success"> {{Session::get('Success_message')}} </div>
                            @endif

                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            <div class="login-form">
                                <form method="post" action="{{url('login')}}">
                                    @csrf
                                    <div class="form-group">
                                        <label>  البريد الالكتروني </label>
                                        <input type="email" class="form-control" name="email"
                                               value="{{old('email')}}">
                                    </div>

                                    <div class="form-group">
                                        <label> كلمة المرور </label>
                                        <input type="password" class="form-control" placeholder="*******"
                                               name="password">
                                    </div>

                                    <div class="form-group">
                                        <button type="submit" class="btn dark-2 btn-md full-width pop-login"> تسجيل
                                            دخول
                                        </button>
                                    </div>

                                </form>
                            </div>

                            <div class="form-group text-center">
                                <span> او سجل دخولك ب  </span>
                            </div>

                            <div class="social_logs mb-4">
                                <ul class="shares_jobs text-center">
                                    <li><a href="#" class="share fb"><i class="fa fa-facebook"></i></a></li>
                                    <li><a href="#" class="share gp"><i class="fa fa-google"></i></a></li>
                                </ul>
                            </div>

                        </div>
                        <div class="modal-footer">
                            <div class="mf-link"><i class="ti-user"></i> ليس لديك حساب ؟ <a href="{{url('register')}}"
                                                                                            class="theme-cl"> حساب
                                    جديد </a></div>
                            <div class="mf-forget"><a href="#"> نسيت كلمة المرور ؟ </a></div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-0"></div>

            </div>
        </div>
    </section>
@endsection
