@extends('admin.layouts.master')
@section('title')
    صفحات الهبوط
@endsection
@section('content')
    <div class="breadcrumb-header justify-content-between">
        <div class="my-auto">
            <div class="d-flex">
                <h4 class="content-title mb-0 my-auto">صفحات الهبوط</h4>
                <span class="text-muted mt-1 tx-13 mr-2 mb-0">/ صفحات مستقلة للحملات والإعلانات، كل محتواها من هنا</span>
            </div>
        </div>
    </div>
    @include('admin.catalog.partials.flash')

    <div class="row row-sm">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><h6 class="mb-0">صفحة جديدة</h6></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.landing.store') }}">
                        @csrf
                        <div class="form-group">
                            <label>اسم الصفحة (يظهر في عنوان المتصفح)</label>
                            <input type="text" name="title" class="form-control" required maxlength="255" value="{{ old('title') }}">
                        </div>
                        <div class="form-group">
                            <label>الرابط</label>
                            <div class="input-group" dir="ltr">
                                <div class="input-group-prepend"><span class="input-group-text">{{ url('lp') }}/</span></div>
                                <input type="text" name="slug" class="form-control" required maxlength="80" placeholder="start" value="{{ old('slug') }}">
                            </div>
                            <small class="text-muted">حروف إنجليزية صغيرة وأرقام وشرطات.</small>
                        </div>
                        <div class="form-group">
                            <label>البداية</label>
                            <select name="template" class="form-control">
                                <option value="starter">من التصميم المقترح (واجهة + 3 مسارات + 4 خطوات + دعوة)</option>
                                <option value="blank">صفحة فارغة</option>
                            </select>
                        </div>
                        <button class="btn btn-primary btn-block">إنشاء كمسودة</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>الصفحة</th><th>الرابط</th><th>الحالة</th><th>آخر تعديل</th><th></th></tr></thead>
                        <tbody>
                            @forelse ($pages as $page)
                                <tr>
                                    <td><strong>{{ $page->title }}</strong></td>
                                    <td dir="ltr" class="text-right"><small>/lp/{{ $page->slug }}</small></td>
                                    <td>
                                        @if ($page->is_published)<span class="badge badge-success">منشورة</span>@else<span class="badge badge-secondary">غير منشورة</span>@endif
                                        @if ($page->hasDraft())<span class="badge badge-warning">تعديلات غير منشورة</span>@endif
                                    </td>
                                    <td><small>{{ $page->updated_at->format('Y-m-d H:i') }}</small></td>
                                    <td class="text-nowrap">
                                        <a href="{{ route('admin.landing.edit', $page) }}" class="btn btn-sm btn-primary">تعديل</a>
                                        <a href="{{ $page->url() }}?preview=1" target="_blank" class="btn btn-sm btn-light">معاينة</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted">لا توجد صفحات بعد. ابدأ من التصميم المقترح.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
