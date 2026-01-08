@extends('admin.layouts.master')
@section('title')
تعديل الباقة
@endsection
@section('content')
<!-- breadcrumb -->
<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto">الرئيسية </h4><span class="text-muted mt-1 tx-13 mr-2 mb-0">/ تعديل
                الباقة</span>
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
                <div class="mb-4 main-content-label"> تعديل الباقة </div>
                <form class="form-horizontal" method="post" action="{{ url('admin/package/update/'.$package->id) }}"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group ">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="form-label"> عنوان الباقة </label>
                                    </div>
                                    <div class="col-md-9">
                                        <input required type="text" class="form-control" name="title"
                                            value="{{ $package->name }}">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group ">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="form-label"> تصنيف الباقة </label>
                                    </div>
                                    <div class="col-md-9">
                                        <select name="category" id="" class="form-control">
                                            <option value=""> -- حدد التصنيف -- </option>
                                            @foreach ($titles as $title )
                                            <option @selected($title->title == $package->title) value="{{ $title->title
                                                }}">{{ $title->title }}</option>
                                            @endforeach
                                        </select>
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
                                            value="{{ old('price',$package->price) }}">
                                    </div>
                                </div>
                            </div>


                        </div>

                        <div class="col-lg-6">
                            <div class="form-group ">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="form-label"> محتوي الباقة (افصل بين كل نفطة والاخري ب ) (,)
                                        </label>
                                    </div>
                                    <div class="col-md-9">
                                        <textarea cols="" rows="10" class="form-control" required
                                            name="description">{{ old('description',$package->description) }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="card-footer text-left">
                        <button type="submit" class="btn btn-primary waves-effect waves-light"> تعديل الباقة
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
