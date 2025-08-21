@extends('website.layouts.master')
@section('title')
    اضافة عقار جديد
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
                                        <li class="breadcrumb-item active" aria-current="page"> اضافة عقار </li>
                                    </ol>
                                </nav>
                            </div>
                        </div>
                    </div>
                    <div class="row mobile_form">
                        <form method="post" action="{{ url('my/property/add') }}" enctype="multipart/form-data"
                            id="uploadService">
                            @csrf
                            <div class="col-lg-12 col-md-12 col-sm-12">
                                <!-- Single Wrap -->
                                <div class="_dashboard_content">
                                    <div class="_dashboard_content_header">
                                        <div class="_dashboard__header_flex">
                                            <h4><i class="ti-briefcase mr-1"></i> اضافة تفاصيل العقار </h4>
                                        </div>
                                    </div>

                                    <div class="_dashboard_content_body">
                                        <div class="row">
                                            <div class="col-xl-12 col-lg-12">
                                                <div class="form-group">
                                                    <label> ادخل العنوان </label>
                                                    <input type="text" class="form-control with-light" name="title"
                                                        required value="{{ old('title') }}" placeholder=" عقار جديد  ">
                                                </div>
                                            </div>

                                            <div class="col-xl-12 col-lg-12">
                                                <div class="form-group">
                                                    <label> الوصف </label>
                                                    <textarea class="form-control with-light" required name="description">{{ old('description') }}</textarea>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="form-group">
                                                    <label> نوع العقار </label>
                                                    <select name="type" id="" class="form-select" required>
                                                        <option value="" selected disabled> -- حدد نوع العقار --
                                                        </option>
                                                        <option {{ old('type') == 'بيع' ? 'selected' : '' }} value="بيع">
                                                            بيع</option>
                                                        <option {{ old('type') == 'ايجار' ? 'selected' : '' }}
                                                            value="ايجار">ايجار</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-6">
                                                <div class="form-group">
                                                    <label> حدد القسم </label>
                                                    <select name="category" id="" class="form-select" required>
                                                        <option value="" selected disabled> -- حدد القسم -- </option>
                                                        <option {{ old('category') == 'شقة' ? 'selected' : '' }}
                                                            value="شقة"> شقة </option>
                                                        <option {{ old('category') == 'فيلا' ? 'selected' : '' }}
                                                            value="فيلا">فيلا</option>
                                                        <option {{ old('category') == 'أرض' ? 'selected' : '' }}
                                                            value="أرض">أرض</option>
                                                        <option {{ old('category') == 'محل تجاري' ? 'selected' : '' }}
                                                            value="محل تجاري">محل تجاري</option>
                                                        <option {{ old('category') == 'بيت عربي' ? 'selected' : '' }}
                                                            value="بيت عربي">بيت عربي</option>
                                                        <option {{ old('category') == 'مكتب' ? 'selected' : '' }}
                                                            value="مكتب">مكتب</option>
                                                        <option {{ old('category') == 'مخزن' ? 'selected' : '' }}
                                                            value="مخزن">مخزن</option>
                                                        <option value="مزارع"
                                                            {{ old('category') == 'مزارع' ? 'selected' : '' }}
                                                            value="مزارع">مزارع</option>
                                                        <option {{ old('category') == 'اخرى' ? 'selected' : '' }}
                                                            value="اخرى">اخرى</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="form-group">
                                                    <label> السعر </label>
                                                    <input type="text" id="formatted-price" value="{{ old('price') }}"
                                                        class="form-control with-light" required>
                                                    <input type="hidden" id="actual-price" name="price"
                                                        value="{{ old('price') }}">
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="form-group">
                                                    <label> حدد العملة </label>
                                                    <select name="currency" id="" class="form-select" required>
                                                        <option value="" selected disabled> -- حدد العملة -- </option>
                                                        <option {{ old('currency') == 'دولار أمريكي' ? 'selected' : '' }}
                                                            value="دولار أمريكي"> دولار أمريكي </option>
                                                        <option {{ old('currency') == 'ريال سعودي' ? 'selected' : '' }}
                                                            value="ريال سعودي"> ريال سعودي </option>
                                                        <option {{ old('currency') == 'دينار أردني' ? 'selected' : '' }}
                                                            value="دينار أردني"> دينار أردني </option>
                                                        <option {{ old('currency') == 'يورو' ? 'selected' : '' }}
                                                            value="يورو"> يورو </option>
                                                        <option {{ old('currency') == 'ليرة لبنانية' ? 'selected' : '' }}
                                                            value="ليرة لبنانية"> ليرة لبنانية </option>
                                                        <option {{ old('currency') == 'جنيه مصري' ? 'selected' : '' }}
                                                            value="جنيه مصري"> جنيه مصري </option>
                                                        <option {{ old('currency') == 'دينار كويتي' ? 'selected' : '' }}
                                                            value="دينار كويتي"> دينار كويتي </option>
                                                        <option {{ old('currency') == 'درهم إماراتي' ? 'selected' : '' }}
                                                            value="درهم إماراتي"> درهم إماراتي </option>
                                                        <option {{ old('currency') == 'ليرة سورية' ? 'selected' : '' }}
                                                            value="ليرة سورية"> ليرة سورية </option>
                                                    </select>
                                                </div>
                                            </div>

                                            <!-- تحميل AutoNumeric.js -->
                                            <script src="https://cdn.jsdelivr.net/npm/autonumeric@4.5.4"></script>

                                            <script>
                                                document.addEventListener("DOMContentLoaded", function() {
                                                    const formattedInput = new AutoNumeric('#formatted-price', {
                                                        digitGroupSeparator: ',', // الفاصلة بين الأرقام
                                                        decimalCharacter: '.', // الفاصلة العشرية
                                                        decimalPlaces: 2, // عدد الأرقام بعد الفاصلة العشرية
                                                        unformatOnSubmit: true // إزالة الفواصل عند الإرسال
                                                    });

                                                    // عند التغيير، نقوم بتحديث الحقل المخفي بالقيمة الفعلية
                                                    document.getElementById('formatted-price').addEventListener('input', function() {
                                                        document.getElementById('actual-price').value = formattedInput.getNumericString();
                                                    });
                                                });
                                            </script>


                                            <div class="col-6">
                                                <div class="form-group">
                                                    <label> المساحة <span class="badge badge-danger"> م٢ </span> </label>
                                                    <input type="number" class="form-control with-light" name="area"
                                                        required value="{{ old('area') }}" step="0.01">
                                                </div>
                                            </div>


                                            <div class="col-6">
                                                <div class="form-group">
                                                    <label> عدد الغرف </label>
                                                    <input type="number" class="form-control with-light" name="rooms"
                                                        required value="{{ old('rooms') }}">
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="form-group">
                                                    <label> عدد الحمامات </label>
                                                    <input type="number" class="form-control with-light"
                                                        name="bathrooms" required value="{{ old('bathrooms') }}">
                                                </div>
                                            </div>
                                            <div class="col-xl-12 col-lg-12">
                                                <div class="form-group">
                                                    <label> مميزات العقار <span class="badge badge-danger"> افصل بين كل
                                                            ميزة والاخري ب (,) </span> </label>
                                                    <textarea class="form-control with-light" name="features">{{ old('features') }}</textarea>
                                                </div>
                                            </div>

                                            <div class="col-xl-12 col-lg-12">
                                                <div class="form-group">
                                                    <label> حدد موقع العقار بالتفصيل </label>
                                                    <input type="text" class="form-control with-light" name="location"
                                                        required value="{{ old('location') }}">
                                                </div>
                                            </div>
                                            <div class="col-xl-6 col-lg-6">
                                                <div class="form-group">
                                                    <label> المدينة </label>
                                                    <input type="text" class="form-control with-light" name="city"
                                                        required value="{{ old('city') }}">
                                                </div>
                                            </div>
                                            <div class="col-xl-6 col-lg-6">
                                                <div class="form-group">
                                                    <label> الدولة </label>
                                                    <input type="text" class="form-control with-light" name="country"
                                                        required value="{{ old('country') }}">
                                                </div>
                                            </div>
                                            <div class="col-xl-12 col-lg-12">
                                                <div class="form-group">
                                                    <label>ادخل صور العقار (يمكنك مشاهدة الصور قبل الرفع)</label>
                                                    <input type="file" id="imageInput" class="form-control with-light"
                                                        multiple name="images[]" accept="image/*">
                                                </div>
                                                <div id="imagePreview" class="d-flex flex-wrap mt-3"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <button style="margin: auto;display: block" type="submit" class="btn btn-save"
                                        id="submitBtn"> اضافة العقار <i class="fa fa-save"></i></button>
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

@section('js')
    <script>
        document.getElementById('imageInput').addEventListener('change', function(event) {
            let imagePreview = document.getElementById('imagePreview');

            Array.from(event.target.files).forEach(file => {
                let reader = new FileReader();
                reader.onload = function(e) {
                    let imgContainer = document.createElement("div");
                    imgContainer.classList.add("position-relative", "m-2");

                    let img = document.createElement("img");
                    img.src = e.target.result;
                    img.classList.add("rounded", "shadow", "border", "p-1");
                    img.style.width = "120px";
                    img.style.height = "120px";

                    let removeBtn = document.createElement("span");
                    removeBtn.innerHTML = "&times;";
                    removeBtn.classList.add("position-absolute", "top-0", "end-0", "bg-danger",
                        "text-white", "rounded-circle", "p-1", "cursor-pointer");
                    removeBtn.style.cursor = "pointer";

                    removeBtn.onclick = function() {
                        imgContainer.remove();
                    };

                    imgContainer.appendChild(img);
                    imgContainer.appendChild(removeBtn);
                    imagePreview.appendChild(imgContainer);
                };
                reader.readAsDataURL(file);
            });
        });
    </script>
@endsection
