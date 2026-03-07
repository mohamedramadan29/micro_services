@extends('admin.layouts.master')

@section('title', 'آراء العملاء')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">آراء العملاء</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-left">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item active">آراء العملاء</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">قائمة آراء العملاء</h3>
                            <a href="{{ route('admin.reviews.create') }}" class="btn btn-primary btn-sm float-left">
                                <i class="fas fa-plus"></i> إضافة رأي جديد
                            </a>
                        </div>
                        <div class="card-body">
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>صورة العميل</th>
                                        <th>اسم العميل</th>
                                        <th>المنصب</th>
                                        <th>الشركة</th>
                                        <th>التقييم</th>
                                        <th>الحالة</th>
                                        <th>مميز</th>
                                        <th>الترتيب</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($reviews as $index => $review)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            @if($review->client_image)
                                                <img src="{{ asset('assets/uploads/reviews/' . $review->client_image) }}" alt="{{ $review->client_name }}"
                                                     class="img-circle" style="width: 40px; height: 40px; object-fit: cover;">
                                            @else
                                                <img src="{{ asset('assets/website/img/user-default.png') }}"
                                                     alt="default" class="img-circle" style="width: 40px; height: 40px;">
                                            @endif
                                        </td>
                                        <td>{{ $review->client_name }}</td>
                                        <td>{{ $review->client_position ?? '-' }}</td>
                                        <td>{{ $review->client_company ?? '-' }}</td>
                                        <td>{!! $review->rating_stars !!}</td>
                                        <td>
                                            @if($review->is_active)
                                                <span class="badge badge-success">نشط</span>
                                            @else
                                                <span class="badge badge-danger">غير نشط</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($review->is_featured)
                                                <span class="badge badge-warning">مميز</span>
                                            @else
                                                <span class="badge badge-secondary">عادي</span>
                                            @endif
                                        </td>
                                        <td>{{ $review->sort_order }}</td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="{{ route('admin.reviews.show', $review) }}" class="btn btn-sm btn-info" title="عرض">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.reviews.edit', $review) }}" class="btn btn-sm btn-warning" title="تعديل">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button class="btn btn-sm {{ $review->is_active ? 'btn-secondary' : 'btn-success' }} toggle-status"
                                                        data-id="{{ $review->id }}" title="تبديل الحالة">
                                                    <i class="fas {{ $review->is_active ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                                                </button>
                                                <button class="btn btn-sm {{ $review->is_approved ? 'btn-secondary' : 'btn-info' }} toggle-approved"
                                                        data-id="{{ $review->id }}" title="تبديل الموافقة">
                                                    <i class="fas {{ $review->is_approved ? 'fa-times' : 'fa-check' }}"></i>
                                                </button>
                                                <button class="btn btn-sm {{ $review->is_featured ? 'btn-secondary' : 'btn-warning' }} toggle-featured"
                                                        data-id="{{ $review->id }}" title="تبديل التميز">
                                                    <i class="fas {{ $review->is_featured ? 'fa-star' : 'fa-star-o' }}"></i>
                                                </button>
                                                <form action="{{ route('admin.reviews.destroy', $review) }}" method="POST" style="display: inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger"
                                                            onclick="return confirm('هل أنت متأكد من حذف هذا الرأي؟')" title="حذف">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@section('js')
<script>
$(document).ready(function() {
    // Toggle status
    $('.toggle-status').click(function() {
        var id = $(this).data('id');
        var btn = $(this);

        $.ajax({
            url: '/admin/reviews/' + id + '/toggle-status',
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                }
            }
        });
    });

    // Toggle approved
    $('.toggle-approved').click(function() {
        var id = $(this).data('id');
        var btn = $(this);

        $.ajax({
            url: '/admin/reviews/' + id + '/toggle-approved',
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                }
            }
        });
    });

    // Toggle featured
    $('.toggle-featured').click(function() {
        var id = $(this).data('id');
        var btn = $(this);

        $.ajax({
            url: '/admin/reviews/' + id + '/toggle-featured',
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                }
            }
        });
    });
});
</script>
@endsection
