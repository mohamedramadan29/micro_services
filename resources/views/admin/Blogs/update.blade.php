@extends('admin.layouts.master')
@section('title')
    تعديل المقال
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
            <form method="post" action="{{ url('admin/blog/update/' . $blog['id']) }}" enctype="multipart/form-data">
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
                                <h4 class="card-title"> تعديل المقال </h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-3 col-12">
                                        <div class="mb-3">
                                            <label for="name" class="form-label"> عنوان المقال <span class="star"
                                                    style="color: red"> * </span>
                                            </label>
                                            <input required type="text" id="name" class="form-control"
                                                name="name" value="{{ $blog['name'] }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-12">
                                        <div class="mb-3">
                                            <label for="category_id" class="form-label"> حدد القسم </label>
                                            <select required class="form-control" id="category_id" data-choices
                                                data-choices-groups data-placeholder="Select Categories" name="category_id">
                                                <option value=""> -- حدد القسم --</option>
                                                @foreach ($categories as $category)
                                                    <option {{ $category['id'] == $blog['category_id'] ? 'selected' : '' }}
                                                        value="{{ $category['id'] }}">{{ $category['name'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-12">
                                        <div class="mb-3">
                                            <label for="publish_date" class="form-label"> جدولة نشر المقال <span
                                                    class="star" style="color: red"> * </span>
                                            </label>
                                            <input required type="date" id="publish_date" class="form-control"
                                                name="publish_date" value="{{ $blog['publish_date'] }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-12">
                                        <div class="mb-3">
                                            <label for="status" class="form-label"> حالة المقال </label>
                                            <select required class="form-control" id="status" data-choices
                                                data-choices-groups data-placeholder="Select Categories" name="status">
                                                <option value=""> -- حدد القسم --</option>
                                                <option value="0" @selected($blog['status'] == 0)> ارشيف </option>
                                                <option value="1" @selected($blog['status'] == 1)> نشط </option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="bg-white col-lg-4 custome_image">
                                        <div class="form-group">
                                            <input type="file" class="form-control" name="image"
                                                id="single-image-edit">
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="mb-3">
                                            <label for="desc" class="form-label"> وصف المقال </label>
                                            <textarea class="form-control bg-light-subtle tinymce" id="desc" rows="7" placeholder="" name="desc">{!! $blog['desc'] !!}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card" style="background-color:#F2F2F8">
                            <div class="card-header">
                                <h4 class="card-title">تحسينات السيو ( SEO )</h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="mb-3">
                                            <label for="meta_title" class="form-label">عنوان صفحة المنتج (Page
                                                Title)</label>
                                            <input type="text" id="meta_title" class="form-control" name="meta_title"
                                                placeholder="ادخل العنوان هنا" value="{{ $blog['meta_title'] }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="mb-3">
                                            <label for="meta_url" class="form-label">رابط صفحة المنتج (SEO PAGE
                                                URL)</label>
                                            <input type="text" id="meta_url" class="form-control" name="meta_url"
                                                placeholder="أدخل رابط المنتج" value="{{ $blog['meta_url'] }}">
                                            <!-- حقل مخفي لتخزين الرابط النهائي -->
                                            <input type="hidden" name="meta_url_final" id="meta_url_final"
                                                value="{{ $blog['meta_url'] }}">
                                            <!-- معاينة الرابط -->
                                            <div class="mt-2">
                                                <small class="text-muted">معاينة الرابط: </small>
                                                <span id="urlPreview" class="text-primary">{{ url('/blog/') }}/<span
                                                        id="slugPreview">{{ $blog['meta_url'] }}</span></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="mb-3">
                                            <label for="meta_description" class="form-label">وصف صفحة المنتج (Page
                                                Description)</label>
                                            <textarea class="form-control" id="meta_description" rows="7" name="meta_description"
                                                placeholder="وصف صفحة المنتج">{{ $blog['meta_desc'] }}</textarea>
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="mb-3">
                                            <label for="meta_keywords" class="form-label">الكلمات المفتاحية</label>
                                            <div class="input-group">
                                                <input type="text" id="meta_keywords" class="form-control"
                                                    placeholder="أدخل الكلمات المفتاحية">
                                                <!-- حقل مخفي لتخزين الكلمات -->
                                                <input type="hidden" name="meta_keywords" id="hidden_keywords"
                                                    value="{{ $blog['meta_keywords'] }}">
                                            </div>
                                            <div id="keywordList" class="mt-2">
                                                @if ($blog['meta_keywords'])
                                                    @foreach (explode(',', $blog['meta_keywords']) as $keyword)
                                                        <span class="mb-2 text-white badge bg-primary me-2"
                                                            data-keyword="{{ $keyword }}">
                                                            {{ $keyword }} <span class="ms-2 text-danger"
                                                                onclick="removeKeyword(this)">×</span>
                                                        </span>
                                                    @endforeach
                                                @endif
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


        <!-- Start file Input  -->
        <script>
            var lang = "{{ app()->getLocale() }}";
            $("#single-image-edit").fileinput({
                theme: 'bs5',
                allowedFileTypes: ['image'],
                language: lang,
                maxFileCount: 1,
                enableResumableUpload: false,
                showUpload: false,
                initialPreviewAsData: true,
                initialPreview: [
                    "{{ asset($blog->Image()) }}"
                ],
            });
        </script>
    @endsection
