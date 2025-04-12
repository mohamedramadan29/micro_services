@extends('admin.layouts.master')
@section('title')
    تفاصيل الوظيفة
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="breadcrumb-header justify-content-between">
        <div class="my-auto">
            <div class="d-flex">
                <h4 class="content-title mb-0 my-auto">الرئيسية </h4><span class="text-muted mt-1 tx-13 mr-2 mb-0">/ تفاصيل
                    الوظيفة </span>
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
                    <div class="mb-4 main-content-label"> تفاصيل الوظيفة </div>
                    <form class="form-horizontal" method="post" action="{{ url('admin/job/update/' . $job->id) }}"
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
                                                value="{{ $job->User->name }}">
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
                                                value="{{ $job->title }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group ">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label class="form-label"> القسم </label>
                                        </div>
                                        <div class="col-md-9">
                                            @foreach ($categories as $category)
                                                @if ($category->id == $job->category_id)
                                                    <input disabled readonly type="text" class="form-control"
                                                        name="name" value="{{ $category->name }}">
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group ">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label class="form-label"> الوصف </label>
                                        </div>
                                        <div class="col-md-9">
                                            <textarea disabled readonly cols="" rows="10" class="form-control" required name="description"> {{ $job->description }} </textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group ">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label class="form-label"> الخبرات المطلوبة </label>
                                        </div>
                                        <div class="col-md-9">
                                            <textarea disabled readonly cols="" rows="10" class="form-control" required name="description"> {{ $job->experience }} </textarea>
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
                                                value="{{ $job->address }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label class="form-label"> المدينة </label>
                                        </div>
                                        <div class="col-md-9">
                                            <input disabled readonly type="text" class="form-control" name="name"
                                                value="{{ $job->city }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label class="form-label"> الدولة </label>
                                        </div>
                                        <div class="col-md-9">
                                            <input disabled readonly type="text" class="form-control" name="name"
                                                value="{{ $job->country }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group ">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label class="form-label"> المرتب <span class="badge badge-danger"> $ </span>
                                            </label>
                                        </div>
                                        <div class="col-md-9">
                                            <input disabled readonly type="text" class="form-control" name="name"
                                                value="{{ $job->salary }}">
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
