@extends('website.layouts.master')
@section('title')
    التقيمات
@endsection
@section('content')

    <!-- ============================ Main Section Start ================================== -->
    <section class="gray-bg pt-4 text-right profile_page" dir="rtl">
        <div class="container-fluid">
            <div class="row m-0">
                <div class="col-xl-3 col-lg-4 col-md-12 col-sm-12">
                    <div class="dashboard-navbar overlio-top">

                        <div class="d-user-avater">
                            @if(Auth::user()->image !='')
                                <img src="{{asset('assets/uploads/users_image/'.Auth::user()->image)}}"
                                     class="img-fluid rounded" alt="">
                            @else
                                <img src="{{asset('assets/website/img/avatar.png')}}" class="img-fluid rounded" alt="">
                            @endif
                            <h4> {{Auth::user()->user_name}} </h4>
                            <span> {{Auth::user()->email}} </span>
                        </div>

                        @include('website.layouts.dashboard-sidebar')


                    </div>
                </div>

                <!-- Item Wrap Start -->
                <div class="col-xl-9 col-lg-8 col-md-12 col-sm-12">
                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <!-- Breadcrumbs -->
                            <div class="bredcrumb_wrap">
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="{{url('/')}}">الرئيسية </a></li>
                                        <li class="breadcrumb-item active" aria-current="page">التقيمات </li>
                                    </ol>
                                </nav>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12">

                            <!-- Single Wrap -->
                            <div class="_dashboard_content">
                                <div class="_dashboard_content_header">
                                    <div class="_dashboard__header_flex">
                                        <h4><i class="fa fa-star mr-1"></i> تقيماتي  </h4>
                                    </div>
                                </div>

                                <div class="_dashboard_content_body p-0">
                                    <div class="_grouping_reviews_wrap">

                                        <!-- Single Reviews -->
                                        <div class="_grouping_single_reviews">
                                            <div class="_grouping_single_reviews_thumb">
                                                <img src="{{asset('assets/website/img/team-1.jpg')}}" class="img-fluid circle" alt=""/>
                                            </div>
                                            <div class="_grouping_single_reviews_caption">
                                                <h4 class="_rev_title_cats"> فهد العتيبي   <span class="esxlop_titme">2 days ago</span>
                                                </h4>
                                                <div class="_dash_usr_rates mb-1">
                                                    <i class="fa fa-star"></i>
                                                    <i class="fa fa-star"></i>
                                                    <i class="fa fa-star"></i>
                                                    <i class="fa fa-star"></i>
                                                    <i class="fa fa-star"></i>
                                                    <span class="good">4.5</span>
                                                </div>
                                                <h5 class="_rev_subject_cats"> تقيم برمجة موقع الكتروني  </h5>
                                                <p>خدمة ممتازة جدا شكرا لك استاذ محمد  </p>
                                                <a href="#" data-toggle="modal" data-target="#editreviews"
                                                   class="_review_edit_btn"> اضف ردا <i class="fa fa-edit"></i>  </a>
                                            </div>
                                        </div>
                                        <!-- Single Reviews -->
                                        <div class="_grouping_single_reviews">
                                            <div class="_grouping_single_reviews_thumb">
                                                <img src="{{asset('assets/website/img/team-1.jpg')}}" class="img-fluid circle" alt=""/>
                                            </div>
                                            <div class="_grouping_single_reviews_caption">
                                                <h4 class="_rev_title_cats"> فهد العتيبي   <span class="esxlop_titme">2 days ago</span>
                                                </h4>
                                                <div class="_dash_usr_rates mb-1">
                                                    <i class="fa fa-star"></i>
                                                    <i class="fa fa-star"></i>
                                                    <i class="fa fa-star"></i>
                                                    <i class="fa fa-star"></i>
                                                    <i class="fa fa-star"></i>
                                                    <span class="good">4.5</span>
                                                </div>
                                                <h5 class="_rev_subject_cats"> تقيم برمجة موقع الكتروني  </h5>
                                                <p>خدمة ممتازة جدا شكرا لك استاذ محمد  </p>
                                                <a href="#" data-toggle="modal" data-target="#editreviews"
                                                   class="_review_edit_btn"> اضف ردا <i class="fa fa-edit"></i>  </a>
                                            </div>
                                        </div>
                                        <!-- Single Reviews -->
                                        <div class="_grouping_single_reviews">
                                            <div class="_grouping_single_reviews_thumb">
                                                <img src="{{asset('assets/website/img/team-1.jpg')}}" class="img-fluid circle" alt=""/>
                                            </div>
                                            <div class="_grouping_single_reviews_caption">
                                                <h4 class="_rev_title_cats"> فهد العتيبي   <span class="esxlop_titme">2 days ago</span>
                                                </h4>
                                                <div class="_dash_usr_rates mb-1">
                                                    <i class="fa fa-star"></i>
                                                    <i class="fa fa-star"></i>
                                                    <i class="fa fa-star"></i>
                                                    <i class="fa fa-star"></i>
                                                    <i class="fa fa-star"></i>
                                                    <span class="good">4.5</span>
                                                </div>
                                                <h5 class="_rev_subject_cats"> تقيم برمجة موقع الكتروني  </h5>
                                                <p>خدمة ممتازة جدا شكرا لك استاذ محمد  </p>
                                                <a href="#" data-toggle="modal" data-target="#editreviews"
                                                   class="_review_edit_btn"> اضف ردا <i class="fa fa-edit"></i>  </a>
                                            </div>
                                        </div>
                                        <!-- Single Reviews -->
                                        <div class="_grouping_single_reviews">
                                            <div class="_grouping_single_reviews_thumb">
                                                <img src="{{asset('assets/website/img/team-1.jpg')}}" class="img-fluid circle" alt=""/>
                                            </div>
                                            <div class="_grouping_single_reviews_caption">
                                                <h4 class="_rev_title_cats"> فهد العتيبي   <span class="esxlop_titme">2 days ago</span>
                                                </h4>
                                                <div class="_dash_usr_rates mb-1">
                                                    <i class="fa fa-star"></i>
                                                    <i class="fa fa-star"></i>
                                                    <i class="fa fa-star"></i>
                                                    <i class="fa fa-star"></i>
                                                    <i class="fa fa-star"></i>
                                                    <span class="good">4.5</span>
                                                </div>
                                                <h5 class="_rev_subject_cats"> تقيم برمجة موقع الكتروني  </h5>
                                                <p>خدمة ممتازة جدا شكرا لك استاذ محمد  </p>
                                                <a href="#" data-toggle="modal" data-target="#editreviews"
                                                   class="_review_edit_btn"> اضف ردا <i class="fa fa-edit"></i>  </a>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                            <!-- Single Wrap End -->

                        </div>
                    </div>

                </div>

            </div>
        </div>
    </section>
    <!-- ============================ Main Section End ================================== -->
@endsection
