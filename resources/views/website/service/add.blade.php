@extends('website.layouts.master')
@section('title')
    اضف خدمة جديدة
@endsection
@section('content')
    <!-- ============================ Page Title End ================================== -->
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
                                        <li class="breadcrumb-item active" aria-current="page"> اضف خدمة جديدة</li>
                                    </ol>
                                </nav>
                            </div>
                        </div>
                    </div>

                    <div class="row mobile_form">
                        <form method="post" action="{{ url('service/add') }}" enctype="multipart/form-data"
                            id="uploadService">
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
                                                    <label> اسم الخدمة </label>
                                                    <input type="text" class="form-control with-light" name="name"
                                                        required value="{{ old('name') }}">
                                                </div>
                                            </div>

                                            <div class="col-xl-6 col-lg-6">
                                                <div class="form-group with-light">
                                                    <label> القسم الرئيسي </label>
                                                    <select required id="mainCategory" class="form-select" name="cat_id">
                                                        <option value=""> -- حدد القسم الرئيسي --</option>
                                                        @foreach ($categories as $category)
                                                            <option value="{{ $category['id'] }}"
                                                                {{ old('cat_id') == $category['id'] ? 'selected' : '' }}>
                                                                {{ $category['name'] }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-xl-6 col-lg-6">
                                                <div class="form-group with-light">
                                                    <label> حدد القسم الفرعي </label>
                                                    <select required class="form-select" name="sub_cat_id" id="subCategory">
                                                        @if (old('sub_cat_id'))
                                                            <option value="{{ old('sub_cat_id') }}" selected>القسم السابق
                                                                المحدد</option>
                                                        @endif
                                                    </select>
                                                </div>
                                            </div>


                                            <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
                                            <script>
                                                $(document).ready(function() {
                                                    const oldSubCatId = "{{ old('sub_cat_id') }}";
                                                    $('#mainCategory').on('change', function() {
                                                        var categoryId = $(this).val();
                                                        if (categoryId) {
                                                            $.ajax({
                                                                url: 'http://127.0.0.1:8000/service/get-subcategories/' + categoryId,
                                                                type: 'GET',
                                                                dataType: 'json',
                                                                success: function(data) {
                                                                    $('#subCategory').empty();

                                                                    $.each(data, function(key, value) {
                                                                        const isSelected = oldSubCatId == value.id ?
                                                                            "selected" : "";
                                                                        $('#subCategory').append(
                                                                            `<option value="${value.id}" ${isSelected}>${value.name}</option>`
                                                                        );
                                                                    });
                                                                }
                                                            });
                                                        } else {
                                                            $('#subCategory').empty();
                                                        }
                                                    });
                                                    // قم بتشغيل تغيير يدوي لتحميل الأقسام الفرعية إذا كانت هناك قيمة قديمة
                                                    if ("{{ old('cat_id') }}") {
                                                        $('#mainCategory').val("{{ old('cat_id') }}").trigger('change');
                                                    }
                                                });
                                            </script>

                                            <div class="col-xl-6 col-lg-6">
                                                <div class="form-group">
                                                    <label> سعر الخدمة </label>
                                                    <input type="number" min="5" class="form-control with-light"
                                                        required name="price" step="0.01" value="{{ old('price') }}">
                                                </div>
                                            </div>

                                            <div class="col-xl-12 col-lg-12">
                                                <div class="form-group">
                                                    <label> وصف الخدمة </label>
                                                    <textarea class="form-control with-light" minlength="20" required name="description">{{ old('description') }}</textarea>
                                                </div>
                                            </div>

                                            <div class="col-xl-12 col-lg-12">
                                                <div class="form-group with-light">
                                                    <label> الكلمات المفتاحية <span class="badge badge-danger"> افصل بين كل
                                                            كلمة والاخري ب (,) </span></label>
                                                    <div class="tg_grouping">
                                                        <input type="text" required id="lg-input" name="tags"
                                                            class="form-control with-light"
                                                            placeholder="برمجة , تصميم , ... " value="{{ old('tags') }}">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xl-12 col-lg-12">
                                                <div class="form-group">
                                                    <label> اضافة صور للخدمة </label>
                                                    <br>
                                                    <input required type="file" name="image" class="form-control"
                                                        accept="" id="fileInput">
                                                    <span id="fileNames" class="span_info">لم يتم اختيار ملفات بعد</span>
                                                    <span class="span_info">الامتدادات المسموحة: jpg,png,jpeg,webp. الحجم
                                                        الأقصى للملف 4MB</span>
                                                </div>

                                            </div>

                                        </div>
                                        <div class="alert main_alert">
                                            <p> تنويه: بإرسال هذه الخدمة، فإنك تؤكد أن جميع المعلومات والمحتوى الوارد أصلي
                                                وغير منسوخ من أي خدمة أخرى على الموقع. </p>
                                        </div>
                                        <button type="submit" class="btn btn-save btn-primary" id="submitBtn"> اضف الخدمة <i
                                                class="fa fa-save"></i></button>
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
        </div>
    </section>
    <!-- ============================ Main Section End ================================== -->
@endsection
