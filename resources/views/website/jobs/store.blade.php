@extends('website.layouts.master')
@section('title')
    اضافة وظيفة جديدة
@endsection
@section('content')
    <!-- ============================ Main Section Start ================================== -->
    <section class="gray-bg pt-4 text-right profile_page" dir="rtl">
        <div class="container-fluid">
            <div class="row m-0">
                <div class="col-xl-3 col-lg-4 col-md-12 col-sm-12">
                    <div class="dashboard-navbar">
                        <div class="d-user-avater">
                            @if (Auth::user()->image != '')
                                <img src="{{ asset('assets/uploads/users_image/' . Auth::user()->image) }}"
                                    class="img-fluid rounded" alt="">
                            @else
                                <img src="{{ asset('assets/website/img/avatar.png') }}" class="img-fluid rounded"
                                    alt="">
                            @endif

                            <h4> {{ Auth::user()->user_name }} </h4>
                            <span> {{ Auth::user()->email }} </span>
                        </div>
                        <div class="d-navigation">
                            <ul id="metismenu">
                                <li><a href="{{ url('dashboard') }}"><i class="ti-dashboard"></i> الملف الشخصي </a>
                                </li>
                                <li><a href="{{ url('balance') }}"><i class="bi bi-credit-card"></i> الرصيد </a></li>
                                <li><a href="{{ url('my/project/index') }}"><i class="bi bi-cast"></i> المشاريع </a></li>
                                <li><a href="{{ url('my/project/add') }}"><i class="ti-plus"></i> اضف مشروع جديد </a></li>
                                <li><a href="{{ url('my/courses') }}"> <i class="bi bi-mortarboard-fill"></i> الكورسات </a>
                                </li>
                                <li><a href="{{ url('my/course/add') }}"><i class="ti-plus"></i> اضف كورس جديد </a></li>
                                <li><a href="{{ url('service/index') }}"><i class="bi bi-database-fill-check"></i> الخدمات
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ url('my/properties/index') }}"><i class="bi bi-building"></i> العقارات
                                    </a>
                                </li>
                                <li><a href="{{ url('my/property/add') }}"><i class="ti-plus"></i> اضف عقار جديد </a></li>
                                <li><a href="{{ url('my/property/maintain/add') }}"><i class="ti-plus"></i> اضف خدمة صيانة
                                        للعقارات </a></li>
                                <li><a href="{{ url('my/property/maintain/index') }}"><i class="ti-plus"></i> خدمات الصيانة
                                    </a></li>
                                <li><a href="{{ url('my/job/add') }}"><i class="ti-plus"></i> اضافة وظيفة جديدة
                                    </a></li>
                                <li><a href="{{ url('my/jobs') }}"><i class="ti-plus"></i> وظائفي
                                    </a></li>
                                <li><a href="{{ url('chats') }}"> <i class="bi bi-chat-dots-fill"></i> المحادثات </a>
                                </li>
                                <li><a href="{{ url('tickets') }}"><i class="bi bi-ticket"></i> تذاكري </a></li>
                                {{-- <li><a href="{{ url('reviews') }}"><i class="ti-email"></i> التقيمات </a></li> --}}
                                <li><a href="{{ url('update-account') }}"> <i class="bi bi-gear-fill"></i> تعديل الملف
                                        الشخصي
                                    </a></li>
                                <li><a href="{{ url('logout') }}"><i class="ti-power-off"></i> تسجيل خروج </a></li>
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
                                        <li class="breadcrumb-item active" aria-current="page"> اضف وظيفة جديدة </li>
                                    </ol>
                                </nav>
                            </div>
                        </div>
                    </div>
                    <div class="row mobile_form">
                        <form method="post" action="{{ url('my/job/add') }}" enctype="multipart/form-data"
                            id="uploadService">
                            @csrf
                            <div class="col-lg-12 col-md-12 col-sm-12">
                                <!-- Single Wrap -->
                                <div class="_dashboard_content">
                                    <div class="_dashboard_content_header">
                                        <div class="_dashboard__header_flex">
                                            <h4><i class="ti-briefcase mr-1"></i> اضف وظيفة جديدة </h4>
                                        </div>
                                    </div>

                                    <div class="_dashboard_content_body">
                                        <div class="row">
                                            <div class="col-xl-12 col-lg-12">
                                                <div class="form-group">
                                                    <label> العنوان الوظيفي </label>
                                                    <input type="text" class="form-control with-light" name="title"
                                                        required value="{{ old('title') }}">
                                                </div>
                                            </div>

                                            <div class="col-xl-12 col-lg-12">
                                                <div class="form-group">
                                                    <label> قسم الوظيفة </label>
                                                    <select name="category_id" class="form-select" id="">
                                                        <option value="" selected disabled> -- حدد القسم -- </option>
                                                        @foreach ($categories as $category)
                                                            <option @selected(old('category_id') == $category['id'])
                                                                value="{{ $category['id'] }}">{{ $category['name'] }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-xl-12 col-lg-12">
                                                <div class="form-group">
                                                    <label> وصف الوظيفة </label>
                                                    <textarea class="form-control with-light" required name="description">{{ old('description') }}</textarea>
                                                </div>
                                            </div>
                                            <div class="col-xl-12 col-lg-12">
                                                <div class="form-group">
                                                    <label> الخبرات المطلوبة <span class="badge badge-danger"> افصل بين كل
                                                            نقطة
                                                            والاخري ب (,) </span> </label>
                                                    <textarea class="form-control with-light" name="experience">{{ old('experience') }}</textarea>
                                                </div>
                                            </div>
                                            <div class="col-xl-12 col-lg-12">
                                                <div class="form-group">
                                                    <label> حدد الجنس </label>
                                                    <select required name="sex" class="form-select" id="">
                                                        <option value="" selected disabled> -- حدد الجنس -- </option>
                                                        <option @selected(old('sex') == 'ذكر') value="ذكر">ذكر</option>
                                                        <option @selected(old('sex') == 'انثى') value="انثى">انثى</option>
                                                        <option @selected(old('sex') == 'غير محدد') value="غير محدد">غير محدد
                                                        </option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-xl-12 col-lg-12">
                                                <div class="form-group">
                                                    <label> المدينة </label>
                                                    <input type="text" class="form-control with-light" name="city"
                                                        required value="{{ old('city') }}">
                                                </div>
                                            </div>
                                            <div class="col-xl-12 col-lg-12">
                                                <div class="form-group">
                                                    <label> الدولة </label>
                                                    <input type="text" class="form-control with-light" name="country"
                                                        required value="{{ old('country') }}">
                                                </div>
                                            </div>
                                            <div class="col-xl-12 col-lg-12">
                                                <div class="form-group">
                                                    <label> العنوان </label>
                                                    <input type="text" class="form-control with-light" name="address"
                                                        required value="{{ old('address') }}">
                                                </div>
                                            </div>
                                            <div class="col-xl-12 col-lg-12">
                                                <div class="form-group">
                                                    <label> الراتب <span class="badge badge-danger"> $ </span> </label>
                                                    <input type="number" step="0.01" class="form-control with-light"
                                                        name="salary" required value="{{ old('salary') }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <button style="margin: auto;display: block" type="submit" class="btn btn-save"
                                        id="submitBtn"> اضافة الوظيفة <i class="fa fa-save"></i></button>
                                    <span id="loader" style="display: none;">جاري الإرسال...</span>
                                </div>
                            </div>
                        </form>

                        <script>
                            document.getElementById('uploadService').addEventListener('submit', function(event) {
                                document.getElementById('submitBtn').disabled = true; // تعطيل زر الإرسال
                                document.getElementById('submitBtn').style.display = 'none'; // إخفاء زر الإرسال
                                document.getElementById('loader').style.display = 'inline'; // إظهار مؤشر التحميل
                            });
                        </script>
                        <style>
                            #loader {
                                font-size: 16px;
                                color: var(--main-color);
                            }
                        </style>
                    </div>
                </div>

            </div>

        </div>
    </section>
    <!-- ============================ Main Section End ================================== -->
@endsection
