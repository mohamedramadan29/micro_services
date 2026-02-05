@extends('admin.layouts.master')
@section('title')
تعديل العمل
@endsection
@section('blog-active', 'active')
@section('blog-collapse', 'show')
@section('css')
@endsection
@section('content')
<!-- ==================================================== -->
<div class="page-content">
    <!-- Start Container Fluid -->
    <div class="container-xxl">
        <form method="post" action="{{ url('admin/nafizha-portfolio/update/'.$portfolio['id']) }}"
            enctype="multipart/form-data">
            @csrf

            <div class="row">
                <div class="col-xl-12 col-lg-12">
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
                    <div class="card" style="background-color: #F2F2F8">
                        <div class="card-header">
                            <h4 class="card-title"> تعديل العمل </h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-6 col-12">
                                    <div class="mb-3">
                                        <label for="name" class="form-label"> عنوان العمل <span class="star"
                                                style="color: red"> * </span>
                                        </label>
                                        <input type="text" class="form-control with-light" name="title" required
                                            value="{{ old('title', $portfolio->title) }}">
                                    </div>
                                </div>
                                <div class="mt-2 col-lg-12">
                                    <div class="mb-3">
                                        <label for="description" class="form-label"> قم بوصف المشروع <span class="star"
                                                style="color: red"> * </span></label>
                                        <textarea class="form-control bg-light-subtle" id="description" rows="7"
                                            placeholder=""
                                            name="description">{{ old('description', $portfolio->description) }}</textarea>
                                    </div>
                                </div>
                                <div>
                                    <div class="form-group mb-4">
                                        <label>الصورة الرئيسية <span class="text-danger">*</span></label>
                                        <input type="file" name="image" accept="image/*" class="form-control">
                                        <small class="text-muted d-block mt-2">الامتدادات: jpg, png, jpeg, webp | الحجم الأقصى: 4MB</small>

                                        @if($portfolio->image)
                                            <div class="mt-3">
                                                <p class="mb-1">الصورة الحالية:</p>
                                                <img src="{{ asset('assets/uploads/portfolios/' . $portfolio['image']) }}" class="img-thumbnail" style="max-height: 200px;">
                                            </div>
                                        @endif
                                    </div>

                                    <div class="form-group mb-4">
                                        <label>صور إضافية للعمل (اختياري)</label>
                                        <input type="file" name="additional_images[]" multiple accept="image/*" class="form-control">
                                        <small class="text-muted d-block mt-2">الامتدادات: jpg, png, jpeg, webp | الحجم الأقصى لكل ملف: 4MB</small>

                                        @if($portfolio->more_images && count($portfolio->more_images) > 0)
                                            <div class="mt-3">
                                                <p class="mb-1">الصور الإضافية الحالية:</p>
                                                <div class="row">
                                                    @foreach($portfolio->more_images as $img)
                                                        <div class="col-md-3 mb-2">
                                                            <img src="{{ asset('assets/uploads/portfolios/' . $img) }}" class="img-thumbnail" style="max-height: 150px;">
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-xl-12 col-lg-12">
                                    <div class="form-group">
                                        <label> مهارات متعلقة بالمشروع </label>
                                        @php
                                        // استخراج المهارات من قاعدة البيانات وتقسيمها إلى مصفوفة
                                        $skills = explode(',', $portfolio['tools']);
                                        @endphp
                                        <select required class="form-control select2" multiple name="skills[]">
                                            <option disabled>-- حدد من القائمة --</option>
                                            @foreach ($sub_categories as $sub_category)
                                            <option value="{{ $sub_category->id }}" {{ in_array($sub_category->id,
                                                $skills) ? 'selected' : '' }}>
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
                                        <label> القسم الرئيسي للعمل </label>
                                        <select required class="form-control form-select" name="category">
                                            <option disabled>-- حدد من القائمة --</option>
                                            @foreach ($categories as $category)
                                            <option value="{{ $category->id }}" {{ old('category', $portfolio->
                                                category_id) == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                            @endforeach

                                        </select>


                                    </div>
                                </div>

                                <div class="col-xl-12 col-lg-12">
                                    <div class="form-group">
                                        <label> رابط العمل ( اختياري ) </label>
                                        <input type="text" class="form-control with-light" name="link"
                                            value="{{ old('link', $portfolio->link) }}">
                                    </div>
                                </div>

                                <div class="col-lg-6 col-12">
                                    <div class="mb-3">
                                        <label for="status" class="form-label"> حالة المشروع </label>
                                        <select required class="form-control" id="status" data-choices
                                            data-choices-groups data-placeholder="Select Categories" name="status">
                                            <option value=""> -- حدد حالة الكورس --</option>
                                            <option value="0" @selected($portfolio->status == 0)> ارشيف </option>
                                            <option value="1" @selected($portfolio->status == 1)> نشط </option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="p-3 mb-3 rounded">
                        <div class="row justify-content-start g-2">
                            <div class="col-lg-3">
                                <button type="submit" class="btn btn-primary" style="background-color: blue"> حفظ <i
                                        class='bx bxs-save'></i></button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </form>
    </div>
    <!-- End Container Fluid -->


    <!-- ==================================================== -->
    <!-- End Page Content -->
    <!-- ==================================================== -->
    @endsection

    @section('js')
    <script>
        // دالة لتحديث الحقل المخفي
            function updateHiddenKeywords() {
                let keywords = [];
                document.querySelectorAll('#keywordList .badge').forEach(badge => {
                    keywords.push(badge.getAttribute('data-keyword'));
                });
                document.getElementById('hidden_keywords').value = keywords.join(',');
            }

            // دالة لإزالة الكلمة وتحديث الحقل المخفي
            function removeKeyword(element) {
                element.parentElement.remove();
                updateHiddenKeywords();
            }

            // إضافة كلمة عند الضغط على Enter
            document.getElementById('meta_keywords').addEventListener('keypress', function(e) {
                if (e.key === 'Enter' && this.value.trim()) {
                    e.preventDefault();
                    let keyword = this.value.trim();
                    let keywordList = document.getElementById('keywordList');
                    let badge = document.createElement('span');
                    badge.className = 'mb-2 text-white badge bg-primary me-2';
                    badge.setAttribute('data-keyword', keyword);
                    badge.innerHTML =
                        `${keyword} <span class="ms-2 text-danger" onclick="removeKeyword(this)">×</span>`;
                    keywordList.appendChild(badge);
                    this.value = '';
                    updateHiddenKeywords();
                }
            });
    </script>
    <script>
        // دالة لتحويل النص إلى slug
            function toSlug(text) {
                return text
                    .toLowerCase()
                    .trim()
                    .replace(/[\s+]/g, '-') // استبدال المسافات بـ -
                    .replace(/[^\w\-]+/g, '') // إزالة الرموز غير المرغوب فيها
                    .replace(/\-\-+/g, '-'); // إزالة الـ - المكررة
            }

            // عرض معاينة الرابط أثناء الكتابة
            document.getElementById('meta_url').addEventListener('input', function() {
                let input = this.value;
                let slug = toSlug(input);
                document.getElementById('slugPreview').textContent = slug || 'عنوان-المنتج';
                document.getElementById('meta_url_final').value = slug; // تحديث الحقل المخفي
            });
    </script>
    @endsection
