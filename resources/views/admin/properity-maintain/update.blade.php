@extends('admin.layouts.master')
@section('title')
    تفاصيل الخدمة
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="breadcrumb-header justify-content-between">
        <div class="my-auto">
            <div class="d-flex">
                <h4 class="content-title mb-0 my-auto">الرئيسية </h4><span class="text-muted mt-1 tx-13 mr-2 mb-0">/ تفاصيل
                    الخدمة </span>
            </div>
        </div>
    </div>
    <!-- breadcrumb -->
    <!-- row -->
    <div class="row row-sm">

        <!-- Col -->
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    @if (Session::has('Success_message'))
                        <div class="alert alert-success"> {{ Session::get('Success_message') }} </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <div class="mb-4 main-content-label"> تفاصيل الخدمة </div>
                    <form class="form-horizontal" method="post"
                        action="{{ url('admin/properity-maintain/update/' . $properity->id) }}"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-group ">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label class="form-label"> المستخدم </label>
                                        </div>
                                        <div class="col-md-9">
                                            <input disabled readonly type="text" class="form-control" name="name"
                                                value="{{ $properity->User->name }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label class="form-label"> العنوان </label>
                                        </div>
                                        <div class="col-md-9">
                                            <input disabled readonly type="text" class="form-control" name="name"
                                                value="{{ $properity->title }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group ">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label class="form-label"> الوصف </label>
                                        </div>
                                        <div class="col-md-9">
                                            <textarea disabled readonly cols="" rows="10" class="form-control" required name="description"> {{ $properity->description }} </textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group ">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label class="form-label"> نوع العقار </label>
                                        </div>
                                        <div class="col-md-9">
                                            <input disabled readonly type="text" class="form-control" name="name"
                                                value="{{ $properity->category }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label class="form-label"> نوع العقد </label>
                                        </div>
                                        <div class="col-md-9">
                                            <input disabled readonly type="text" class="form-control" name="name"
                                                value="{{ $properity->contract_type }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label class="form-label"> مساحة العقار </label>
                                        </div>
                                        <div class="col-md-9">
                                            <input disabled readonly required type="number" class="form-control"
                                                name="price" value="{{ $properity->area }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group ">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label class="form-label"> عدد الغرف </label>
                                        </div>
                                        <div class="col-md-9">
                                            <input disabled readonly required type="number" class="form-control"
                                                name="price" value="{{ $properity->rooms }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group ">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label class="form-label"> الخدمات المتاحة </label>
                                        </div>
                                        <div class="col-md-9">
                                            @php
                                                $features = explode(',', $properity->service_type);

                                            @endphp
                                            <textarea disabled readonly cols="" rows="10" class="form-control" required name="description">
@foreach ($features as $feature)
{{ $feature }}
@endforeach
                                                </textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group ">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label class="form-label"> الموقع </label>
                                        </div>
                                        <div class="col-md-9">
                                            <input disabled readonly required type="text" step="0.01"
                                                class="form-control" name="price" value="{{ $properity->location }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group ">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label class="form-label"> المدينة </label>
                                        </div>
                                        <div class="col-md-9">
                                            <input disabled readonly required type="text" step="0.01"
                                                class="form-control" name="price" value="{{ $properity->city }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group ">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label class="form-label"> الدولة </label>
                                        </div>
                                        <div class="col-md-9">
                                            <input disabled readonly required type="text" step="0.01"
                                                class="form-control" name="price" value="{{ $properity->country }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group ">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label class="form-label"> الحالة </label>
                                        </div>
                                        <div class="col-md-9">
                                            @if ($properity->active == 1)
                                                <span class="badge badge-success"> مفعل </span>
                                            @else
                                                <span class="badge badge-danger"> غير مفعل </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                    </form>
                </div>

            </div>
        </div>
        <!-- /Col -->
    </div>
    <!-- row closed -->
    </div>
    <!-- Container closed -->
    </div>
    <!-- main-content closed -->
@endsection
