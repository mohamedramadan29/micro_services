@extends('admin.layouts.master')
@section('title')
    تفاصيل الكورس
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="breadcrumb-header justify-content-between">
        <div class="my-auto">
            <div class="d-flex">
                <h4 class="content-title mb-0 my-auto">الرئيسية </h4><span class="text-muted mt-1 tx-13 mr-2 mb-0">/ اضافة
                    خدمة جديدة </span>
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
                    <div class="mb-4 main-content-label"> تفاصيل الكورس </div>
                    <form class="form-horizontal" method="post" action="{{ url('admin/course/update/'.$course->id) }}"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-group ">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label class="form-label"> عنوان الكورس </label>
                                        </div>
                                        <div class="col-md-9">
                                            <input disabled readonly type="text" class="form-control" name="name"
                                                value="{{ $course->title }}">
                                        </div>

                                    </div>
                                </div>

                                <div class="form-group ">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label class="form-label"> وصف الكورس  </label>
                                        </div>
                                        <div class="col-md-9">
                                            <textarea disabled readonly cols="" rows="10" class="form-control" required name="description"> {{ $course->desc }} </textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group ">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label class="form-label"> مميزات الكورس   </label>
                                        </div>
                                        <div class="col-md-9">
                                            <textarea disabled readonly cols="" rows="10" class="form-control" required name="description"> {{ $course->adv }} </textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label class="form-label">ماذا سوف تتعلم  </label>
                                        </div>
                                        <div class="col-md-9">
                                            <textarea disabled readonly cols="" rows="10" class="form-control" required name="description"> {{ $course->learn }} </textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group ">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label class="form-label"> هل هناك شروط لدخول الكورس   </label>
                                        </div>
                                        <div class="col-md-9">
                                            <textarea disabled readonly cols="" rows="10" class="form-control" required name="description"> {{ $course->terms_course }} </textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label class="form-label">  عدد ساعات الكورس  </label>
                                        </div>
                                        <div class="col-md-9">
                                            <input disabled readonly required type="number" class="form-control" name="price"
                                                value="{{ $course->course_hourse}}">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group ">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label class="form-label">  عدد المحاضرات  </label>
                                        </div>
                                        <div class="col-md-9">
                                            <input disabled readonly required type="number" class="form-control" name="price"
                                                value="{{ $course->leason_numbers }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group ">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label class="form-label">   عدد الطلبة  </label>
                                        </div>
                                        <div class="col-md-9">
                                            <input disabled readonly required type="number" class="form-control" name="price"
                                                value="{{ $course->student_num }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group ">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label class="form-label"> سعر الاشتراك </label>
                                        </div>
                                        <div class="col-md-9">
                                            <input disabled readonly required type="number" step="0.01" class="form-control" name="price"
                                                value="{{ $course->price }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group ">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label class="form-label"> تعديل حالة الكورس  </label>
                                        </div>
                                        <div class="col-md-9">
                                           <select name="status" id="" class="form-select form-control">
                                            <option value=""> -- حدد حالة الكورس  -- </option>
                                            <option value="1" @if($course->status == 1) selected @endif> فعال </option>
                                            <option value="0" @if($course->status == 0) selected @endif> غير فعال </option>
                                           </select>
                                        </div>
                                    </div>
                                </div>


                            </div>

                        </div>

                        <div class="card-footer text-left">
                            <button type="submit" class="btn btn-primary waves-effect waves-light"> تعديل حالة الكورس
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
