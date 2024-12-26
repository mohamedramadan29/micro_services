@extends('admin.layouts.master')
@section('title')
    اضافة استشاري جديد
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="breadcrumb-header justify-content-between">
        <div class="my-auto">
            <div class="d-flex">
                <h4 class="content-title mb-0 my-auto">الرئيسية </h4><span class="text-muted mt-1 tx-13 mr-2 mb-0">/ اضافة
                    استشاري جديد </span>
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
                    <div class="mb-4 main-content-label"> اضافة استشاري جديد </div>
                    <form class="form-horizontal" method="post" action="{{ url('admin/consultant/add') }}"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group ">
                                    <div class="row">

                                        <div class="col-md-3">
                                            <label class="form-label"> حدد التخصص </label>
                                        </div>
                                        <div class="col-md-9">
                                            <select required class="form-control select2" name="specialization"
                                                id="specialization">
                                                <option> -- حدد التخصص --</option>
                                                @foreach ($categories as $category)
                                                    <option @if (old('specialization') == $category['id']) selected @endif
                                                        value="{{ $category['id'] }}"> {{ $category['name'] }} </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group ">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label class="form-label"> الاسم </label>
                                        </div>
                                        <div class="col-md-9">
                                            <input required type="text" class="form-control" name="name"
                                                value="{{ old('name') }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label class="form-label"> البريد الالكتروني </label>
                                        </div>
                                        <div class="col-md-9">
                                            <input required type="email" class="form-control" name="email"
                                                value="{{ old('email') }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group ">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label class="form-label"> السعر </label>
                                        </div>
                                        <div class="col-md-9">
                                            <input required type="number" class="form-control" name="price"
                                                value="{{ old('price') }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label class="form-label"> صورة الاستشاري </label>
                                        </div>
                                        <div class="col-md-9">
                                            <input required type="file" class="form-control" name="image">
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="col-lg-6">
                                <div class="form-group ">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label class="form-label"> نبذة مختصرة عن الاستشاري </label>
                                        </div>
                                        <div class="col-md-9">
                                            <textarea cols="" rows="" class="form-control" required name="bio">{{ old('bio') }}</textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label class="form-label"> حالة الاستشاري </label>
                                        </div>
                                        <div class="col-md-9">
                                            <select required class="form-control select" name="is_active">
                                                <option> -- حدد --</option>
                                                <option value="1" selected> متاح </option>
                                                <option value="0"> غير متاح </option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="card-footer text-left">
                            <button type="submit" class="btn btn-primary waves-effect waves-light"> اضافة الاستشاري
                            </button>
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
