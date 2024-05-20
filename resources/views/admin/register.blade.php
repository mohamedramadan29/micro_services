@extends('admin.layouts.master2')
@section('css')
    <!-- Sidemenu-respoansive-tabs css -->
    <link href="{{URL::asset('assets/admin.php/plugins/sidemenu-responsive-tabs/css/sidemenu-responsive-tabs.css')}}"
          rel="stylesheet">
@endsection
@section('content')
    <div class="container-fluid">
        <div class="row no-gutter">
            <!-- The image half -->
            <div class="col-md-6 col-lg-6 col-xl-7 d-none d-md-flex bg-primary-transparent">
                <div class="row wd-100p mx-auto text-center">
                    <div class="col-md-12 col-lg-12 col-xl-12 my-auto mx-auto wd-100p">
                        <img src="{{URL::asset('assets/admin.php/img/media/login.png')}}"
                             class="my-auto ht-xl-80p wd-md-100p wd-xl-80p mx-auto" alt="logo">
                    </div>
                </div>
            </div>
            <!-- The content half -->
            <div class="col-md-6 col-lg-6 col-xl-5 bg-white">
                <div class="login d-flex align-items-center py-2">
                    <!-- Demo content-->
                    <div class="container p-0">
                        <div class="row">
                            <div class="col-md-10 col-lg-10 col-xl-9 mx-auto">
                                <div class="card-sigin">

                                    <div class="card-sigin">
                                        <div class="main-signup-header">
                                            @if(\Illuminate\Support\Facades\Session::has('Error_Message'))
                                                <div class="alert alert-danger"> {{\Illuminate\Support\Facades\Session::get('Error_Message')}} </div>
                                            @endif

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
                                            <h2>مرحبا!</h2>
                                            <h5 class="font-weight-semibold mb-4"> اضافه حساب جديد </h5>
                                            <form action="{{url('admin.php/register')}}" method="post">
                                                @csrf

                                                <div class="form-group">
                                                    <label> الاسم </label>
                                                    <input required type="text" name="name"
                                                           class="form-control" value="{{old('name')}}">
                                                </div>
                                                <div class="form-group">
                                                    <label>البريد الالكتروني </label> <input required type="email"
                                                                                             name="email"
                                                                                             class="form-control"
                                                                                             value="{{old('email')}}">
                                                </div>
                                                <div class="form-group">
                                                    <label> رقم الهاتف </label>
                                                    <input required type="text" name="phone" class="form-control"
                                                           value="{{old('phone')}}">
                                                </div>
                                                <div class="form-group">
                                                    <label> كلمه المرور </label> <input class="form-control"
                                                                                        name="password"

                                                                                        type="password" required>
                                                </div>

                                                <div class="form-group">
                                                    <label> تاكيد كلمه المرور </label> <input class="form-control"
                                                                                              name="confirm_password"

                                                                                              type="password" required>
                                                </div>
                                                <button class="btn btn-main-primary btn-block"> حساب جديد</button>
                                            </form>
                                            <div class="main-signin-footer mt-5">
                                                <p><a href="{{url('/')}}"> لديك حساب </a></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!-- End -->
                </div>
            </div><!-- End -->
        </div>
    </div>
@endsection
@section('js')
@endsection
