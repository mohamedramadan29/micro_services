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
            <form method="post" action="{{url('admin/product/update/'.$product['slug'])}}" enctype="multipart/form-data">
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
                                            <input required type="text" id="name" name="name" class="form-control"
                                                   placeholder="" value="{{$product['name']}}">
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-12">
                                        <div class="mb-3">
                                            <label for="name" class="form-label"> اضف اسم خاص للرابط  ( اختياري )  </label>
                                            <input required type="text" id="slug" name="slug" class="form-control"
                                                   placeholder="" value="{{$product['slug']}}">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="category_id" class="form-label"> حدد القسم الرئيسي </label>
                                            <select required class="form-control" id="category_id" data-choices
                                                    data-choices-groups data-placeholder="Select Categories"
                                                    name="category_id">
                                                <option value=""> -- حدد القسم --</option>
                                                @foreach($MainCategories as $maincat)
                                                    <option @if($product['category_id'] == $maincat['id']) selected
                                                            @endif value="{{$maincat['id']}}">{{$maincat['name']}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="sub_category_id" class="form-label"> حدد القسم الفرعي </label>
                                            <select class="form-control" id="sub_category_id"
                                                    data-placeholder="Select Categories"
                                                    name="sub_category_id">
                                                <option value=""> -- حدد القسم الفرعي --</option>
                                                @if($product['sub_category_id'] !='')
                                                    <option selected
                                                            value="{{$product['sub_product_id']}}"> {{$product['Sub_Category']['name']}}</option>
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
                                    <script>
                                        $(document).ready(function () {
                                            $('#category_id').on('change', function () {
                                                var categoryId = $(this).val();
                                                if (categoryId) {
                                                    $.ajax({
                                                        url: '{{ route("get.subcategories") }}', // تأكد من استخدام المسار الصحيح
                                                        type: "GET",
                                                        data: {category_id: categoryId},
                                                        success: function (data) {
                                                            $('#sub_category_id').empty();
                                                            if (data.message) {
                                                                $('#sub_category_id').append('<option value="">' + data.message + '</option>');
                                                            } else {
                                                                $('#sub_category_id').append('<option value=""> -- حدد القسم الفرعي --</option>');
                                                                $.each(data, function (key, value) {
                                                                    $('#sub_category_id').append('<option value="' + key + '">' + value + '</option>');
                                                                });
                                                            }
                                                        }
                                                    });
                                                } else {
                                                    $('#sub_category_id').empty();
                                                    $('#sub_category_id').append('<option value=""> -- حدد القسم الفرعي --</option>');
                                                }
                                            });
                                        });
                                    </script>

                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="brand_id" class="form-label"> العلامة التجارية </label>
                                            <select class="form-control" id="brand_id" data-choices
                                                    data-choices-groups data-placeholder="Select Categories"
                                                    name="brand_id">
                                                <option value=""> -- حدد العلامة التجارية --</option>
                                                @foreach($brands as $brand)
                                                    <option @if($product['brand_id'] == $brand['id']) selected
                                                            @endif value="{{$brand['id']}}">{{$brand['name']}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="status" class="form-label"> حالة المنتج </label>
                                            <select class="form-control" id="status" data-choices
                                                    data-choices-groups data-placeholder="Select Categories"
                                                    name="status">
                                                <option value=""> -- حدد حالة المنتج --</option>
                                                <option @if($product['status'] == 1) selected @endif value="1"> مفعل
                                                </option>
                                                <option @if($product['status'] == 0) selected @endif value="0"> ارشيف
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="mb-3">
                                            <label for="short_description" class="form-label"> وصف مختصر عن
                                                المنتج </label>
                                            <textarea class="form-control bg-light-subtle" id="short_description"
                                                      rows="5"
                                                      placeholder=""
                                                      name="short_description">{{$product['short_description']}}</textarea>
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="mb-3">
                                            <label for="description" class="form-label"> وصف المنتج </label>
                                            <textarea required class="form-control bg-light-subtle" id="description"
                                                      rows="7"
                                                      placeholder=""
                                                      name="description">{{$product['description']}}</textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-4">
                                        <div class="mb-3">
                                            <label for="quantity" class="form-label"> الكمية المتاحة </label>
                                            <input min="1" type="number" id="quantity" name="quantity"
                                                   value="{{$product['quantity']}}"
                                                   class="form-control"
                                                   placeholder="">
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
                                                 src="{{asset('assets/uploads/product_images/'.$product['image'])}}"
                                                 alt="">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="gallery" class="form-label"> اضافة صور للمعرض </label>
                                            <input type="file" multiple id="gallery" name="gallery[]"
                                                   class="form-control"
                                                   accept="image/*">
                                        </div>
                                        @if(count($gallaries) > 0)
                                            <table class="table table-bordered">
                                                <thead>
                                                <tr>
                                                    <th> الصورة</th>
                                                    <th> العمليات</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($gallaries as $gallary)
                                                    <tr>
                                                        <td><img width="80px" class="img-thumbnail img-prod"
                                                                 src="{{asset('assets/uploads/product_gallery/'.$gallary['image'])}}"
                                                                 alt=""></td>
                                                        <td>
                                                            <button type="button" class="btn btn-soft-danger btn-sm"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#delete_gallary_{{$gallary['id']}}">
                                                                <iconify-icon
                                                                    icon="solar:trash-bin-minimalistic-2-broken"
                                                                    class="align-middle fs-18"></iconify-icon>
                                                        </td>
                                                    </tr>
                                                    @include('admin.Products.delete_gallary')
                                                @endforeach
                                                </tbody>
                                            </table>
                                        @endif

                                    </div>
                                    <div class="col-lg-12">
                                        <div class="mb-3">
                                            <label for="video" class="form-label"> اضافة فيديو للمنتج </label>
                                            <input type="file" id="video" name="video" class="form-control"
                                                   accept="video/*">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title"> تحديد نوع المنتج </h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="type">نوع المنتج:</label>
                                            <select class="form-control
                                    " name="type" id="product-type" required>
                                                <option @if($product['type'] == 'بسيط') selected @endif  value="بسيط">
                                                    بسيط
                                                </option>
                                                <option @if($product['type'] == 'متغير') selected @endif  value="متغير">
                                                    متغير
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <!------->
                                <div id="variable-product-fields"
                                     style="display: {{ $product->type == 'متغير' ? 'block' : 'none' }};">
                                    @if($product->type == 'متغير')
                                        <div>
                                            @foreach($variations as $variation)
                                                <input type="hidden" name="variant_id[]" value="{{ $variation->id }}" class="form-control" required>
                                                <div class="variation d-flex align-items-center justify-content-between">
                                                    <!-- عرض السمات الخاصة بالمتغير -->
                                                    @foreach($variation->variationValues as $value)
                                                        <div class="form-group">
                                                            <label>{{ $value->attribute->name }}</label>
                                                            <input type="text" name="variant_attributes[{{ $loop->parent->index }}][{{ $value->attribute_id }}]"
                                                                   value="{{ $value->attribute_value_name }}" class="form-control" required>
                                                        </div>
                                                    @endforeach

                                                    <div class="form-group">
                                                        <label>سعر المتغير</label>
                                                        <input type="number" name="variant_price[]" value="{{ $variation->price }}" class="form-control" required>
                                                    </div>

                                                    <div class="form-group">
                                                        <label>تخفيض المتغير</label>
                                                        <input type="number" name="variant_discount[]" value="{{ $variation->discount }}" class="form-control">
                                                    </div>

                                                    <div class="form-group">
                                                        <label>المخزون</label>
                                                        <input type="number" name="variant_stock[]" value="{{ $variation->stock }}" class="form-control" required>
                                                    </div>

                                                    <div class="form-group">
                                                        <label>صورة المتغير</label>
                                                        <input type="file" name="variant_image[]" class="form-control">
                                                        @if($variation->image)
                                                            <img src="{{ asset($variation->image) }}" alt="صورة المتغير" style="max-width: 100px;">
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                    <div id="attribute-container">
                                        <br>
                                        <!-- العنصر المؤقت الذي لا يمكن حذفه -->
                                        <h4 class="card-title"> تحديث وتعديل متغيرات المنتج  </h4>
                                        <br>
                                        <div class="row d-flex align-items-center" id="attribute-row-template"
                                             style="display: none">
                                            <div class="col-lg-4 col-12">
                                                <div class="mb-3">
                                                    <label for="attribute">اختر السمة:</label>
                                                    <select class="form-control" name="attributes[]">
                                                        <option value=""> -- حدد السمة --</option>
                                                        @foreach($attributes as $attribute)
                                                            <option
                                                                value="{{ $attribute->id }}">{{ $attribute->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-lg-6 col-12">
                                                <div class="mb-3">
                                                    <label for="variations">حدد المتغيرات:</label>
                                                    <input type="text" class="form-control" name="variations[]"
                                                           placeholder="ادخل المتغيرات وافصل بين كل متغير بـ(-)">
                                                </div>
                                            </div>
                                            <div class="col-2 mb-3">
                                                <button style="margin-top: 20px"
                                                        class="btn btn-sm btn-danger delete-attribute"><i
                                                        class="ti ti-x"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                    <button id="add_new_vartion" class="btn btn-primary btn-sm"><i
                                            class="ti ti-plus"></i> اضافة متغير جديد
                                    </button>

                                    <button id="confirm-variations" class="btn btn-success btn-sm">تأكيد المتغيرات
                                    </button>

                                    <br>
                                    <!---- متغيرات المنتج  ----->
                                    <div id="product-variants"></div>
                                </div>


                                <script>
                                    document.getElementById('add_new_vartion').addEventListener('click', function (e) {
                                        e.preventDefault();

                                        // احصل على القالب
                                        let template = document.getElementById('attribute-row-template');

                                        // استنساخ القالب
                                        let clone = template.cloneNode(true);

                                        // إزالة الـ ID من النسخة المستنسخة وإظهارها
                                        clone.removeAttribute('id');
                                        clone.style.display = 'flex';

                                        // إعادة تعيين قيمة حقل variations في النسخة الجديدة
                                        clone.querySelector('input[name="variations[]"]').value = '';

                                        // إضافة النسخة إلى الحاوية
                                        document.getElementById('attribute-container').appendChild(clone);
                                    });

                                    // الاستماع لأحداث النقر على أزرار الحذف
                                    document.getElementById('attribute-container').addEventListener('click', function (e) {
                                        if (e.target.classList.contains('delete-attribute')) {
                                            // تحقق إذا كان هناك عناصر يمكن حذفها
                                            let allRows = document.querySelectorAll('#attribute-container .row');
                                            if (allRows.length > 1) { // تأكد من وجود أكثر من سطر واحد قبل الحذف
                                                e.target.closest('.row').remove();
                                            } else {
                                                alert('لا يمكنك حذف العنصر الأخير.');
                                            }
                                        }
                                    });
                                </script>

                            </div>
                        </div>
                        <div class="card" id="simple-product-fields">
                            <div class="card-header">
                                <h4 class="card-title"> تفاصيل السعر </h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <label for="product-price" class="form-label"> سعر الشراء </label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text fs-20"><i class='bx bx-dollar'></i></span>
                                            <input type="number" id="purches_price" name="purches_price"
                                                   class="form-control"
                                                   placeholder="000" value="{{$product['purches_price']}}">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <label for="product-price" class="form-label"> سعر البيع </label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text fs-20"><i class='bx bx-dollar'></i></span>
                                            <input type="number" id="price" name="price" class="form-control"
                                                   placeholder="000" value="{{$product['price']}}">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <label for="product-discount" class="form-label"> السعر بعدالخصم </label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text fs-20"><i class='bx bxs-discount'></i></span>
                                            <input type="number" id="discount" name="discount" class="form-control"
                                                   placeholder="000" value="{{$product['discount']}}">
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
                                                   placeholder="" value="{{$product['meta_title']}}">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="meta_keywords" class="form-label"> الكلمات المفتاحية </label>
                                            <input type="text" id="meta_keywords" name="meta_keywords"
                                                   class="form-control"
                                                   placeholder="" value="{{$product['meta_keywords']}}">
                                        </div>
                                    </div>

                                    <div class="col-lg-12">
                                        <div class="mb-3">
                                            <label for="meta_description" class="form-label"> الوصف </label>
                                            <textarea class="form-control bg-light-subtle" id="meta_description"
                                                      rows="7"
                                                      placeholder=""
                                                      name="meta_description">{{$product['meta_description']}}</textarea>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>


                        <div class="p-3 bg-light mb-3 rounded">
                            <div class="row justify-content-end g-2">
                                <div class="col-lg-2">
                                    <a href="{{url('admin/products')}}" class="btn btn-primary w-100"> رجوع </a>
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
        </div>
        <!-- End Container Fluid -->

    </div>

@endsection

@section('js')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        document.getElementById('product-type').addEventListener('change', function () {
            if (this.value === 'بسيط') {
                document.getElementById('simple-product-fields').style.display = 'block';
                document.getElementById('variable-product-fields').style.display = 'none';
            } else {
                document.getElementById('simple-product-fields').style.display = 'none';
                document.getElementById('variable-product-fields').style.display = 'block';
            }
        });
    </script>

    <script>
        document.getElementById('confirm-variations').addEventListener('click', function ($e) {
            $e.preventDefault();
            const attributes = document.querySelectorAll('select[name="attributes[]"]');
            const variations = document.querySelectorAll('input[name="variations[]"]');

            let selectedValues = [];

            attributes.forEach((attribute, index) => {
                const selectedAttribute = attribute.value;
                if (selectedAttribute) {
                    const variationValues = variations[index].value.split('-').map(v => v.trim());
                    selectedValues.push(variationValues);
                }
            });

            const productVariants = cartesianProduct(selectedValues);
            let productVariantsHTML = '';

            productVariants.forEach(variant => {
                const variantText = variant.join(' - ');
                const variationInputsHTML = `
            <div class="variant-inputs d-flex align-items-center justify-content-between">
                <div class="form-group">
                    <label>اسم المتغير</label>
                    <input name='variant_new_name[]' class="form-control" type="text" value="${variantText}">
                </div>
                <div class="form-group">
                    <label>سعر المنتج</label>
                    <input placeholder="السعر" class="form-control" type="number" name='variant_new_price[]'>
                </div>
                <div class="form-group">
                    <label>السعر بعد التخفيض</label>
                    <input placeholder="السعر" class="form-control" type="number" name='variant_new_discount[]'>
                </div>
                <div class="form-group">
                    <label>الكمية المتاحة</label>
                    <input placeholder="الكمية" class="form-control" type="number" name='variant_new_stock[]'>
                </div>
                <div class="form-group">
                    <label>صورة المنتج</label>
                    <input type='file' class='form-control' name='variant_new_image[]'>
                </div>
                <div class="form-group">
                    <button style="margin-top: 20px" class="btn btn-sm btn-danger delete-variant"><i class="ti ti-x"></i></button>
                </div>
            </div>
        `;
                productVariantsHTML += variationInputsHTML;
            });

            document.getElementById('product-variants').innerHTML = productVariantsHTML;

            // أضف الاستماع لأزرار الحذف الجديدة
            attachDeleteEventListeners();
        });

        function cartesianProduct(arrays) {
            return arrays.reduce(function (a, b) {
                var result = [];
                a.forEach(function (a) {
                    b.forEach(function (b) {
                        result.push(a.concat([b]));
                    });
                });
                return result;
            }, [[]]);
        }

        function attachDeleteEventListeners() {
            const deleteButtons = document.querySelectorAll('.delete-variant');
            deleteButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const variantRow = this.closest('.variant-inputs');
                    variantRow.remove();
                });
            });
        }

    </script>
@endsection
