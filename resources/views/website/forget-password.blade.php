@extends('website.layouts.master')
@section('title')
     نسيت كلمة المرور
@endsection
@section('content')
    <!-- ============================ Page Title Start================================== -->
    <div class="page-title" style="height: 350px;text-align: right">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-md-12">

                    <h2 class="ipt-title">   نسيت كلمة المرور  </h2>

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
                            <h4> نسيت كلمة المرور  </h4>

                        </div>
                        <div class="modal-body">
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
                            <div class="login-form">
                                <form action="{{url('forget-password')}}" method="post">
                                    @csrf
                                    <div class="form-group">
                                        <label>  البريد الالكتروني </label>
                                        <input type="email" class="form-control" name="email"
                                               value="{{old('email')}}">
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
