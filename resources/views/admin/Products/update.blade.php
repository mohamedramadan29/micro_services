@extends('admin.layouts.master')
@section('title')
    تعديل المنتج
@endsection
@section('css')
@endsection
@section('content')
    <div class="page-content">
        <!-- Start Container Fluid -->
        <div class="container-xxl">
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
            <form method="post" action="{{ url('admin/product/update/' . $product['slug']) }}" enctype="multipart/form-data">
                @csrf
                <div class="row">

                    <div class="col-xl-12 col-lg-8 ">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title"> تعديل المنتج </h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-6 col-12">
                                        <div class="mb-3">
                                            <label for="name" class="form-label"> اسم المنتج </label>
                                            <input required type="text" id="name" name="name"
                                                class="form-control" placeholder="" value="{{ $product['name'] }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-12">
                                        <div class="mb-3">
                                            <label for="name" class="form-label"> اضف اسم خاص للرابط ( اختياري )
                                            </label>
                                            <input required type="text" id="slug" name="slug"
                                                class="form-control" placeholder="" value="{{ $product['slug'] }}">
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="status" class="form-label"> حالة المنتج </label>
                                            <select class="form-control" id="status" data-choices data-choices-groups
                                                data-placeholder="Select Categories" name="status">
                                                <option value=""> -- حدد حالة المنتج --</option>
                                                <option @if ($product['status'] == 1) selected @endif value="1">
                                                    مفعل
                                                </option>
                                                <option @if ($product['status'] == 0) selected @endif value="0">
                                                    ارشيف
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="mb-3">
                                            <label for="short_description" class="form-label"> وصف مختصر عن
                                                المنتج </label>
                                            <textarea class="form-control bg-light-subtle" id="short_description" rows="5" placeholder=""
                                                name="short_description">{{ $product['short_description'] }}</textarea>
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="mb-3">
                                            <label for="description" class="form-label"> وصف المنتج </label>
                                            <textarea required class="form-control bg-light-subtle tinymce" id="description" rows="7" placeholder=""
                                                name="description">{{ $product['description'] }}</textarea>
                                        </div>
                                    </div>
                                </div>


                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title"> مرفقات المنتج </h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="image" class="form-label"> صورة المنتج </label>
                                            <input type="file" id="image" name="image" class="form-control"
                                                accept="image/*">
                                            <br>
                                            <img width="80px" class="img-thumbnail img-prod"
                                                src="{{ asset('assets/uploads/product_images/' . $product['image']) }}"
                                                alt="">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="gallery" class="form-label"> اضافة صور للمعرض </label>
                                            <input type="file" multiple id="gallery" name="gallery[]"
                                                class="form-control" accept="image/*">
                                        </div>

                                    </div>

                                </div>
                            </div>
                        </div>
                        <div class="card" id="simple-product-fields">
                            <div class="card-header">
                                <h4 class="card-title"> تفاصيل السعر </h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <label for="product-price" class="form-label"> سعر البيع </label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text fs-20"><i class='bx bx-dollar'></i></span>
                                            <input step="0.01" type="number" id="price" name="price"
                                                class="form-control" placeholder="000" value="{{ $product['price'] }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <label for="product-discount" class="form-label"> الخصم علي المنتج  </label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text fs-20"><i class='bx bx-dollar'></i></span>
                                            <input step="0.01" type="number" id="discount" name="discount"
                                                class="form-control" placeholder="000" value="{{ $product['discount'] }}">
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>


                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title"> معلومات السيو ومحركات البحث </h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="meta_title" class="form-label"> العنوان </label>
                                            <input type="text" id="meta_title" name="meta_title" class="form-control"
                                                placeholder="" value="{{ $product['meta_title'] }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="meta_keywords" class="form-label"> الكلمات المفتاحية </label>
                                            <input type="text" id="meta_keywords" name="meta_keywords"
                                                class="form-control" placeholder=""
                                                value="{{ $product['meta_keywords'] }}">
                                        </div>
                                    </div>

                                    <div class="col-lg-12">
                                        <div class="mb-3">
                                            <label for="meta_description" class="form-label"> الوصف </label>
                                            <textarea class="form-control bg-light-subtle" id="meta_description" rows="7" placeholder=""
                                                name="meta_description">{{ $product['meta_description'] }}</textarea>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <div class="p-3 bg-light mb-3 rounded">
                            <div class="row justify-content-end g-2">
                                <div class="col-lg-2">
                                    <a href="{{ url('admin/products') }}" class="btn btn-primary w-100"> رجوع </a>
                                </div>
                                <div class="col-lg-2">
                                    <button type="submit" class="btn btn-outline-secondary w-100"> حفظ <i
                                            class='bx bxs-save'></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            <div class="card">
                <div class="card-header">
                    معرض المنتج
                </div>
                <div class="card-body">
                    @if (count($gallaries) > 0)
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th> الصورة</th>
                                    <th> العمليات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($gallaries as $gallary)
                                    <tr>
                                        <td><img width="80px" class="img-thumbnail img-prod"
                                                src="{{ asset('assets/uploads/product_gallery/' . $gallary['image']) }}"
                                                alt=""></td>
                                        <td>
                                            <button data-target="#delete_gallary_{{ $gallary['id'] }}"
                                                data-toggle="modal" class="btn btn-danger btn-sm"> حذف <i
                                                    class="fa fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @include('admin.Products.delete_gallary')
                                @endforeach
                            </tbody>
                        </table>
                    @endif

                </div>
            </div>
        </div>
        <!-- End Container Fluid -->

    </div>

@endsection


@section('js')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        tinymce.init({
            selector: '.tinymce',
            height: 300,
            directionality: 'rtl', // لجعل المحرر يعمل من اليمين إلى اليسار
            language: 'ar',
            plugins: [
                'advlist', 'autolink', 'link', 'image', 'lists', 'charmap', 'preview', 'anchor', 'pagebreak',
                'searchreplace', 'wordcount', 'visualblocks', 'visualchars', 'code', 'fullscreen',
                'insertdatetime',
                'media', 'table', 'emoticons', 'help'
            ],
            toolbar: 'undo redo | styles | bold italic | alignleft aligncenter alignright alignjustify | ' +
                'bullist numlist outdent indent | link image | print preview media fullscreen | ' +
                'forecolor backcolor emoticons',
            menu: {
                favs: {
                    title: 'My Favorites',
                    items: 'code visualaid | searchreplace | emoticons'
                }
            },
            image_title: true, // السماح بتعديل العنوان
            automatic_uploads: true,
            images_upload_url: 'post_uploads', // مسار API لاستقبال الصور
            file_picker_types: 'image',
            file_picker_callback: function(cb, value, meta) {
                if (meta.filetype === 'image') {
                    var input = document.createElement('input');
                    input.setAttribute('type', 'file');
                    input.setAttribute('accept', 'image/*');
                    input.onchange = function() {
                        var file = this.files[0];
                        var reader = new FileReader();
                        reader.onload = function() {
                            cb(reader.result, {
                                title: file.name
                            });
                        };
                        reader.readAsDataURL(file);
                    };
                    input.click();
                }
            }
        });
    </script>
@endsection
