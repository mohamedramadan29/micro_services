@extends('admin.layouts.master')
@section('title')
    خدمات صفحة الخدمات
@endsection
@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
@endsection
@section('content')
    @include('admin.catalog.partials.head', ['title' => 'الخدمات', 'subtitle' => 'كل تغيير يظهر تلقائيًا في صفحة الخدمات والبحث وعدد الخدمات'])

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0">قائمة الخدمات الحالية ({{ $services->count() }})</h6>
            <a href="{{ route('admin.catalog.services.create', array_filter(['group' => request('group')])) }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> إضافة خدمة جديدة</a>
        </div>
        <div class="card-body">
            <form method="GET" class="form-inline mb-3" style="gap: 8px">
                <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="ابحث باسم الخدمة">
                <select name="group" class="form-control">
                    <option value="">كل المجموعات</option>
                    @foreach ($groups as $group)
                        <option value="{{ $group->id }}" @selected((int) request('group') === $group->id)>{{ $group->name }}</option>
                    @endforeach
                </select>
                <select name="visible" class="form-control">
                    <option value="">الكل</option>
                    <option value="1" @selected(request('visible') === '1')>منشورة</option>
                    <option value="0" @selected(request('visible') === '0')>مخفية</option>
                </select>
                <button class="btn btn-primary">تصفية</button>
                <a href="{{ route('admin.catalog.services') }}" class="btn btn-light">مسح</a>
            </form>
            <div class="table-responsive">
                <table class="table table-bordered mb-0">
                    <thead>
                        <tr><th>الخدمة</th><th>المجموعة</th><th>الترتيب</th><th>الظهور</th><th>إجراء</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($services as $service)
                            <tr>
                                <td>
                                    @if ($service->imageUrl())<img src="{{ $service->imageUrl() }}" alt="" width="28" height="28" style="object-fit: cover; border-radius: 6px"> @elseif ($service->icon)<i class="bi {{ $service->icon }}"></i> @endif<strong>{{ $service->name }}</strong>
                                    @if ($service->description)<div class="text-muted small">{{ $service->description }}</div>@endif
                                    @if (! $service->link && ! isset($withForm[$service->id]))
                                        <div class="small text-warning"><i class="fas fa-exclamation-triangle"></i> لا نموذج طلب منشور ولا رابط: «أطلب الخدمة» يحوّل العميل إلى «ساعدوني أختار». <a href="{{ route('admin.request-forms.index') }}">أنشئ نموذجًا</a> أو ضع رابطًا.</div>
                                    @endif
                                </td>
                                <td>{{ $service->category?->name ?? '—' }}</td>
                                <td>{{ $service->sort_order }}</td>
                                <td>{!! $service->status ? '<span class="badge badge-success">منشور</span>' : '<span class="badge badge-secondary">مخفي</span>' !!}</td>
                                <td class="text-nowrap">
                                    <a href="{{ route('admin.catalog.services.edit', $service) }}" class="btn btn-sm btn-light">تعديل</a>
                                    <form method="POST" action="{{ route('admin.catalog.services.destroy', $service) }}" class="d-inline" onsubmit="return confirm('حذف الخدمة نهائيًا؟ إن كانت مستخدمة سنطلب منك إخفاءها بدلًا من الحذف.')">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-danger">حذف</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted">لا توجد خدمات مطابقة.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
