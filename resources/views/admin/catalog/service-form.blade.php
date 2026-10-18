@extends('admin.layouts.master')
@section('title')
    {{ $service->exists ? 'تعديل خدمة' : 'إضافة خدمة جديدة' }}
@endsection
@section('content')
    @include('admin.catalog.partials.head', ['title' => $service->exists ? 'تعديل خدمة' : 'إضافة خدمة جديدة', 'subtitle' => 'بيانات الخدمة'])

    <div class="card">
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data" action="{{ $service->exists ? route('admin.catalog.services.update', $service) : route('admin.catalog.services.store') }}">
                @csrf
                <div class="row">
                    <div class="form-group col-md-6">
                        <label>اسم الخدمة <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required maxlength="255" value="{{ old('name', $service->name) }}" placeholder="مثال: تصميم الهوية البصرية">
                    </div>
                    <div class="form-group col-md-6">
                        <label>وصف مختصر</label>
                        <input type="text" name="description" class="form-control" maxlength="500" value="{{ old('description', $service->description) }}" placeholder="اكتب وصفاً يظهر في بطاقة الخدمة">
                    </div>
                    <div class="form-group col-md-6">
                        <label>المجموعة <span class="text-danger">*</span></label>
                        <select name="parent_id" class="form-control">
                            <option value="">— اختار مجموعة —</option>
                            @foreach ($groups as $group)
                                <option value="{{ $group->id }}" @selected((int) old('parent_id', $service->parent_id) === $group->id)>{{ $group->name }}</option>
                            @endforeach
                        </select>
                        <input type="text" name="new_group" class="form-control mt-2" maxlength="255" value="{{ old('new_group') }}" placeholder="أو أنشئ مجموعة جديدة بهذا الاسم">
                    </div>
                    <div class="form-group col-md-3">
                        <label>الأيقونة</label>
                        <input type="text" name="icon" class="form-control" dir="ltr" value="{{ old('icon', $service->icon) }}" placeholder="bi-brush">
                        <small class="text-muted"><a href="https://icons.getbootstrap.com" target="_blank" rel="noopener">Bootstrap Icons</a></small>
                    </div>
                    <div class="form-group col-md-3">
                        <label>أو صورة</label>
                        <input type="file" name="image" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                        @if ($service->image)
                            <label class="mt-1"><input type="checkbox" name="remove_image" value="1"> إزالة الصورة الحالية</label>
                        @endif
                    </div>
                    <div class="form-group col-md-6">
                        <label>رابط الخدمة</label>
                        <input type="text" name="link" class="form-control" dir="ltr" maxlength="500" value="{{ old('link', $service->link) }}" placeholder="اتركه فارغًا ليفتح نموذج طلب هذه الخدمة">
                        <small class="text-muted">صفحة التفاصيل أو مسار الطلب. فارغ = نموذج «اطلب عرضًا» لهذه الخدمة.</small>
                    </div>
                    <div class="form-group col-md-3">
                        <label>نص زر الطلب</label>
                        <input type="text" name="button_text" class="form-control" maxlength="100" value="{{ old('button_text', $service->button_text) }}" placeholder="مثال: أطلب الخدمة">
                    </div>
                    <div class="form-group col-md-1">
                        <label>الترتيب</label>
                        <input type="number" name="sort_order" min="0" class="form-control" value="{{ old('sort_order', $service->sort_order) }}">
                    </div>
                    <div class="form-group col-md-2">
                        <label class="d-block">الظهور</label>
                        <label><input type="checkbox" name="status" value="1" @checked(old('status', $service->status))> منشورة</label>
                    </div>
                </div>
                @if ($service->exists && ($service->serviceForm?->status !== \App\Models\ServiceForm::STATUS_PUBLISHED))
                    <div class="alert alert-warning">لا يوجد نموذج طلب منشور لهذه الخدمة بعد، فزر «أطلب الخدمة» سيعرض للعميل أنها غير متاحة. <a href="{{ route('admin.request-forms.index') }}">نماذج الخدمات</a></div>
                @endif
                <button class="btn btn-success">حفظ ونشر</button>
                <a href="{{ route('admin.catalog.services', ['group' => $service->parent_id]) }}" class="btn btn-light">رجوع</a>
            </form>
        </div>
    </div>
@endsection
