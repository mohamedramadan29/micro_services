@extends('website.layouts.master')
@section('title')
    {{$service['name']}}
@endsection
@section('content')
    <!-- ============================ Page Title Start================================== -->
    <div class="page-title bg-cover" style="background:url( {{asset('assets/website/img/bn-2.jpg')}})no-repeat;"
         data-overlay="5">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-md-12">

                </div>
            </div>
        </div>
    </div>
    <!-- ============================ Page Title End ================================== -->

    <!-- ============================ Main Section Start ================================== -->
    <section class="gray-bg text-right" dir="rtl">
        <div class="container">
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
                                                        <div>
                                                            <h5>
                                                                <a href="{{url('service/'.$serv['id'].'-'.$serv['slug'])}}"> {{$serv['name']}} </a>
                                                            </h5>
                                                            <span> {{$serv['category']['name']}}  <span>
                                                              <div class="_dash_usr_rates mb-1">
															<span class="good"> {{$serv['rate']}} </span>
                                                                  @for($i = 0 ; $i < 5 ; $i++ )
                                                                      @if($i < $serv['rate'])
                                                                          <i class="fa fa-star"></i>
                                                                      @else
                                                                          <i class="fa fa-star-o"></i>
                                                                      @endif
                                                                  @endfor
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
                        <div class="_jb_summary_largethumb">
                            <img src="{{asset('assets/uploads/services/'.$service['image'])}}" class="img-fluid" alt="">
                        </div>
                        <div class="_jb_summary_thumb">
                            <img src="{{asset('assets/uploads/users_image/'.$service['user']['image'])}}"
                                 class="img-fluid circle" alt="">
                        </div>
                        <div class="_jb_summary_caption">
                            <h4> {{$service['user']['name']}} </h4>
                            <span>{{$service['user']['job_title']}}</span>
                        </div>
                        <div class="_jb_summary_body">
                            @if($service['user_id'] != \Illuminate\Support\Facades\Auth::id())
                            <div class="_view_dis_908 d-flex">
                                <form method="post" action="{{url('cart/add')}}">
                                    <input type="hidden" name="service_id" value="{{$service['id']}}">
                                    <input type="hidden" name="service_name" value="{{$service['name']}}">
                                    <input type="hidden" name="service_price" value="{{$service['price']}}">
                                    <input type="hidden" name="qty" value="1">
                                    <input type="hidden" name="user_serv" value="{{$service['user_id']}}">
                                    @csrf
                                    <button type="submit" class="btn flw_btn"> اضف الي السلة</button>
                                </form>
                                @if(\Illuminate\Support\Facades\Auth::id())
                                    <form action="{{url('conversation/start')}}" method="post">
                                        @csrf
                                       <input type="hidden" name="service_id" value="{{$service['id']}}">
                                        <input type="hidden" name="sender_id" value="{{Auth::id()}}">
                                        <input type="hidden" name="receiver_id" value="{{$service['user_id']}}">
                                        <button type="submit" class="btn msg_btn"> تواصل معي </button>
                                    </form>
                                @else
                                    <a href="{{url('register')}}" class="btn msg_btn"> تواصل معي </a>
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
                                    <li>المشترين <span class="text-warning"> {{$service['users_num_buy']}} </span></li>
                                    <li> طلبات جاري تنفيذها <span class="text-info">1</span></li>
                                    <li> سعر الخدمة يبدأ من <span class="text-danger"> {{$service['price']}} $</span>
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
