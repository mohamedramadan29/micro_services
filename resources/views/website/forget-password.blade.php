@extends('website.layouts.master')
@section('title')
    نسيت كلمة المرور
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
                            <h1 class="nf-auth-title"> نسيت كلمة المرور </h1>

                        </div>
                        <div class="modal-body">
                            <div class="login-form">
                                <form action="{{ url('forget-password') }}" method="post">
                                    @csrf
                                    <div class="form-group">
                                        <label for="auth-email"> ادخل البريد الالكتروني المسجل لدينا </label>
                                        <input id="auth-email" type="email" class="form-control" name="email" autocomplete="email" dir="ltr"
                                            value="{{ old('email') }}">
                                    </div>

                                    <div class="form-group">
                                        <button type="submit" class="btn dark-2 btn-md full-width pop-login">
                                            ارسال
                                        </button>
                                    </div>

                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-0"></div>

            </div>
        </div>
    </section>
@endsection
