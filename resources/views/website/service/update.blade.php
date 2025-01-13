@extends('website.layouts.master')
@section('title')
    {{$service['name']}}
@endsection
@section('content')

    <!-- ============================ Main Section Start ================================== -->
    <section class="gray-bg pt-4 text-right profile_page" dir="rtl">
        <div class="container-fluid">
            <div class="row m-0">
                <div class="col-xl-3 col-lg-4 col-md-12 col-sm-12">
                    <div class="dashboard-navbar">
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
                                <li><a href="{{ url('dashboard') }}"><i class="ti-dashboard"></i> الملف الشخصي </a>
                                </li>
                                <li><a href="{{ url('balance') }}"><i class="bi bi-credit-card"></i> الرصيد </a></li>
                                <li><a href="{{ url('my/project/index') }}"><i class="bi bi-cast"></i> المشاريع </a></li>
                                <li><a href="{{ url('my/project/add') }}"><i class="ti-plus"></i> اضف مشروع جديد </a></li>
                                <li><a href="{{ url('my/courses') }}"> <i class="bi bi-mortarboard-fill"></i> الكورسات </a>
                                </li>
                                <li><a href="{{ url('my/course/add') }}"><i class="ti-plus"></i> اضف كورس جديد </a></li>
                                <li><a href="{{ url('service/index') }}"><i class="bi bi-database-fill-check"></i> الخدمات </a></li>
                                <li><a href="{{ url('service/add') }}"><i class="ti-plus"></i> اضف خدمة جديدة </a></li>
                                <li><a href="{{ url('chat-main') }}"> <i class="bi bi-chat-dots-fill"></i> المحادثات </a></li>
                                <li><a href="{{ url('tickets') }}"><i class="bi bi-ticket"></i> تذاكري </a></li>
                                {{-- <li><a href="{{ url('reviews') }}"><i class="ti-email"></i> التقيمات </a></li> --}}
                                <li><a href="{{ url('update-account') }}"> <i class="bi bi-gear-fill"></i> تعديل الملف الشخصي
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
                                        <li class="breadcrumb-item active" aria-current="page"> تعديل الخدمة</li>
                                    </ol>
                                </nav>
                            </div>
                        </div>
                    </div>

                    <div class="row mobile_form">
                        <form id="uploadService" method="post" action="{{url('service/update/'.$service['id'])}}" enctype="multipart/form-data">
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
                                                    <label>  القسم الرئيسي  </label>
                                                    <select required class="form-control select2" name="cat_id" id="mainCategory">
                                                        <option> -- حدد القسم --</option>
                                                        @foreach($categories as $category)
                                                            <option @if($service['cat_id'] == $category['id']) selected @endif value="{{$category['id']}}"> {{$category['name']}} </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-xl-6 col-lg-6">
                                                <div class="form-group with-light">
                                                    <label>  القسم الفرعي  </label>
                                                    <select required class="form-control select2" name="sub_cat_id" id="subCategory">
                                                        <option> -- القسم الفرعي --</option>
                                                        @foreach($subCategories as $subcategory)
                                                            <option @if($service['sub_cat_id'] == $subcategory['id']) selected @endif value="{{$subcategory['id']}}"> {{$subcategory['name']}} </option>
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
                                                    <label> اضافة صور للخدمة  </label>
                                                    <br>
                                                    <input type="file" name="image"
                                                           class="form-control" accept=""
                                                           id="fileInput" multiple style="display: none;">
                                                    <button type="button" class="btn btn-primary uploadFiles"
                                                            id="uploadButton">ارفع الملفات  <i class="fa fa-upload"></i>
                                                    </button>
                                                    <span id="fileNames" class="span_info">لم يتم اختيار ملفات بعد</span>
                                                    <span class="span_info">الامتدادات المسموحة: jpg,png,jpeg,webp. الحجم الأقصى للملف 4MB</span>
                                                </div>

                                                <img width="100px" style="margin-top: 10px;border-radius: 5px" src="{{asset('assets/uploads/services/'.$service['image'])}}">


                                            </div>

                                        </div>

                                    </div>
                                </div>
                                <button type="submit" class="btn btn-save"
                                        id="submitBtn">   تعديل الخدمة <i class="fa fa-save"></i></button>
                                <span id="loader"
                                      style="display: none;">جاري الإرسال...</span>
                        </form>

                            <script>
                                document.getElementById('uploadService').addEventListener('submit', function (event) {
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
                                    font-size:14px
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
            var selectedCategoryId = $('#mainCategory').val();
            if (selectedCategoryId) {
                loadSubCategories(selectedCategoryId, "{{ $service['sub_cat_id'] }}");
            }

            $('#mainCategory').on('change', function() {
                var categoryId = $(this).val();
                if (categoryId) {
                    loadSubCategories(categoryId, null);
                } else {
                    $('#subCategory').empty();
                    $('#subCategory').append('<option> -- حدد القسم الفرعي --</option>');
                }
            });

            function loadSubCategories(categoryId, selectedSubCatId) {
                $.ajax({

                    url: 'http://127.0.0.1:8000/admin/service/get-subcategories/' + categoryId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        $('#subCategory').empty();
                        $('#subCategory').append('<option> -- حدد القسم الفرعي --</option>');
                        $.each(data, function(key, value) {
                            var isSelected = (selectedSubCatId && selectedSubCatId == value.id) ? 'selected' : '';
                            $('#subCategory').append('<option value="' + value.id + '" ' + isSelected + '>' + value.name + '</option>');
                        });
                    }
                });
            }
        });
    </script>

@endsection
