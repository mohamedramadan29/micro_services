@extends('website.layouts.master')
@section('title')
    تغيير كلمة المرور
@endsection
@section('content')

    <!-- ============================ Main Section Start ================================== -->
    <section class="gray-bg text-right register_page" dir="rtl">
        <div class="container">
            <div class="nf-auth-grid">
                <div class="modal-content nf-sheet" id="registermodal">
                        <div class="modal-header">
                            <h1 class="nf-auth-title">تغيير كلمة المرور</h1>

                        </div>
                        <div class="modal-body">
                            <div class="login-form">
                                <form action="{{url('user/update_forget_password')}}" method="post">
                                    <input type="hidden" name="code" value="{{ $code }}">
                                    @csrf
                                    <div class="form-group">
                                        <label for="auth-email"> البريد الالكتروني </label>
                                        <input id="auth-email" readonly name="email" autocomplete="email" dir="ltr" type="email" required=""
                                               value="{{$email}}"
                                               class="form-control"
                                               placeholder=" البريد الالكتروني  *">

                                    </div>
                                    <div class="form-group">
                                        <label for="auth-password"> كلمة المرور </label>
                                        <input id="auth-password" type="password" class="form-control" placeholder="*******"
                                               name="password" autocomplete="new-password">
                                    </div>

                                    <div class="form-group">
                                        <label for="auth-confirm_password"> تأكيد كلمة المرور </label>
                                        <input id="auth-confirm_password" type="password" class="form-control" placeholder="*******"
                                               name="confirm_password" autocomplete="new-password">
                                    </div>


                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary dark-2 btn-md full-width pop-login">
                                            ارسال
                                        </button>
                                    </div>

                                </form>
                            </div>
                        </div>
                    </div>
                @include('website.partials.auth-aside')
            </div>
        </div>
    </section>
@endsection
