@extends('website.layouts.master')
@section('title')
    تعديل المشروع
@endsection
@section('content')
    <!-- ============================================================== -->
    <!-- Top header  -->
    <!-- ============================================================== -->
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

                        @include('website.layouts.dashboard-sidebar')


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
                                        <li class="breadcrumb-item active" aria-current="page"> تعديل المشروع </li>
                                    </ol>
                                </nav>
                            </div>
                        </div>
                    </div>

                    <div class="row mobile_form">
                        <form method="post" action="{{ url('my/project/update/' . $project['id']) }}"
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
                                                        @foreach ($sub_categories as $sub_category)
                                                            <option value="{{ $sub_category->id }}"
                                                                {{ in_array($sub_category->id, $skills) ? 'selected' : '' }}>
                                                                {{ $sub_category->name }}
                                                            </option>
                                                        @endforeach
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
                                                    <select style="height:55px" required class="form-select" name="price">
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

                                    <button style="margin: auto;display: block" type="submit" class="btn btn-save btn-primary"
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
