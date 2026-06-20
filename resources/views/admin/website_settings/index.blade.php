@extends('admin.layouts.master')
@section('title')
  التعديلات العامة للموقع
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="breadcrumb-header justify-content-between">
        <div class="my-auto">
            <div class="d-flex">
                <h4 class="content-title mb-0 my-auto">الرئيسية </h4><span class="text-muted mt-1 tx-13 mr-2 mb-0">/ التعديلات العامة للموقع  </span>
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
                    <div class="mb-4 main-content-label">   التعديلات العامة للموقع  </div>
                    <form class="form-horizontal" method="post" action="{{url('admin/public_settings/update')}}"
                          enctype="multipart/form-data">
                        @csrf
                        <div class="form-group ">
                            <div class="row">
                                <div class="col-md-3">
                                    <label class="form-label"> لوجو الموقع  </label>
                                </div>
                                <div class="col-md-9">
                                    <input type="file" name="logo" class="form-control" value="">
                                </div>
                            </div>
                        </div>
                        <div class="form-group ">
                            <div class="row">
                                <div class="col-md-3">
                                    <label class="form-label"> اسم الموقع   </label>
                                </div>
                                <div class="col-md-9">
                                    <input type="text" class="form-control" name="website_title" value="{{$setting['website_title']}}">
                                </div>
                            </div>
                        </div>
                        <div class="form-group ">
                            <div class="row">
                                <div class="col-md-3">
                                    <label class="form-label">  وصف الموقع   </label>
                                </div>
                                <div class="col-md-9">
                                    <input type="text" class="form-control" name="website_desc"
                                           value="{{$setting['website_desc']}}">
                                </div>
                            </div>
                        </div>
                        <div class="form-group ">
                            <div class="row">
                                <div class="col-md-3">
                                    <label class="form-label"> اللون الاساسي للموقع  </label>
                                </div>
                                <div class="col-md-9">
                                    <input type="color" name="website_color" class="form-control"
                                           value="{{$setting['website_color']}}">
                                </div>
                            </div>
                        </div>
                        <div class="form-group ">
                            <div class="row">
                                <div class="col-md-3">
                                    <label class="form-label">  نسبة او عمولة الموقع <span class="badge bagde-danger bg-danger"> % </span>  </label>
                                </div>
                                <div class="col-md-9">
                                    <input type="number" name="website_commission" class="form-control"
                                           value="{{$setting['website_commission']}}">
                                </div>
                            </div>
                        </div>

                        <hr>
                        <h5>قسم الهيرو (Hero Section)</h5>
                        <p class="text-muted">البيانات التي تظهر في البانر الرئيسي للموقع</p>

                        <div class="form-group ">
                            <div class="row">
                                <div class="col-md-3">
                                    <label class="form-label">النص الأول (السطر 1)</label>
                                </div>
                                <div class="col-md-9">
                                    <input type="text" class="form-control" name="hero_title_1" value="{{$setting['hero_title_1']}}" placeholder="نفّذها منصّة تجمع خبراء التسويق والإعلام مع أصحاب المشاريع...">
                                </div>
                            </div>
                        </div>
                        <div class="form-group ">
                            <div class="row">
                                <div class="col-md-3">
                                    <label class="form-label">النص الثاني (السطر 2)</label>
                                </div>
                                <div class="col-md-9">
                                    <input type="text" class="form-control" name="hero_title_2" value="{{$setting['hero_title_2']}}" placeholder="إذا كنت تعمل في التسويق، الإعلام، صناعة المحتوى...">
                                </div>
                            </div>
                        </div>
                        <div class="form-group ">
                            <div class="row">
                                <div class="col-md-3">
                                    <label class="form-label">النص الثالث (السطر 3)</label>
                                </div>
                                <div class="col-md-9">
                                    <input type="text" class="form-control" name="hero_title_3" value="{{$setting['hero_title_3']}}" placeholder="سجّل خدماتك وخلّي العملاء يوصلون لك بسهولة">
                                </div>
                            </div>
                        </div>
                        <div class="form-group ">
                            <div class="row">
                                <div class="col-md-3">
                                    <label class="form-label">النص الفرعي (اختياري)</label>
                                </div>
                                <div class="col-md-9">
                                    <textarea class="form-control" name="hero_subtitle" rows="3" placeholder="نص يظهر تحت العناوين الرئيسية">{{$setting['hero_subtitle']}}</textarea>
                                </div>
                            </div>
                        </div>
                        <div class="form-group ">
                            <div class="row">
                                <div class="col-md-3">
                                    <label class="form-label">صورة الخلفية</label>
                                </div>
                                <div class="col-md-9">
                                    <input type="file" name="hero_image" class="form-control" accept="image/*">
                                    @if($setting['hero_image'])
                                        <small class="text-muted">الصورة الحالية: {{ $setting['hero_image'] }}</small>
                                        <br>
                                        <img src="{{ asset('assets/uploads/public_setting/' . $setting['hero_image']) }}" style="max-height:80px; margin-top:5px;">
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="form-group ">
                            <div class="row">
                                <div class="col-md-3">
                                    <label class="form-label">لون التغطية (Overlay)</label>
                                </div>
                                <div class="col-md-9">
                                    <input type="color" class="form-control" name="hero_overlay_color" value="{{$setting['hero_overlay_color'] ?? '#00000057'}}">
                                    <small class="text-muted">شفافة فوق الصورة لتوضيح النص</small>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer text-left">
                            <button type="submit" class="btn btn-primary waves-effect waves-light">تعديل البيانات
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

