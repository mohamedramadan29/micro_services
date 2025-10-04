@extends('admin.layouts.master')
@section('title')
    اضافة صفحة كورس جديد
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
            <form method="post" action="{{ route('public-courses.add') }}" enctype="multipart/form-data">
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
                                <h4 class="card-title"> اضافة صفحة كورس جديد </h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-6 col-12">
                                        <div class="mb-3">
                                            <label for="name" class="form-label"> عنوان الكورس <span class="star"
                                                    style="color: red"> * </span>
                                            </label>
                                            <input required type="text" id="name" class="form-control"
                                                name="name" value="{{ old('name') }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-12">
                                        <div class="mb-3">
                                            <label for="status" class="form-label"> حالة الكورس </label>
                                            <select required class="form-control" id="status" data-choices
                                                data-choices-groups data-placeholder="Select Categories" name="status">
                                                <option value=""> -- حدد حالة الكورس --</option>
                                                <option value="0"> ارشيف </option>
                                                <option value="1" selected> نشط </option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="bg-white col-lg-4 custome_image">
                                        <div class="form-group">
                                            <input type="file" class="form-control" name="image" id="single-image"
                                                accept="image/*">
                                        </div>
                                    </div>
                                    <div class="mt-2 col-lg-12">
                                        <div class="mb-3">
                                            <label for="description" class="form-label"> محتوي الكورس <span class="star"
                                                    style="color: red"> * </span></label>
                                            <textarea class="form-control bg-light-subtle tinymce" id="description" rows="7" placeholder=""
                                                name="description">{{ old('description') }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card" style="background-color:#F2F2F8">

                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="mb-3">
                                            <label for="meta_url" class="form-label">رابط صفحة الكورس (SEO PAGE
                                                URL)</label>
                                            <input type="text" id="meta_url" class="form-control" name="meta_url"
                                                placeholder="أدخل رابط الكورس" value="{{ old('meta_url') }}">
                                            <!-- حقل مخفي لتخزين الرابط النهائي -->
                                            <input type="hidden" name="meta_url_final" id="meta_url_final"
                                                value="{{ old('meta_url') }}">
                                            <!-- معاينة الرابط -->
                                            <div class="mt-2">
                                                <small class="text-muted">معاينة الرابط: </small>
                                                <span id="urlPreview"
                                                    class="text-primary">{{ url('/course/public/') }}/<span
                                                        id="slugPreview"></span></span>
                                            </div>
                                        </div>
                                    </div>


                                </div>
                            </div>
                        </div>
                        <div class="p-3 mb-3 rounded bg-light">
                            <div class="row justify-content-start g-2">
                                <div class="col-lg-3">
                                    <button type="submit" class="btn btn-primary w-100"> حفظ <i
                                            class='bx bxs-save'></i></button>
                                </div>
                                <div class="col-lg-3">
                                    <a href="{{ url('admin/blogs') }}" class="btn btn-danger w-100"> الغاء </a>
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
