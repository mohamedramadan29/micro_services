@extends('website.layouts.master')
@section('title')
   {{ __('login.new_account') }}
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
                            <h4>  {{ __('login.new_register') }} </h4>
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
                                <h4>  {{ __('login.login_socialmedia') }} </h4>
                                <a class="google_login" href="{{route('auth.google.redirect','google')}}"> <i class="bi bi-google"></i> </a>
                            </div>
                            <hr>
                            <br>
                            <div class="login-form">
                                <form method="post" action="{{ url('/register') }}">
                                    @csrf
                                    <div class="form-group">
                                        <label> {{ __('login.name') }} </label>
                                        <input required type="text" class="form-control" name="name"
                                            value="{{ old('name') }}">
                                    </div>
                                    <div class="form-group">
                                        <label>  {{ __('login.user_name') }}</label>
                                        <input required type="text" class="form-control" name="username"
                                            value="{{ old('username') }}">
                                    </div>
                                    <div class="form-group">
                                        <label>  {{ __('login.email') }}</label>
                                        <input required type="email" class="form-control" name="email"
                                            value="{{ old('email') }}">
                                    </div>

                                    <div class="form-group">
                                        <label> {{ __('login.password') }} </label>
                                        <input required type="password" class="form-control" placeholder="*******"
                                            name="password">
                                    </div>

                                    <div class="form-group">
                                        <label> {{ __('login.confirm_password') }} </label>
                                        <input required type="password" class="form-control" placeholder="*******"
                                            name="confirm-password">
                                    </div>
                                    <div class="form-group">
                                        {!! NoCaptcha::display() !!}
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
                </div>
                <div class="col-lg-2 col-0"></div>

            </div>
        </div>
    </section>
@endsection
