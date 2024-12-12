@extends('website.layouts.master')
@section('title')
    تعديل المشروع
@endsection
@section('content')
    <!-- ============================================================== -->
    <!-- Top header  -->
    <!-- ============================================================== -->
    <!-- ============================ Page Title Start================================== -->
    <div class="page-title bg-cover" style="background:url({{ asset('assets/website/img/bn-1.jpg') }})no-repeat;"
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
                                <li><a href="{{ url('my/project/index') }}"><i class="ti-user"></i> المشاريع </a></li>
                                <li><a href="{{ url('my/project/add') }}"><i class="ti-plus"></i> اضف مشروع جديد </a></li>
                                <li><a href="{{ url('my/courses') }}"><i class="ti-user"></i> الكورسات  </a></li>
                                <li><a href="{{ url('my/course/add') }}"><i class="ti-plus"></i> اضف كورس جديد </a></li>
                                <li><a href="{{ url('service/index') }}"><i class="ti-user"></i> الخدمات </a></li>
                                <li><a href="{{ url('service/add') }}"><i class="ti-plus"></i> اضف خدمة جديدة </a></li>
                                <li><a href="{{ url('chat-main') }}"><i class="ti-email"></i> المحادثات </a></li>
                                <li><a href="{{ url('tickets') }}"><i class="bi bi-ticket"></i> تذاكري </a></li>
                                <li><a href="{{ url('reviews') }}"><i class="ti-email"></i> التقيمات </a></li>
                                <li><a href="{{ url('update-account') }}"><i class="ti-email"></i> تعديل الملف الشخصي
                                    </a></li>
                                <li><a href="{{ url('balance') }}"><i class="ti-email"></i> الرصيد </a></li>
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
                                        <li class="breadcrumb-item active" aria-current="page"> تعديل المشروع  </li>
                                    </ol>
                                </nav>
                            </div>
                        </div>
                    </div>

                    <div class="row">
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
                        <form method="post" action="{{ url('project/update/' . $project['id']) }}"
                            enctype="multipart/form-data" id="uploadService">
                            @csrf
                            <div class="col-lg-12 col-md-12 col-sm-12">

                                <!-- Single Wrap -->
                                <div class="_dashboard_content">
                                    <div class="_dashboard_content_header">
                                        <div class="_dashboard__header_flex">
                                            <h4><i class="ti-briefcase mr-1"></i> تعديل المشروع </h4>
                                        </div>
                                    </div>

                                    <div class="_dashboard_content_body">

                                        <div class="row">

                                            <div class="col-xl-12 col-lg-12">
                                                <div class="form-group">
                                                    <label> عنوان المشروع </label>
                                                    <input type="text" class="form-control with-light" name="title"
                                                        required value="{{ $project['title'] }}">
                                                </div>
                                            </div>

                                            <div class="col-xl-12 col-lg-12">
                                                <div class="form-group">
                                                    @php
                                                        // استخراج المهارات من قاعدة البيانات وتقسيمها إلى مصفوفة
                                                        $skills = explode(',', $project['skills']);
                                                    @endphp
                                                    <label>مهارات متعلقة بالمشروع</label>
                                                    <select required class="form-control select2" multiple name="skills[]">
                                                        <option disabled>-- حدد من القائمة --</option>
                                                        <option value="برمجة"
                                                            {{ in_array('برمجة', $skills) ? 'selected' : '' }}>برمجة
                                                        </option>
                                                        <option value="تصميم جرافيك"
                                                            {{ in_array('تصميم جرافيك', $skills) ? 'selected' : '' }}>تصميم
                                                            جرافيك</option>
                                                        <option value="كتابة محتوى"
                                                            {{ in_array('كتابة محتوى', $skills) ? 'selected' : '' }}>كتابة
                                                            محتوى</option>
                                                        <option value="إدارة المشاريع"
                                                            {{ in_array('إدارة المشاريع', $skills) ? 'selected' : '' }}>
                                                            إدارة المشاريع</option>
                                                        <option value="تحليل البيانات"
                                                            {{ in_array('تحليل البيانات', $skills) ? 'selected' : '' }}>
                                                            تحليل البيانات</option>
                                                        <option value="تسويق رقمي"
                                                            {{ in_array('تسويق رقمي', $skills) ? 'selected' : '' }}>تسويق
                                                            رقمي</option>
                                                        <option value="التدقيق اللغوي"
                                                            {{ in_array('التدقيق اللغوي', $skills) ? 'selected' : '' }}>
                                                            التدقيق اللغوي</option>
                                                        <option value="التعليق الصوتي"
                                                            {{ in_array('التعليق الصوتي', $skills) ? 'selected' : '' }}>
                                                            التعليق الصوتي</option>
                                                        <option value="تصميم مواقع"
                                                            {{ in_array('تصميم مواقع', $skills) ? 'selected' : '' }}>تصميم
                                                            مواقع</option>
                                                        <option value="تحرير الفيديو"
                                                            {{ in_array('تحرير الفيديو', $skills) ? 'selected' : '' }}>
                                                            تحرير الفيديو</option>
                                                        <option value="ترجمة"
                                                            {{ in_array('ترجمة', $skills) ? 'selected' : '' }}>ترجمة
                                                        </option>
                                                        <option value="دعم فني"
                                                            {{ in_array('دعم فني', $skills) ? 'selected' : '' }}>دعم فني
                                                        </option>
                                                        <option value="تطوير ويب"
                                                            {{ in_array('تطوير ويب', $skills) ? 'selected' : '' }}>تطوير
                                                            ويب</option>
                                                        <option value="التسويق عبر وسائل التواصل الاجتماعي"
                                                            {{ in_array('التسويق عبر وسائل التواصل الاجتماعي', $skills) ? 'selected' : '' }}>
                                                            التسويق عبر وسائل التواصل الاجتماعي</option>
                                                        <option value="استشارات الأعمال"
                                                            {{ in_array('استشارات الأعمال', $skills) ? 'selected' : '' }}>
                                                            استشارات الأعمال</option>
                                                        <option value="تحليل مالي"
                                                            {{ in_array('تحليل مالي', $skills) ? 'selected' : '' }}>تحليل
                                                            مالي</option>
                                                        <option value="إدارة الموارد البشرية"
                                                            {{ in_array('إدارة الموارد البشرية', $skills) ? 'selected' : '' }}>
                                                            إدارة الموارد البشرية</option>
                                                        <option value="تحسين محركات البحث"
                                                            {{ in_array('تحسين محركات البحث', $skills) ? 'selected' : '' }}>
                                                            تحسين محركات البحث</option>
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
                                                    <textarea class="form-control with-light" required name="description">{{ $project['desc'] }}</textarea>
                                                </div>
                                            </div>
                                            <div class="col-xl-6 col-lg-6">
                                                <div class="form-group">
                                                    <label> الميزانية المتوقعة </label>
                                                    <select style="height:55px" required class="form-select"
                                                        name="price">
                                                        <option disabled selected>-- حدد الميزانية --</option>
                                                        @php
                                                            $prices = [
                                                                '10 - 25',
                                                                '25 - 50',
                                                                '50 - 100',
                                                                '100 - 250',
                                                                '250 - 500',
                                                                '500 - 1000',
                                                                '1000 - 2500',
                                                                '2500 - 5000',
                                                                '5000 - 10000',
                                                            ];
                                                        @endphp
                                                        @foreach ($prices as $price)
                                                            <option value="{{ $price }}"
                                                                {{ $project['price'] == $price ? 'selected' : '' }}>
                                                                {{ $price }}
                                                            </option>
                                                        @endforeach
                                                    </select>

                                                </div>
                                            </div>
                                            <div class="col-xl-6 col-lg-6">
                                                <div class="form-group">
                                                    <label> المدة المتوقعة للتسليم ( بالايام ) </label>
                                                    <input max="90" min="1" type="number"
                                                        class="form-control with-light" name="days" required
                                                        value="{{ $project['day_number'] }}">
                                                </div>
                                            </div>

                                            <div class="col-xl-12 col-lg-12">
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
                                                    <br>

                                                    @if (!empty($project['files']) && count($project['files']) > 0)
                                                        @foreach ($project['files'] as $file)
                                                            @if ($file['user_received_id'] == null)
                                                                <a class="btn btn-warning btn-sm"
                                                                    href="{{ asset('assets/uploads/project_files/' . $project['files']) }}">
                                                                    <i class="bi bi-eye"></i> مشاهدة الملف </a>
                                                            @endif
                                                        @endforeach
                                                    @endif

                                                </div>

                                            </div>

                                        </div>

                                    </div>

                                    <!-- Single Wrap End -->
                                    {{--                                <button type="submit" class="btn btn-sm btn-save"> اضف الخدمة <i class="fa fa-save"></i> --}}
                                    {{--                                </button> --}}

                                    <button style="margin: auto;display: block" type="submit" class="btn btn-save"
                                        id="submitBtn"> تعديل المشروع <i class="fa fa-save"></i></button>
                                    <span id="loader" style="display: none;">جاري الإرسال...</span>
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
