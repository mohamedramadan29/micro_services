@extends('admin.layouts.master')
@section('title')
    اضافة خدمة جديدة
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="breadcrumb-header justify-content-between">
        <div class="my-auto">
            <div class="d-flex">
                <h4 class="content-title mb-0 my-auto">الرئيسية </h4><span class="text-muted mt-1 tx-13 mr-2 mb-0">/   اضافة خدمة جديدة   </span>
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
                    @if(Session::has('Success_message'))
                        <div
                            class="alert alert-success"> {{Session::get('Success_message')}} </div>
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
                    <div class="mb-4 main-content-label"> اضافة خدمة جديدة</div>
                    <form class="form-horizontal" method="post" action="{{url('admin/service/add')}}"
                          enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group ">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label class="form-label"> القسم الرئيسي </label>
                                        </div>
                                        <div class="col-md-9">
                                            <select required class="form-control select2" name="cat_id"
                                                    id="mainCategory">
                                                <option> -- حدد القسم الرئيسي --</option>
                                                @foreach($categories as $category)
                                                    <option value="{{$category['id']}}"> {{$category['name']}} </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group ">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label class="form-label"> حدد القسم الفرعي </label>
                                        </div>
                                        <div class="col-md-9">
                                            <select required class="form-control select2" name="sub_cat_id"
                                                    id="subCategory">
                                                <option> -- حدد القسم الفرعي --</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
                                <script>
                                    $(document).ready(function () {
                                        $('#mainCategory').on('change', function () {
                                            var categoryId = $(this).val();
                                            if (categoryId) {
                                                $.ajax({
                                                    url: 'http://127.0.0.1:8000/admin/service/get-subcategories/' + categoryId,
                                                    type: 'GET',
                                                    dataType: 'json',
                                                    success: function (data) {
                                                        $('#subCategory').empty();
                                                        $('#subCategory').append('<option> -- حدد القسم الفرعي --</option>');
                                                        $.each(data, function (key, value) {
                                                            $('#subCategory').append('<option value="' + value.id + '">' + value.name + '</option>');
                                                        });
                                                    }
                                                });
                                            } else {
                                                $('#subCategory').empty();
                                                $('#subCategory').append('<option> -- حدد القسم الفرعي --</option>');
                                            }
                                        });
                                    });
                                </script>


                                <div class="form-group ">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label class="form-label"> المستخدم </label>
                                        </div>
                                        <div class="col-md-9">
                                            <input required type="hidden" class="form-control" name="user_id"
                                                   value="{{Auth::user()->id}}">
                                            <input readonly type="text" class="form-control" name=""
                                                   value="{{Auth::user()->name}}">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label class="form-label"> اسم الخدمة </label>
                                        </div>
                                        <div class="col-md-9">
                                            <input required type="text" class="form-control" name="name"
                                                   value="{{old('name')}}">
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
                                                   value="{{old('price')}}">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label class="form-label"> صورة الخدمة </label>
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
                                            <label class="form-label"> وصف الخدمة </label>
                                        </div>
                                        <div class="col-md-9">
                                            <textarea cols="" rows="10" class="form-control" required
                                                      name="description">{{old('description')}}</textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group ">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label class="form-label"> الكلمات المفتاحية <span
                                                    class="badge badge-danger"> افصل بين كل كلمة والاخري ب (,) </span>
                                            </label>
                                        </div>
                                        <div class="col-md-9">
                                            <input required type="text" class="form-control" name="tags"
                                                   value="{{old('tags')}}">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label class="form-label"> حالة الخدمة </label>
                                        </div>
                                        <div class="col-md-9">
                                            <select required class="form-control select" name="status">
                                                <option> -- حدد --</option>
                                                <option value="1"> فعالة</option>
                                                <option value="0"> غير فعالة</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="card-footer text-left">
                            <button type="submit" class="btn btn-primary waves-effect waves-light"> اضافة خدمة
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

