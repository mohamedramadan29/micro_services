@extends('admin.layouts.master')
@section('title')
    مجموعات الخدمات
@endsection
@section('content')
    @include('admin.catalog.partials.head', ['title' => 'مجموعات الخدمات', 'subtitle' => 'تظهر كبطاقات في صفحة الخدمات بهذا الترتيب'])

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0">المجموعات ({{ $groups->count() }})</h6>
            <a href="{{ route('admin.catalog.groups.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> إضافة مجموعة</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered mb-0">
                    <thead>
                        <tr><th>الترتيب</th><th>المجموعة</th><th>الوصف</th><th>الخدمات</th><th>الظهور</th><th></th></tr>
                    </thead>
                    <tbody>
                        @forelse ($groups as $group)
                            <tr>
                                <td>{{ $group->sort_order }}</td>
                                <td>@if ($group->icon)<i class="bi {{ $group->icon }}"></i> @endif<strong>{{ $group->name }}</strong></td>
                                <td>{{ $group->description }}</td>
                                <td><a href="{{ route('admin.catalog.services', ['group' => $group->id]) }}">{{ $group->sub_categories_count }} خدمة</a></td>
                                <td>{!! $group->status ? '<span class="badge badge-success">منشورة</span>' : '<span class="badge badge-secondary">مخفية</span>' !!}</td>
                                <td class="text-nowrap">
                                    <a href="{{ route('admin.catalog.groups.edit', $group) }}" class="btn btn-sm btn-light">تعديل</a>
                                    @if (! $group->sub_categories_count)
                                        <form method="POST" action="{{ route('admin.catalog.groups.destroy', $group) }}" class="d-inline" onsubmit="return confirm('حذف المجموعة؟')">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-danger">حذف</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">لا توجد مجموعات بعد.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
@endsection
