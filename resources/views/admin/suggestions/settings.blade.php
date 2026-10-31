@extends('admin.layouts.master')
@section('title')
    إعدادات الاقتراحات
@endsection
@section('content')
    <div class="breadcrumb-header justify-content-between">
        <div class="my-auto">
            <div class="d-flex">
                <h4 class="content-title mb-0 my-auto">الاقتراحات</h4>
                <span class="text-muted mt-1 tx-13 mr-2 mb-0">/ الإعدادات</span>
            </div>
        </div>
        <div class="my-auto">
            <a href="{{ route('admin.suggestions.index') }}" class="btn btn-light"><i class="fas fa-arrow-right"></i> الاقتراحات</a>
        </div>
    </div>
    @include('admin.catalog.partials.flash')

    <div class="row row-sm">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><h6 class="mb-0">أنواع الاقتراحات</h6></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-3">
                            <thead><tr><th>الاسم</th><th style="width: 90px">الترتيب</th><th style="width: 110px">يظهر في النموذج</th><th>الاستخدام</th><th></th></tr></thead>
                            <tbody>
                                @foreach ($categories as $category)
                                    <tr>
                                        <td colspan="3" class="p-1">
                                            <form method="POST" action="{{ route('admin.suggestions.categories.update', $category) }}" class="d-flex align-items-center" style="gap: 6px">
                                                @csrf
                                                <input type="text" name="name" value="{{ $category->name }}" class="form-control" maxlength="80" required aria-label="اسم النوع">
                                                <input type="number" name="sort_order" value="{{ $category->sort_order }}" class="form-control" style="width: 90px" min="0" aria-label="الترتيب">
                                                <label class="mb-0 text-nowrap"><input type="checkbox" name="is_active" value="1" @checked($category->is_active)> يظهر</label>
                                                <button class="btn btn-sm btn-primary">حفظ</button>
                                            </form>
                                        </td>
                                        <td>{{ $category->suggestions_count }} اقتراح</td>
                                        <td>
                                            <form method="POST" action="{{ route('admin.suggestions.categories.destroy', $category) }}" onsubmit="return confirm('حذف هذا النوع؟ إن كان مستخدمًا سيُخفى فقط.')">
                                                @csrf
                                                <button class="btn btn-sm btn-light">{{ $category->suggestions_count ? 'إخفاء' : 'حذف' }}</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <form method="POST" action="{{ route('admin.suggestions.categories.store') }}" class="form-inline" style="gap: 8px">
                        @csrf
                        <input type="text" name="name" class="form-control" maxlength="80" required placeholder="نوع جديد" aria-label="اسم النوع الجديد">
                        <input type="number" name="sort_order" class="form-control" style="width: 90px" min="0" placeholder="الترتيب" aria-label="الترتيب">
                        <input type="hidden" name="is_active" value="1">
                        <button class="btn btn-primary">إضافة نوع</button>
                    </form>
                    @error('name')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><h6 class="mb-0">النصوص والرسائل</h6></div>
                <div class="card-body">
                    <p>تتغيّر من غير تعديل الكود:</p>
                    <ul class="pr-3">
                        <li>عنوان قسم الفوتر ووصفه ونص الزر وإظهاره أو إخفاؤه مؤقتًا: <a href="{{ route('admin.content.index', ['page' => 'footer']) }}">إدارة محتوى الواجهة ← الفوتر</a>.</li>
                        <li>نصوص صفحة «شاركنا اقتراحك» ورسالة النجاح: <a href="{{ route('admin.content.index', ['page' => 'suggestions']) }}">إدارة محتوى الواجهة ← شاركنا اقتراحك</a>.</li>
                        <li>رسائل الاستلام وتحديث الحالة: <a href="{{ route('admin.notification-texts.index') }}">نصوص الإشعارات ← الاقتراحات والتطوير</a>.</li>
                        <li>إرسالها بالبريد أو لا: الإعدادات العامة للموقع ← إشعارات البريد.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection
