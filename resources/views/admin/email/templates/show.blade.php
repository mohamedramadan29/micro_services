@extends('admin.layouts.master')

@section('title', $emailTemplate->name)
@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ $emailTemplate->name }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-left">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.email.templates.index') }}">القوالب</a></li>
                        <li class="breadcrumb-item active">عرض</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">تفاصيل القالب</h3>
                    <div class="float-left">
                        <a href="{{ route('admin.email.templates.edit', $emailTemplate) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> تعديل
                        </a>
                        <a href="{{ route('admin.email.templates.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> رجوع
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th style="width:200px;">اسم القالب</th>
                            <td>{{ $emailTemplate->name }}</td>
                        </tr>
                        <tr>
                            <th>عنوان الإيميل</th>
                            <td>{{ $emailTemplate->subject }}</td>
                        </tr>
                        <tr>
                            <th>المتغيرات</th>
                            <td>
                                @if($emailTemplate->variables)
                                    @foreach($emailTemplate->variables as $var)
                                        <span class="badge badge-info">{{ $var }}</span>
                                    @endforeach
                                @else
                                    <span class="text-muted">لا يوجد</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>الحالة</th>
                            <td>
                                @if($emailTemplate->is_active)
                                    <span class="badge badge-success">نشط</span>
                                @else
                                    <span class="badge badge-danger">غير نشط</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>تاريخ الإنشاء</th>
                            <td>{{ $emailTemplate->created_at->format('Y-m-d H:i') }}</td>
                        </tr>
                    </table>

                    <hr>
                    <h5>معاينة الإيميل</h5>
                    <div class="border p-3 mt-3" style="background:#f9f9f9; min-height:200px;">
                        {!! $emailTemplate->body !!}
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
