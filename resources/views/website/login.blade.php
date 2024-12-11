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
                            <h4> {{ __('login.login') }} </h4>

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
                                <a class="google_login" href="{{ route('auth.google.redirect', 'google') }}"> <i
                                        class="bi bi-google"></i> </a>
                                <a class="facebook_login" href="{{ route('auth.google.redirect', 'facebook') }}"> <i
                                        class="bi bi-facebook"></i> </a>
                            </div>
                            <hr>
                            <br>
                            <div class="login-form">
                                <form method="post" action="{{ url('login') }}">
                                    @csrf
                                    <div class="form-group">
                                        <label> {{ __('login.email') }} </label>
                                        <input type="email" class="form-control" name="email"
                                            value="{{ old('email') }}">
                                    </div>

                                    <div class="form-group">
                                        <label> {{ __('login.password') }} </label>
                                        <input type="password" class="form-control" placeholder="*******" name="password">
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
