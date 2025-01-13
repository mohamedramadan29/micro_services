@extends('website.layouts.master')
@section('title')
    اضافة مشروع جديد
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
                                    </a></li>
                                <li><a href="{{ url('service/add') }}"><i class="ti-plus"></i> اضف خدمة جديدة </a></li>
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
                                        <li class="breadcrumb-item active" aria-current="page"> اضف مشروع جديد</li>
                                    </ol>
                                </nav>
                            </div>
                        </div>
                    </div>

                    <div class="row mobile_form">
                        <form method="post" action="{{ url('my/project/add') }}" enctype="multipart/form-data"
                            id="uploadService">
                            @csrf
                            <div class="col-lg-12 col-md-12 col-sm-12">
                                <!-- Single Wrap -->
                                <div class="_dashboard_content">
                                    <div class="_dashboard_content_header">
                                        <div class="_dashboard__header_flex">
                                            <h4><i class="ti-briefcase mr-1"></i> اضف مشروع جديد </h4>
                                        </div>
                                    </div>
                                    <div class="_dashboard_content_body">
                                        <div class="row">
                                            <div class="col-xl-12 col-lg-12">
                                                <div class="form-group">
                                                    <label> عنوان المشروع </label>
                                                    <input type="text" class="form-control with-light" name="title"
                                                        required value="{{ old('title') }}">
                                                </div>
                                            </div>
                                            <div class="col-xl-12 col-lg-12">
                                                <div class="form-group">
                                                    <label> مهارات متعلقة بالمشروع </label>
                                                    <select required class="form-control select2" multiple name="skills[]">
                                                        <option>-- حدد من القائمة --</option>
                                                        <option value="برمجة">برمجة</option>
                                                        <option value="تصميم جرافيك">تصميم جرافيك</option>
                                                        <option value="كتابة محتوى">كتابة محتوى</option>
                                                        <option value="إدارة المشاريع">إدارة المشاريع</option>
                                                        <option value="تحليل البيانات">تحليل البيانات</option>
                                                        <option value="تسويق رقمي">تسويق رقمي</option>
                                                        <option value="التدقيق اللغوي">التدقيق اللغوي</option>
                                                        <option value="التعليق الصوتي">التعليق الصوتي</option>
                                                        <option value="تصميم مواقع">تصميم مواقع</option>
                                                        <option value="تحرير الفيديو">تحرير الفيديو</option>
                                                        <option value="ترجمة">ترجمة</option>
                                                        <option value="دعم فني">دعم فني</option>
                                                        <option value="تطوير ويب">تطوير ويب</option>
                                                        <option value="التسويق عبر وسائل التواصل الاجتماعي">التسويق عبر
                                                            وسائل التواصل الاجتماعي
                                                        </option>
                                                        <option value="استشارات الأعمال">استشارات الأعمال</option>
                                                        <option value="تحليل مالي">تحليل مالي</option>
                                                        <option value="إدارة الموارد البشرية">إدارة الموارد البشرية
                                                        </option>
                                                        <option value="تحسين محركات البحث">تحسين محركات البحث</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
                                            <script>
                                                $(document).ready(function() {
                                                    $('.select2').select2({
                                                        placeholder: "حدد المهارات",
                                                        allowClear: true
                                                    });
                                                });
                                            </script>

                                            <div class="col-xl-12 col-lg-12">
                                                <div class="form-group">
                                                    <label> قم بوصف المشروع </label>
                                                    <textarea class="form-control with-light" required name="description">{{ old('description') }}</textarea>
                                                </div>
                                            </div>
                                            <div class="col-xl-6 col-lg-6">
                                                <div class="form-group">
                                                    <label> الميزانية المتوقعة </label>
                                                    <select required class="form-select" name="price">
                                                        <option disabled selected> -- حدد الميزانية --</option>
                                                        <option value="10 - 25">10 - 25</option>
                                                        <option value="25 - 50">25 - 50</option>
                                                        <option value="50 - 100">50 - 100</option>
                                                        <option value="100 - 250">100 - 250</option>
                                                        <option value="250 - 500">250 - 500</option>
                                                        <option value="500 - 1000">500 - 1000</option>
                                                        <option value="1000 - 2500">1000 - 2500</option>
                                                        <option value="2500 - 5000">2500 - 5000</option>
                                                        <option value="5000 - 10000">5000 - 10000</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-xl-6 col-lg-6">
                                                <div class="form-group">
                                                    <label> المدة المتوقعة للتسليم ( بالايام ) </label>
                                                    <input max="90" min="1" type="number"
                                                        class="form-control with-light" name="days" required
                                                        value="{{ old('days') }}">
                                                </div>
                                            </div>

                                            <div class="col-xl-6 col-lg-6">
                                                <div class="form-group">
                                                    <br>
                                                    <input type="file" name="files[]" class="form-control"
                                                        accept="" id="fileInput" multiple style="display: none;">
                                                    <button type="button" class="btn btn-primary uploadFiles"
                                                        id="uploadButton"> ارفاق ملفات <i class="fa fa-upload"></i>
                                                    </button>
                                                    <span id="fileNames" class="span_info">لم يتم اختيار ملفات بعد</span>
                                                    <span class="span_info">الامتدادات المسموحة: jpg,png,jpeg,webp. الحجم
                                                        الأقصى للملف 4MB</span>
                                                </div>

                                            </div>

                                        </div>
                                    </div>
                                    <button style="margin: auto;display: block" type="submit" class="btn btn-save"
                                        id="submitBtn"> اضافة المشروع <i class="fa fa-save"></i></button>
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


    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#mainCategory').on('change', function() {
                var categoryId = $(this).val();
                if (categoryId) {
                    $.ajax({
                        url: 'http://127.0.0.1:8000/admin/service/get-subcategories/' + categoryId,
                        type: 'GET',
                        dataType: 'json',
                        success: function(data) {
                            $('#subCategory').empty();
                            $('#subCategory').append(
                                '<option> -- حدد القسم الفرعي --</option>');
                            $.each(data, function(key, value) {
                                $('#subCategory').append('<option value="' + value.id +
                                    '">' + value.name + '</option>');
                            });
                        }
                    });
                } else {
                    $('#subCategory').empty();
                    $('#subCategory').append('<option> -- حدد القسم الفرعي --</option>');
                }
            });
        });
    </script>
@endsection
