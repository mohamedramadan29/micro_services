@extends('website.layouts.master')
@section('title')
    {{$product['name']}}
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
    <section class="gray-bg text-right product_page" dir="rtl">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-12 col-sm-12">
                    <div class="_jb_summary light_box">
                        <div class="_jb_summary_largethumb">
                            <img src="{{asset('assets/uploads/product_images/'.$product['image'])}}" class="product_image img-fluid"
                                 alt="">
                        </div>

                        <div class="_jb_summary_caption">
                            <h4 class="product_name"> {{$product['name']}} </h4>
                        </div>
                        <div class="_view_dis_908 price_section">
                            <ul class="exlio_list">
                                <li> السعر <span
                                        class="text-danger"> {{ number_format($product['price'],2)}} $</span>
                                </li>
                            </ul>
                        </div>
                        <div class="_jb_summary_body">
                            <div class="_view_dis_908 d-flex">
                                <form style="width: 100%" method="post" action="{{url('cart/add')}}">
                                    <input type="hidden" name="product_id" value="{{$product['id']}}">
                                    <input type="hidden" name="product_name" value="{{$product['name']}}">
                                    <input type="hidden" name="product_price" value="{{$product['price']}}">
                                    <input type="hidden" name="qty" value="1">
                                    @csrf

                                    <button type="submit" class="btn global_button"> شراء المنتج  <i class="bi bi-bag"></i> </button>
                                </form>
                            </div>

                        </div>
                    </div>

                    <div class="_jb_summary light_box p-4">
                        <h4> مشاركة الخدمة </h4>
                        <!-- AddToAny BEGIN -->
                        <div class="a2a_kit a2a_kit_size_32 a2a_default_style " style=" float: right">
                            <a class="a2a_dd" href="https://www.addtoany.com/share"> </a>
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
                                    {{$product['description']}}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>
    <!-- ============================ Main Section End ================================== -->

@endsection
