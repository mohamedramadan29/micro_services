@extends('website.layouts.master')
@section('title')
    {{$service['name']}}
@endsection
@section('content')
    <!-- ============================================================== -->
    <!-- Top header  -->
    <!-- ============================================================== -->
    <!-- ============================ Page Title Start================================== -->
    <div class="page-title bg-cover" style="background:url({{asset('assets/website/img/bn-1.jpg')}})no-repeat;"
         data-overlay="5">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-md-12"></div>
            </div>
        </div>
    </div>
    <!-- ============================ Page Title End ================================== -->
    <!-- ============================ Main Section Start ================================== -->
    <section class="gray-bg pt-4 text-right" dir="rtl">
        <div class="container-fluid">
            <div class="row m-0">
                <div class="col-xl-3 col-lg-4 col-md-12 col-sm-12">
                    <div class="dashboard-navbar overlio-top">
                        <div class="d-user-avater">
                            @if(Auth::user()->image !='')
                                <img src="{{asset('assets/uploads/users_image/'.Auth::user()->image)}}"
                                     class="img-fluid rounded" alt="">
                            @else
                                <img src="{{asset('assets/website/img/avatar.png')}}" class="img-fluid rounded" alt="">
                            @endif

                            <h4> {{Auth::user()->user_name}} </h4>
                            <span> {{Auth::user()->email}} </span>
                        </div>
                        <div class="d-navigation">
                            <ul id="metismenu">
                                <li><a href="{{url('dashboard')}}"><i class="ti-dashboard"></i> الملف الشخصي </a>
                                </li>
                                <li><a href="{{url('service/index')}}"><i class="ti-user"></i> الخدمات </a></li>
                                <li><a href="{{url('service/add')}}"><i class="ti-plus"></i> اضف خدمة جديدة </a></li>
                                <li><a href="{{url('user/chat')}}"><i class="ti-email"></i> المحادثات </a></li>
                                <li><a href="{{url('user/reviews')}}"><i class="ti-email"></i> التقيمات </a></li>
                                <li><a href="{{url('user/update')}}"><i class="ti-email"></i> تعديل الملف الشخصي </a>
                                </li>
                                <li><a href="{{url('user/balance')}}"><i class="ti-email"></i> الرصيد </a></li>
                                <li><a href="{{url('logout')}}"><i class="ti-power-off"></i> تسجيل خروج </a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Item Wrap Start -->
                <div class="col-xl-9 col-lg-8 col-md-12 col-sm-12">
                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <!-- Breadcrumbs -->
                            <div class="bredcrumb_wrap">
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="#"> الرئيسية </a></li>
                                        <li class="breadcrumb-item"><a href="#"> لوحة التحكم </a></li>
                                        <li class="breadcrumb-item active" aria-current="page"> تعديل الخدمة</li>
                                    </ol>
                                </nav>
                            </div>
                        </div>
                    </div>

                    <div class="row">
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
                        <form method="post" action="{{url('service/update/'.$service['id'])}}" enctype="multipart/form-data">
                            @csrf
                            <div class="col-lg-12 col-md-12 col-sm-12">

                                <!-- Single Wrap -->
                                <div class="_dashboard_content">
                                    <div class="_dashboard_content_header">
                                        <div class="_dashboard__header_flex">
                                            <h4><i class="ti-briefcase mr-1"></i> اضف خدمة جديدة </h4>
                                        </div>
                                    </div>

                                    <div class="_dashboard_content_body">

                                        <div class="row">

                                            <div class="col-xl-12 col-lg-12">
                                                <div class="form-group">
                                                    <label> العنوان </label>
                                                    <input type="text" class="form-control with-light" name="name"
                                                           required
                                                           value="{{$service['name']}}">
                                                </div>
                                            </div>

                                            <div class="col-xl-6 col-lg-6">
                                                <div class="form-group with-light">
                                                    <label> القسم </label>
                                                    <select required id="jb-category" class="form-control"
                                                            name="cat_id">
                                                        <option> -- حدد القسم --</option>
                                                        @foreach($categories as $category)
                                                            <option @if($category['id'] == $service['cat_id']) selected @endif
                                                                value="{{$category['id']}}"> {{$category['name']}} </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-xl-6 col-lg-6">
                                                <div class="form-group">
                                                    <label> السعر </label>
                                                    <input type="number" min="5" class="form-control with-light"
                                                           required
                                                           name="price" value="{{$service['price']}}">
                                                </div>
                                            </div>

                                            <div class="col-xl-12 col-lg-12">
                                                <div class="form-group">
                                                    <label> وصف الخدمة </label>
                                                    <textarea class="form-control with-light" required
                                                              name="description">{{$service['description']}}</textarea>
                                                </div>
                                            </div>

                                            <div class="col-xl-12 col-lg-12">
                                                <div class="form-group with-light">
                                                    <label> الكلمات المفتاحية <span class="badge badge-danger"> افصل بين كل كلمة والاخري ب (,) </span></label>
                                                    <div class="tg_grouping">
                                                        <input type="text" id="lg-input" name="tags"
                                                               class="form-control with-light" value="{{$service['tags']}}"
                                                               placeholder="برمجة , تصميم , ... ">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xl-12 col-lg-12">
                                                <div class="form-group">
                                                    <label> صورة الخدمة </label>
                                                    <input type="file" class="form-control with-light"
                                                           name="image">
                                                    <img width="100px" style="margin-top: 10px;border-radius: 5px" src="{{asset('assets/uploads/services/'.$service['image'])}}">
                                                </div>
                                            </div>

                                        </div>

                                    </div>
                                </div>
                                <!-- Single Wrap End -->

                                <button type="submit" class="btn btn-sm btn-save">  تعديل الخدمة <i class="fa fa-save"></i>  </button>
                        </form>
                    </div>
                </div>

            </div>

        </div>
        </div>
    </section>
    <!-- ============================ Main Section End ================================== -->

@endsection
