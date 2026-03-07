@extends('admin.layouts.master')

@section('title', 'إضافة رأي جديد')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">إضافة رأي جديد</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-left">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.reviews.index') }}">آراء العملاء</a></li>
                        <li class="breadcrumb-item active">إضافة رأي جديد</li>
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
                            <a href="{{ route('admin.reviews.index') }}" class="btn btn-secondary btn-sm float-left">
                                <i class="fas fa-arrow-left"></i> رجوع
                            </a>
                        </div>
                        <form action="{{ route('admin.reviews.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="client_name">اسم العميل <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="client_name" name="client_name"
                                                   value="{{ old('client_name') }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="client_position">المنصب</label>
                                            <input type="text" class="form-control" id="client_position" name="client_position"
                                                   value="{{ old('client_position') }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="client_company">الشركة</label>
                                            <input type="text" class="form-control" id="client_company" name="client_company"
                                                   value="{{ old('client_company') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="client_image">صورة العميل</label>
                                            <input type="file" class="form-control" id="client_image" name="client_image"
                                                   accept="image/*">
                                            <small class="text-muted">صورة مربعة مقاس 150x150 بكسل</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="rating">التقييم <span class="text-danger">*</span></label>
                                            <select class="form-control" id="rating" name="rating" required>
                                                <option value="">اختر التقييم</option>
                                                <option value="5" {{ old('rating') == '5' ? 'selected' : '' }}>⭐⭐⭐⭐⭐ (5)</option>
                                                <option value="4" {{ old('rating') == '4' ? 'selected' : '' }}>⭐⭐⭐⭐ (4)</option>
                                                <option value="3" {{ old('rating') == '3' ? 'selected' : '' }}>⭐⭐⭐ (3)</option>
                                                <option value="2" {{ old('rating') == '2' ? 'selected' : '' }}>⭐⭐ (2)</option>
                                                <option value="1" {{ old('rating') == '1' ? 'selected' : '' }}>⭐ (1)</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="sort_order">الترتيب</label>
                                            <input type="number" class="form-control" id="sort_order" name="sort_order"
                                                   value="{{ old('sort_order', 0) }}" min="0">
                                            <small class="text-muted">القيمة الأقل تظهر أولاً</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="content">رأي العميل <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="content" name="content" rows="4" required>{{ old('content') }}</textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input" id="is_active" name="is_active"
                                                       value="1" {{ old('is_active', '1') ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="is_active">نشط</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input" id="is_approved" name="is_approved"
                                                       value="1" {{ old('is_approved', '1') ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="is_approved">موافق عليه</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input" id="is_featured" name="is_featured"
                                                       value="1" {{ old('is_featured') ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="is_featured">مميز</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> حفظ
                                </button>
                                <a href="{{ route('admin.reviews.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> إلغاء
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
