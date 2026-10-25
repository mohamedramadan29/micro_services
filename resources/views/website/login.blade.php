@extends('website.layouts.master')
@section('title')
    {{ __('login.login') }}
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
                            <h1 class="nf-auth-title">{{ __('login.login') }}</h1>

                        </div>
                        <div class="modal-body">
                           
                            <div class="social_login">
                                <p>{{ __('login.login_socialmedia') }}</p>
                                <a aria-label="تسجيل الدخول باستخدام Google" class="google_login" href="{{ route('auth.google.redirect', 'google') }}"> @include('website.partials.google-mark')<span>Google</span> </a>

                            </div>
                            <div class="nf-auth-divider"><span>أو بالبريد الإلكتروني</span></div>
                            <div class="login-form">
                                <form method="post" action="{{ url('login') }}">
                                    @csrf
                                    <div class="form-group">
                                        <label for="auth-email"> {{ __('login.email') }} </label>
                                        <input id="auth-email" required type="email" class="form-control" name="email" autocomplete="email" dir="ltr"
                                            value="{{ old('email') }}">
                                    </div>

                                    <div class="form-group">
                                        <label for="auth-password"> {{ __('login.password') }} </label>
                                        <input id="auth-password" required type="password" class="form-control" placeholder="*******" name="password" autocomplete="current-password">
                                    </div>
                                    <div class="form-group">
                                        {!! NoCaptcha::display(['data-size' => 'compact']) !!}
                                        @if ($errors->has('g-recaptcha-response'))
                                            <span class="help-block">
                                                <strong class="text-danger">{{ $errors->first('g-recaptcha-response') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary  dark-2 btn-md full-width pop-login">
                                          {{ __('login.login') }}
                                        </button>
                                    </div>

                                </form>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <div class="mf-link"><i class="ti-user"></i> {{ __('login.not_account') }} <a href="{{ url('register') }}"
                                    class="theme-cl"> {{ __('login.new_account') }} </a></div>
                            <div class="mf-forget"><a href="{{ url('forget-password') }}">  {{ __('login.forget_password') }} </a></div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-0"></div>

            </div>
        </div>
    </section>
@endsection
