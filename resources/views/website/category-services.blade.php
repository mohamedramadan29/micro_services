@extends('website.layouts.master')
@section('title')
    الخدمات - {{$category['name']}}
@endsection
@section('content')

    <!-- ============================ Main Section Start ================================== -->
    <section class="gray-bg text-right" dir="rtl">
        <div class="container">
            <div class="main_hero_section" style="margin-bottom:20px">
                <div>
                    <h4> الخدمات - {{$category['name']}}  </h4>
                </div>

            </div>
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <div class="simple-sidebar sm-sidebar" style="margin-top: 0">
                        <div class="search-sidebar_header">
                            <h4 class="ssh_heading"> البحث </h4>
                        </div>
                        <!-- Find New Property -->
                        <div class="sidebar-widgets" id="search_open" data-parent="#search_open">
                            <form action="{{url('services/'.$category['slug'])}}" method="get">
                                @csrf
                                <div class="search-inner p-0 d-flex align-items-center">
                                    <div class="filter-search-box pb-0" style="width: 80%">
                                        <div class="form-group">
                                            <input type="text" name="search" class="form-control" value="{{request()->input('search')}}"
                                                   placeholder=" ابحث عن الخدمة ... ">
                                        </div>
                                    </div>
                                    <div class="form-group filter_button" style="padding:10px;margin:0;">
                                        <button type="submit" class="btn btn btn-theme-2 rounded full-width"> بحث
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                    </div>
                    <!-- Sidebar End -->

                </div>

                <!-- Item Wrap Start -->
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <div class="row">
                        @foreach($services as $serv)
                            <!-- Single Item -->
                            <div class="col-lg-3 col-md-6 col-sm-12">
                                <div class="ser_110 shadow_0" style="min-height: 470px">
                                    <div class="ser_110_thumb">
                                        <a href="{{url('service/'.$serv['id'].'-'.$serv['slug'])}}"
                                           class="ser_100_link"><img
                                                src="{{asset('assets/uploads/services/'.$serv['image'])}}"
                                                class="img-fluid" alt=""></a>
                                    </div>
                                    <div class="ser_110_footer bott">
                                        <div class="_110_foot_left">
                                            <div class="_autho098">
                                                @if (empty($serv['user']['image']))
                                                    <img
                                                        src="{{asset('assets/website/img/avatar.png')}}"
                                                        class="img-fluid circle" alt="">
                                                @else
                                                    <img
                                                        src="{{asset('assets/uploads/users_image/'.$serv['user']['image'])}}"
                                                        class="img-fluid circle" alt="">

                                                @endif
                                                <img
                                                    src="{{asset('assets/website/img/verify.svg')}}"
                                                    class="verified"
                                                    width="12" alt=""></div>
                                            <div class="_autho097"><h5><a
                                                        href="{{url('user/'.$serv['user']['user_name'])}}">{{$serv['user']['user_name']}}</a>
                                                </h5>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="ser_110_caption">
                                        <div class="ser_rev098">
                                            @for($i = 0; $i < 5 ;  $i++)
                                                @if($i < $serv['rate'])
                                                    <i class="fa fa-star filled"></i>
                                                @else
                                                    <i class="fa fa-star"></i>
                                                @endif
                                            @endfor
                                        </div>
                                        <div class="ser_title098">
                                            <h4 class="_ser_title"><a
                                                    href="{{url('service/'.$serv['id'].'-'.$serv['slug'])}}"> {{$serv['name']}} </a>
                                            </h4>
                                        </div>
                                        <div class="_oi0po"><i class="fa fa-bolt"></i> تبدا من <strong
                                                class="theme-cl"> {{ number_format($serv['price'],2)}} $ </strong>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            {{$services->links()}}
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </section>
    <!-- ============================ Main Section End ================================== -->
@endsection
