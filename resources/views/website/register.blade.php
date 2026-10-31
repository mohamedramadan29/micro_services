@extends('website.layouts.master')
@section('title')
   {{ __('login.new_account') }}
@endsection
@section('content')

    <!-- ============================ Main Section Start ================================== -->
    <section class="gray-bg text-right register_page" dir="rtl">
        <div class="container">
            <div class="nf-auth-grid">
                <div class="modal-content nf-sheet" id="registermodal">
                        <div class="modal-header">
                            <h1 class="nf-auth-title">  {{ __('login.new_register') }} </h1>
                        </div>
                        <div class="modal-body">
                            <div class="social_login">
                                <p>{{ __('login.login_socialmedia') }}</p>
                                <a aria-label="إنشاء حساب باستخدام Google" class="google_login" href="{{route('auth.google.redirect','google')}}"> @include('website.partials.google-mark')<span>Google</span></a>
                            </div>
                            <div class="nf-auth-divider"><span>أو بالبريد الإلكتروني</span></div>
                            <div class="login-form">
                                <form method="post" action="{{ url('/register') }}">
                                    @csrf
                                    <div class="form-group">
                                        <label for="auth-name"> {{ __('login.name') }} </label>
                                        <input id="auth-name" required type="text" class="form-control" name="name" autocomplete="name"
                                            value="{{ old('name') }}">
                                    </div>
                                    <div class="form-group">
                                        <label for="auth-username">  {{ __('login.user_name') }}</label>
                                        <input id="auth-username" required type="text" class="form-control" name="username" autocomplete="username" dir="ltr"
                                            value="{{ old('username') }}">
                                    </div>
                                    <div class="form-group">
                                        <label for="auth-email">  {{ __('login.email') }}</label>
                                        <input id="auth-email" required type="email" class="form-control" name="email" autocomplete="email" dir="ltr"
                                            value="{{ old('email') }}">
                                    </div>

                                    <div class="form-group">
                                        <span class="form-label d-block">نوع الحساب</span>
                                        <div class="account-type-options">
                                            <input type="radio" class="btn-check" name="account_type" id="type_client"
                                                value="{{ \App\Models\User::TYPE_CLIENT }}" required
                                                @checked(old('account_type', request('type') === 'freelancer' ? \App\Models\User::TYPE_FREELANCER : null) == \App\Models\User::TYPE_CLIENT)>
                                            <label class="account-type-option" for="type_client">
                                                <i class="bi bi-briefcase"></i>
                                                <strong> عميل </strong>
                                                <span> أطلب تنفيذ مشاريعي وأستقبل العروض </span>
                                            </label>
                                            <input type="radio" class="btn-check" name="account_type" id="type_freelancer"
                                                value="{{ \App\Models\User::TYPE_FREELANCER }}"
                                                @checked(old('account_type', request('type') === 'freelancer' ? \App\Models\User::TYPE_FREELANCER : null) == \App\Models\User::TYPE_FREELANCER)>
                                            <label class="account-type-option" for="type_freelancer">
                                                <i class="bi bi-person-workspace"></i>
                                                <strong> مستقل </strong>
                                                <span> أقدّم عروضي على طلبات العملاء </span>
                                            </label>
                                        </div>
                                    </div>
                                    @include('website.user.partials.whatsapp-field')

                                    <div class="form-group">
                                        <label for="auth-password"> {{ __('login.password') }} </label>
                                        <input id="auth-password" required type="password" class="form-control" placeholder="*******"
                                            name="password" autocomplete="new-password">
                                    </div>

                                    <div class="form-group">
                                        <label for="auth-confirm-password"> {{ __('login.confirm_password') }} </label>
                                        <input id="auth-confirm-password" required type="password" class="form-control" placeholder="*******"
                                            name="confirm-password" autocomplete="new-password">
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
                                        <button type="submit" class="btn btn-primary dark-2 btn-md full-width pop-login">
                                             {{ __('login.new_account') }}
                                        </button>
                                    </div>

                                </form>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <div class="mf-link"><i class="ti-user"></i> {{ __('login.have_account') }} <a href="{{ url('login') }}"
                                    class="theme-cl">  {{ __('login.login') }} </a></div>
                        </div>
                    </div>
                @include('website.partials.auth-aside')
            </div>
        </div>
    </section>
@endsection
