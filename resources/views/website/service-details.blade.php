@extends('website.layouts.master')
@section('title')
    {{$service['name']}}
@endsection
@section('content')

    <!-- ============================ Main Section Start ================================== -->
    <section class="gray-bg text-right" dir="rtl">
        <div class="container">
            <div class="main_hero_section" style="margin-bottom: 30px">
                <div>
                    <h4>   {{$service['name']}} </h4>
                </div>
                <div>
                    <a class="btn btn-global-button" href="{{url('service/add')}}">  اضف خدمتك الان  <i class="fa fa-plus"></i> </a>
                </div>
            </div>
            <div class="row">
                <!-- Item Wrap Start -->
                <div class="col-lg-8 col-md-12 col-sm-12">
                    <div class="_job_detail_box">
                        @if (Session::has('Success_message'))
                            @php
                                toastify()->success(\Illuminate\Support\Facades\Session::get('Success_message'));
                            @endphp
                        @endif
                        @if ($errors->any())
                            @foreach ($errors->all() as $error)
                                @php
                                    toastify()->error($error);
                                @endphp
                            @endforeach
                        @endif
                        <div class="_wrap_box_slice">
                            <div class="_job_detail_single">
                                <p>
                                    {{$service['description']}}
                                </p>
                            </div>
                            <div class="_job_detail_single">
                                <h4> المهارات </h4>
                                @php
                                    $skills = explode(',',$service['tags']);
                                @endphp
                                <ul class="skilss">
                                    @foreach($skills as $skill)
                                        <li><a href="javascript:void(0);"> {{$skill}} </a></li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                        <!-- Award & Experience -->
                        <div class="_wrap_box_slice">
                            <div class="_job_detail_single">
                                <h4> خدمات مقترحة </h4>
                                <div class="row">
                                    @foreach($more_servicess as $serv)
                                        <!-- Single Item -->
                                        <div class="col-lg-4 col-md-6 col-sm-12">
                                            <div class="ser_110">
                                                <div class="ser_110_thumb">
                                                    <a href="{{url('service/'.$serv['id'].'-'.$serv['slug'])}}"
                                                       class="ser_100_link"><img
                                                            src=" {{asset('assets/uploads/services/'.$serv['image'])}}"
                                                            class="img-fluid" alt=""></a>
                                                </div>
                                                <div class="ser_110_footer bott">
                                                    <div class="_110_foot_left">
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
                                                            <div class="_oi0po price_section"><i class="fa fa-bolt"></i>
                                                                سعر الخدمة <strong
                                                                    class="theme-cl"> {{ number_format($serv['price'],2)}}
                                                                    $ </strong>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-12 col-sm-12">
                    <div class="_jb_summary light_box">
                        <div class="_jb_summary_largethumb service_details_section">
                            <img src="{{asset('assets/uploads/services/'.$service['image'])}}" class="img-fluid" alt="">
                        </div>
                        <div class="_jb_summary_thumb">
                            <img style="width: 75px;height: 75px;border-radius: 100%;border: 2px solid #ccc;"
                                 src="{{asset('assets/uploads/users_image/'.$service['user']['image'])}}"
                                 class="img-fluid circle" alt="">
                        </div>
                        <div class="_jb_summary_caption">
                            <h4> {{$service['user']['name']}} </h4>
                            <span>{{$service['user']['job_title']}}</span>
                        </div>
                        <div class="_jb_summary_body">
                            @if($service['user_id'] != \Illuminate\Support\Facades\Auth::id())
                                <div class="_view_dis_908 d-flex justify-content-center">
                                    <button type="button" class="btn flw_btn btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#exampleModal">
                                        شراء الخدمة الان
                                    </button>

                                    <!-- Modal -->
                                    <div class="modal fade buy_services_model" id="exampleModal" tabindex="-1"
                                         aria-labelledby="exampleModalLabel" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h1 class="modal-title fs-5" id="exampleModalLabel"> شراء
                                                        الخدمة </h1>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <ul>
                                                        <li> منصة نفذها تضمن حقوقك بنسبة 100% .</li>
                                                        <li> لا تتردد ابداً في التواصل معنا إذا احتجت أي مساعدة وسنسعد
                                                            بخدمتك.
                                                        </li>
                                                        <li> يمكنك التواصل مع مقدم الخدمة إذا احتجت أي استفسار قبل طلب
                                                            الخدمة.
                                                        </li>
                                                    </ul>
                                                </div>
                                                <div class="modal-footer">

                                                    <button type="button" style="background-color: #404040;"
                                                            class="btn btn-secondary" data-bs-dismiss="modal">رجوع
                                                    </button>

                                                    @if(Auth::check())
                                                        <form method="post" action="{{url('create_order')}}">
                                                            <input type="hidden" name="service_id"
                                                                   value="{{$service['id']}}">
                                                            <input type="hidden" name="service_name"
                                                                   value="{{$service['name']}}">
                                                            <input type="hidden" name="service_price"
                                                                   value="{{$service['price']}}">
                                                            <input type="hidden" name="qty" value="1">
                                                            <input type="hidden" name="user_serv"
                                                                   value="{{$service['user_id']}}">
                                                            @csrf
                                                            <button type="submit" class="btn btn-primary"> شراء الخدمة الان
                                                                <i class="bi bi-cart"></i></button>
                                                        </form>

                                                    @else
                                                        <a href="{{url('login')}}" type="button"
                                                           class="btn btn-primary">شراء الخدمة الان </a>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    @if(\Illuminate\Support\Facades\Auth::id())
                                        <form action="{{url('conversation/start')}}" method="post">
                                            @csrf
                                            <input type="hidden" name="service_id" value="{{$service['id']}}">
                                            <input type="hidden" name="sender_id" value="{{Auth::id()}}">
                                            <input type="hidden" name="receiver_id" value="{{$service['user_id']}}">
                                            <button type="submit" class="btn msg_btn btn-sm"> تواصل معي</button>
                                        </form>
                                    @else
                                        <a href="{{url('register')}}" class="btn msg_btn btn-sm"> تواصل معي </a>
                                    @endif
                                </div>
                            @endif
                            <div class="_view_dis_908">
                                <ul class="exlio_list">
                                    <li>  <span class="text-success">
                                               <div class="ser_rev098">
                                                   @for($i = 0 ; $i < 5; $i++)
                                                       @if($service['rate'] > $i)
                                                           <i class="fa fa-star filled"></i>
                                                       @else
                                                           <i class="fa fa-star"></i>
                                                       @endif
                                                   @endfor

                                    </div>
                                      </span> التقييمات
                                    </li>
                                    {{--                                    <li>المشترين <span class="text-warning"> {{$service['users_num_buy']}} </span></li>--}}
                                    {{--                                    <li> طلبات جاري تنفيذها <span class="text-info">1</span></li>--}}
                                    <li> سعر الخدمة <span class="" style="font-size: 17px;color: #3fb699;"> {{$service['price']}} $</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="_jb_summary light_box p-4">
                        <h4> مشاركة الخدمة </h4>
                        <!-- AddToAny BEGIN -->
                        <div class="a2a_kit a2a_kit_size_32 a2a_default_style" style="float: right">
                            <a class="a2a_dd" href="https://www.addtoany.com/share"></a>
                            <a class="a2a_button_facebook"></a>
                            <a class="a2a_button_linkedin"></a>
                            <a class="a2a_button_whatsapp"></a>
                            <a class="a2a_button_telegram"></a>
                            <a class="a2a_button_x"></a>
                        </div>
                        <script async src="https://static.addtoany.com/menu/page.js"></script>
                        <!-- AddToAny END -->
                    </div>

                </div>

            </div>
        </div>
    </section>
    <!-- ============================ Main Section End ================================== -->

@endsection
