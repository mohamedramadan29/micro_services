@extends('admin.layouts.master')
@section('title')
    {{ $group->exists ? 'تعديل مجموعة' : 'إضافة مجموعة' }}
@endsection
@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
@endsection
@section('content')
    @include('admin.catalog.partials.head', ['title' => $group->exists ? 'تعديل مجموعة' : 'إضافة مجموعة', 'subtitle' => 'مجموعات الخدمات'])

    <div class="card">
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data" action="{{ $group->exists ? route('admin.catalog.groups.update', $group) : route('admin.catalog.groups.store') }}">
                @csrf
                <div class="row">
                    <div class="form-group col-md-6">
                        <label>اسم المجموعة <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required maxlength="255" value="{{ old('name', $group->name) }}">
                    </div>
                    <div class="form-group col-md-6">
                        <label>وصف مختصر (يظهر تحت الاسم)</label>
                        <input type="text" name="description" class="form-control" maxlength="500" value="{{ old('description', $group->description) }}">
                    </div>
                    <div class="form-group col-md-4">
                        <label>الأيقونة</label>
                        <input type="text" name="icon" class="form-control" dir="ltr" value="{{ old('icon', $group->icon) }}" placeholder="bi-palette">
                        <small class="text-muted">اسم أيقونة من <a href="https://icons.getbootstrap.com" target="_blank" rel="noopener">Bootstrap Icons</a>، أو ارفع صورة.</small>
                    </div>
                    <div class="form-group col-md-4">
                        <label>أو صورة (تُقصّ مربعة تلقائيًا)</label>
                        <input type="file" name="image" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                        @if ($group->image)
                            <label class="mt-1"><input type="checkbox" name="remove_image" value="1"> إزالة الصورة الحالية</label>
                        @endif
                    </div>
                    <div class="form-group col-md-2">
                        <label>الترتيب</label>
                        <input type="number" name="sort_order" min="0" class="form-control" value="{{ old('sort_order', $group->sort_order) }}">
                    </div>
                    <div class="form-group col-md-2">
                        <label class="d-block">الظهور</label>
                        <label><input type="checkbox" name="status" value="1" @checked(old('status', $group->status))> منشورة</label>
                    </div>
                </div>
                <button class="btn btn-success">حفظ ونشر</button>
                <a href="{{ route('admin.catalog.groups') }}" class="btn btn-light">رجوع</a>
            </form>
        </div>
    </div>
@endsection
