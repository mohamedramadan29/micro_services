@extends('admin.layouts.master')

@section('title', 'تفاصيل رأي العميل')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">تفاصيل رأي العميل</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-left">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.reviews.index') }}">آراء العملاء</a></li>
                        <li class="breadcrumb-item active">تفاصيل رأي العميل</li>
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
                            <h3 class="card-title">بيانات رأي العميل</h3>
                            <div class="float-left">
                                <a href="{{ route('admin.reviews.edit', $review) }}" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i> تعديل
                                </a>
                                <a href="{{ route('admin.reviews.index') }}" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-arrow-left"></i> رجوع
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 text-center">
                                    @if($review->client_image)
                                        <img src="{{ asset($review->client_image) }}" alt="{{ $review->client_name }}"
                                             class="img-fluid img-thumbnail" style="max-width: 200px; height: 200px; object-fit: cover;">
                                    @else
                                        <img src="{{ asset('assets/admin/dist/img/user2-160x160.jpg') }}"
                                             alt="default" class="img-fluid img-thumbnail" style="max-width: 200px; height: 200px;">
                                    @endif
                                </div>
                                <div class="col-md-9">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th style="width: 150px;">اسم العميل</th>
                                            <td>{{ $review->client_name }}</td>
                                        </tr>
                                        <tr>
                                            <th>المنصب</th>
                                            <td>{{ $review->client_position ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>الشركة</th>
                                            <td>{{ $review->client_company ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>التقييم</th>
                                            <td>{!! $review->rating_stars !!}</td>
                                        </tr>
                                        <tr>
                                            <th>الحالة</th>
                                            <td>
                                                @if($review->is_active)
                                                    <span class="badge badge-success">نشط</span>
                                                @else
                                                    <span class="badge badge-danger">غير نشط</span>
                                                @endif

                                                @if($review->is_approved)
                                                    <span class="badge badge-info mr-2">موافق عليه</span>
                                                @else
                                                    <span class="badge badge-secondary mr-2">غير موافق عليه</span>
                                                @endif

                                                @if($review->is_featured)
                                                    <span class="badge badge-warning">مميز</span>
                                                @else
                                                    <span class="badge badge-secondary">عادي</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>الترتيب</th>
                                            <td>{{ $review->sort_order }}</td>
                                        </tr>
                                        <tr>
                                            <th>تاريخ الإضافة</th>
                                            <td>{{ $review->created_at->format('Y-m-d H:i:s') }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <div class="mt-4">
                                <h5>رأي العميل</h5>
                                <div class="card">
                                    <div class="card-body">
                                        <p class="mb-0">{{ $review->content }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
