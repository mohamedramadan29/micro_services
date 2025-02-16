@extends('website.layouts.master')
@section('title')
    تعديل عقار
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
                                        <li class="breadcrumb-item active" aria-current="page">  تعديل عقار </li>
                                    </ol>
                                </nav>
                            </div>
                        </div>
                    </div>
                    <div class="row mobile_form">
                        <form method="post" action="{{ url('my/property/update/'.$property['id']) }}" enctype="multipart/form-data"
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
                                                        required value="{{  $property['title'] ?? old('title')  }}" placeholder=" عقار جديد  ">
                                                </div>
                                            </div>

                                            <div class="col-xl-12 col-lg-12">
                                                <div class="form-group">
                                                    <label> الوصف </label>
                                                    <textarea class="form-control with-light" required name="description">{{ $property['description'] ?? old('description') }}</textarea>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="form-group">
                                                    <label> نوع العقار </label>
                                                    <select name="type" id="" class="form-select" required>
                                                        <option value="" selected disabled> -- حدد نوع العقار --
                                                        </option>
                                                        <option {{ $property['type'] ?? old('type') == 'بيع' ? 'selected' : '' }} value="بيع">بيع</option>
                                                        <option {{ $property['type'] ?? old('type') == 'ايجار' ? 'selected' : '' }} value="ايجار">ايجار</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-6">
                                                <div class="form-group">
                                                    <label> حدد القسم </label>
                                                    <select name="category" id="" class="form-select" required>
                                                        <option value="" selected disabled> -- حدد القسم -- </option>
                                                        <option {{ $property['category'] ?? old('category') == 'شقة' ? 'selected' : '' }} value="شقة"> شقة </option>
                                                        <option {{ $property['category'] ?? old('category') == 'فيلا' ? 'selected' : '' }} value="فيلا">فيلا</option>
                                                        <option {{ $property['category'] ?? old('category') == 'أرض' ? 'selected' : ''  }} value="أرض">أرض</option>
                                                        <option {{ $property['category'] ?? old('category') == 'محل تجاري' ? 'selected' : ''  }} value="محل تجاري">محل تجاري</option>
                                                        <option {{ $property['category'] ?? old('category') == 'اخري' ? 'selected' : ''   }} value="اخري">اخري</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="form-group">
                                                    <label> السعر </label>
                                                    <input type="number" class="form-control with-light" name="price"
                                                        required value="{{ $property['price'] ?? old('price') }}" step="0.01">
                                                </div>
                                            </div>

                                            <div class="col-6">
                                                <div class="form-group">
                                                    <label> المساحة <span class="badge badge-danger"> م٢ </span> </label>
                                                    <input type="number" class="form-control with-light" name="area"
                                                        required value="{{ $property['area'] ?? old('area') }}" step="0.01">
                                                </div>
                                            </div>


                                            <div class="col-6">
                                                <div class="form-group">
                                                    <label> عدد الغرف </label>
                                                    <input type="number" class="form-control with-light" name="rooms"
                                                        required value="{{ $property['rooms'] ?? old('rooms') }}">
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="form-group">
                                                    <label> عدد الحمامات </label>
                                                    <input type="number" class="form-control with-light"
                                                        name="bathrooms" required value="{{ $property['bathrooms'] ?? old('bathrooms') }}">
                                                </div>
                                            </div>
                                            <div class="col-xl-12 col-lg-12">
                                                <div class="form-group">
                                                    <label> مميزات العقار <span class="badge badge-danger"> افصل بين كل
                                                            ميزة والاخري ب (,) </span> </label>
                                                    <textarea class="form-control with-light" name="features">{{ $property['features'] ?? old('features') }}</textarea>
                                                </div>
                                            </div>

                                            <div class="col-xl-12 col-lg-12">
                                                <div class="form-group">
                                                    <label> حدد موقع العقار بالتفصيل </label>
                                                    <input type="text" class="form-control with-light" name="location"
                                                        required value="{{ $property['location'] ?? old('location') }}">
                                                </div>
                                            </div>
                                            <div class="col-xl-6 col-lg-6">
                                                <div class="form-group">
                                                    <label> المدينة </label>
                                                    <input type="text" class="form-control with-light" name="city"
                                                        required value="{{ $property['city'] ?? old('city') }}">
                                                </div>
                                            </div>
                                            <div class="col-xl-6 col-lg-6">
                                                <div class="form-group">
                                                    <label> الدولة </label>
                                                    <input type="text" class="form-control with-light" name="country"
                                                        required value="{{ $property['country'] ?? old('country') }}">
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
                                        id="submitBtn"> تحديث البيانات  <i class="fa fa-save"></i></button>
                                    <span id="loader" style="display: none;">جاري الإرسال...</span>
                                </div>
                            </div>
                        </form>

                        <div class="card">
                            <div class="card-body">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th> صور العقار </th>
                                            <th> العمليات  </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($property['ProperityImages'] as $image)
                                            <tr>
                                                <td>
                                                    <img width="80px" height="80px" src="{{ asset('assets/uploads/properities/' . $image->image) }}" alt="">
                                                </td>
                                                <td>
                                                    <form action="{{ url('delete-property-image/' . $image->id) }}" method="post">
                                                    @csrf
                                                    <button type="submit" onclick="return confirm('هل تريد حذف هذه الصورة؟')"
                                                        class="btn btn-danger btn-sm text-light"> حذف <i class="bi bi-trash"></i> </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
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
