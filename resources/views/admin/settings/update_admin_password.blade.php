@extends('admin.layouts.master')
@section('title')
    تعديل كلمة مرور الادمن
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="breadcrumb-header justify-content-between">
        <div class="my-auto">
            <div class="d-flex">
                <h4 class="content-title mb-0 my-auto">الرئيسية </h4><span class="text-muted mt-1 tx-13 mr-2 mb-0">/ تعديل كلمة المرور</span>
            </div>
        </div>
    </div>
    <!-- breadcrumb -->
    <!-- row -->
    <div class="row row-sm">

        <!-- Col -->
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    @if(\Illuminate\Support\Facades\Session::has('Error_message'))
                        <div
                            class="alert alert-danger"> {{\Illuminate\Support\Facades\Session::get('Error_message')}} </div>
                    @endif
                    @if(\Illuminate\Support\Facades\Session::has('Success_message'))
                        <div
                            class="alert alert-success"> {{\Illuminate\Support\Facades\Session::get('Success_message')}} </div>
                    @endif

                    <div class="mb-4 main-content-label"> تعديل كلمة المرو الأدمن</div>
                    <form name="updateAdminPasswordForm" class="form-horizontal"
                          action="{{url('admin.php/update_admin_password')}}" autocomplete="off" method="post"
                          enctype="multipart/form-data">
                        @csrf
                        <div class="mb-4 main-content-label">معلومات</div>
                        <div class="form-group ">
                            <div class="row">
                                <div class="col-md-3">
                                    <label class="form-label">البريد الإلكتروني</label>
                                </div>
                                <div class="col-md-9">
                                    <input type="text" readonly class="form-control" placeholder="User Name"
                                           value="{{$adminDetails->email}}">
                                </div>
                            </div>
                        </div>
                        <div class="form-group ">
                            <div class="row">
                                <div class="col-md-3">
                                    <label class="form-label">النوع </label>
                                </div>
                                <div class="col-md-9">
                                    <input type="text" readonly class="form-control" placeholder="First Name"
                                           value="{{$adminDetails->type}}">
                                </div>
                            </div>
                        </div>
                        <div class="form-group ">
                            <div class="row">
                                <div class="col-md-3">
                                    <label class="form-label">كلمة المرور القديمة </label>
                                </div>
                                <div class="col-md-9">
                                    <input required name="old_password" id="old_password" type="password"
                                           class="form-control" placeholder="">
                                    <span id="check_password"></span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group ">
                            <div class="row">
                                <div class="col-md-3">
                                    <label class="form-label"> كلمة المرور الجديدة </label>
                                </div>
                                <div class="col-md-9">
                                    <input type="password" name="new_password" required class="form-control"
                                           placeholder="">
                                </div>
                            </div>
                        </div>
                        <div class="form-group ">
                            <div class="row">
                                <div class="col-md-3">
                                    <label class="form-label">تأكيد كلمة المرور</label>
                                </div>
                                <div class="col-md-9">
                                    <input type="password" class="form-control" required name="confirm_password"
                                           placeholder="">
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-left">
                            <button type="submit" class="btn btn-primary waves-effect waves-light">تعديل كلمة المرور
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
        <!-- /Col -->
    </div>
    <!-- row closed -->
    </div>
    <!-- Container closed -->
    </div>
    <!-- main-content closed -->
@endsection

