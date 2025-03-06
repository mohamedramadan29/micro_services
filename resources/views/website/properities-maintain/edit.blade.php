@extends('website.layouts.master')
@section('title')
    تعديل خدمة الصيانة
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
                                <li><a href="{{ url('my/course/add') }}"><i class="ti-plus"></i> اضف كورس جديد </a></li>
                                <li>
                                    <a href="{{ url('my/properties/index') }}"><i class="bi bi-building"></i> العقارات
                                    </a>
                                </li>
                                <li><a href="{{ url('my/property/add') }}"><i class="ti-plus"></i> اضف عقار جديد </a></li>
                                <li><a href="{{ url('my/property/maintain/index') }}"><i class="bi bi-database-fill"></i> خدمات الصيانة
                                </a></li>
                                <li><a href="{{ url('my/property/maintain/add') }}"><i class="ti-plus"></i> اضف خدمة صيانة
                                        للعقارات </a></li>
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
                                        <li class="breadcrumb-item active" aria-current="page"> تعديل خدمة الصيانة </li>
                                    </ol>
                                </nav>
                            </div>
                        </div>
                    </div>
                    <div class="row mobile_form">
                        <form method="post"
                            action="{{ url('my/property/maintain/update/' . $properity_maintaine['id']) }}"
                            enctype="multipart/form-data" id="uploadService">
                            @csrf
                            <div class="col-lg-12 col-md-12 col-sm-12">
                                <!-- Single Wrap -->
                                <div class="_dashboard_content">
                                    <div class="_dashboard_content_header">
                                        <div class="_dashboard__header_flex">
                                            <h4><i class="ti-briefcase mr-1"></i> تعديل تفاصيل الخدمة </h4>
                                        </div>
                                    </div>

                                    <div class="_dashboard_content_body">
                                        <div class="row">
                                            <div class="col-xl-12 col-lg-12">
                                                <div class="form-group">
                                                    <label> ادخل العنوان </label>
                                                    <input type="text" class="form-control with-light" name="title"
                                                        required value="{{ $properity_maintaine['title'] }}">
                                                </div>
                                            </div>

                                            <div class="col-xl-12 col-lg-12">
                                                <div class="form-group">
                                                    <label> الوصف </label>
                                                    <textarea class="form-control with-light" required name="description">{{ $properity_maintaine['description'] }}</textarea>
                                                </div>
                                            </div>


                                            <div class="col-6">
                                                <div class="form-group">
                                                    <label> نوع العقار</label>
                                                    <select name="category" id="" class="form-select" required>
                                                        <option value="" selected disabled> -- نوع العقار-- </option>
                                                        <option
                                                            {{ $properity_maintaine['category'] == 'شقة' ? 'selected' : '' }}
                                                            value="شقة"> شقة </option>
                                                        <option
                                                            {{ $properity_maintaine['category'] == 'فيلا' ? 'selected' : '' }}
                                                            value="فيلا">فيلا</option>
                                                        <option
                                                            {{ $properity_maintaine['category'] == 'محل تجاري' ? 'selected' : '' }}
                                                            value="محل تجاري">محل تجاري</option>
                                                        <option
                                                            {{ $properity_maintaine['category'] == 'بيت عربي' ? 'selected' : '' }}
                                                            value="بيت عربي">بيت عربي</option>
                                                        <option
                                                            {{ $properity_maintaine['category'] == 'اخرى' ? 'selected' : '' }}
                                                            value="اخرى">اخرى</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="form-group">
                                                    <label> نوع العقد </label>
                                                    <select name="contract_type" id="" class="form-select"
                                                        required>
                                                        <option value="" selected disabled> -- نوع العقد-- </option>
                                                        <option value="عقد سنوي"
                                                            {{ $properity_maintaine['contract_type'] == 'عقد سنوي' ? 'selected' : '' }}>
                                                            عقد
                                                            سنوي</option>
                                                        <option value="عقد شهري"
                                                            {{ $properity_maintaine['contract_type'] == 'عقد شهري' ? 'selected' : '' }}>
                                                            عقد
                                                            شهري</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="form-group">
                                                    <label> نوع الخدمة </label>
                                                    <select name="service_type" id="" class="form-select"
                                                        required>
                                                        <option value="" selected disabled> -- نوع الخدمة-- </option>
                                                        <option value="صيانة مكيفات"
                                                            {{ $properity_maintaine['service_type'] == 'صيانة مكيفات' ? 'selected' : '' }}>
                                                            صيانة
                                                            مكيفات</option>
                                                        <option value="مياه"
                                                            {{ $properity_maintaine['service_type'] == 'مياه' ? 'selected' : '' }}>
                                                            مياه
                                                        </option>
                                                        <option value="نجارة"
                                                            {{ $properity_maintaine['service_type'] == 'نجارة' ? 'selected' : '' }}>
                                                            نجارة
                                                        </option>
                                                        <option value="السباكة"
                                                            {{ $properity_maintaine['service_type'] == 'السباكة' ? 'selected' : '' }}>
                                                            السباكة
                                                        </option>
                                                        <option value="الكهرباء"
                                                            {{ $properity_maintaine['service_type'] == 'الكهرباء' ? 'selected' : '' }}>
                                                            الكهرباء
                                                        </option>
                                                        <option value="الدهان"
                                                            {{ $properity_maintaine['service_type'] == 'الدهان' ? 'selected' : '' }}>
                                                            الدهان
                                                        </option>
                                                        <option value="البلاط"
                                                            {{ $properity_maintaine['service_type'] == 'البلاط' ? 'selected' : '' }}>
                                                            البلاط
                                                        </option>
                                                        <option value="اصلاح العقار"
                                                            {{ $properity_maintaine['service_type'] == 'اصلاح العقار' ? 'selected' : '' }}>
                                                            اصلاح العقار
                                                        </option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="form-group">
                                                    <label> المساحة <span class="badge badge-danger"> م٢ </span> </label>
                                                    <input type="number" class="form-control with-light" name="area"
                                                        required value="{{ $properity_maintaine['area'] }}"
                                                        step="0.01">
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="form-group">
                                                    <label> عدد الغرف </label>
                                                    <input type="number" class="form-control with-light" name="rooms"
                                                        value="{{ $properity_maintaine['rooms'] }}">
                                                </div>
                                            </div>
                                            <div class="col-xl-12 col-lg-12">
                                                <div class="form-group">
                                                    <label> حدد موقع العقار بالتفصيل </label>
                                                    <input type="text" class="form-control with-light" name="location"
                                                        required value="{{ $properity_maintaine['location'] }}">
                                                </div>
                                            </div>
                                            <div class="col-xl-6 col-lg-6">
                                                <div class="form-group">
                                                    <label> المدينة </label>
                                                    <input type="text" class="form-control with-light" name="city"
                                                        required value="{{ $properity_maintaine['city'] }}">
                                                </div>
                                            </div>
                                            <div class="col-xl-6 col-lg-6">
                                                <div class="form-group">
                                                    <label> الدولة </label>
                                                    <input type="text" class="form-control with-light" name="country"
                                                        required value="{{ $properity_maintaine['country'] }}">
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                    <button style="margin: auto;display: block" type="submit" class="btn btn-save"
                                        id="submitBtn"> تعديل الخدمة <i class="fa fa-save"></i></button>
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

                        <script>
                            document.getElementById('uploadButton').addEventListener('click', function() {
                                document.getElementById('fileInput').click();
                            });

                            document.getElementById('fileInput').addEventListener('change', function() {
                                var input = document.getElementById('fileInput');
                                var output = document.getElementById('fileNames');
                                var fileNames = [];
                                for (var i = 0; i < input.files.length; i++) {
                                    fileNames.push(input.files[i].name);
                                }
                                output.textContent = fileNames.length > 0 ? fileNames.join(', ') : 'لم يتم اختيار ملفات بعد';
                            });
                        </script>

                        <style>
                            .uploadFiles {
                                padding: 10px 20px;
                                border-radius: 5px;
                                cursor: pointer;
                                background: transparent;
                                color: #fff;
                                border-color: var(--main-color);
                                outline: none;
                            }

                            .uploadFiles:hover {
                                background: transparent;
                                color: var(--main-color);
                                border-color: var(--main-color);
                            }

                            #fileNames {
                                display: block;
                                margin-top: 10px;
                                color: #333;
                                font-size: 14px
                            }

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
