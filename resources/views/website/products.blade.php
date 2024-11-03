@extends('website.layouts.master')
@section('title')
    منتجات نفذها
@endsection
@section('content')

    <!-- ============================ Page Title Start================================== -->
    <div class="page-title" style="height: 350px;text-align: right">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-md-12">

                    <h2 class="ipt-title"> منتجاتنا </h2>
                    <span class="ipn-subtitle"> مشاهدة جميع منتجات نفذها   </span>

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
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <div class="row">
                        @foreach($products as $product)
                            <!-- Single Item -->
                            <div class="col-lg-3 col-md-6 col-sm-12">
                                <div class="ser_110 shadow_0">
                                    <div class="ser_110_thumb">
                                        <a href="{{url('product/'.$product['slug'])}}"
                                           class="ser_100_link"><img
                                                src="{{asset('assets/uploads/product_images/'.$product['image'])}}"
                                                class="img-fluid" alt=""></a>
                                    </div>
                                    <div class="ser_110_footer bott">


                                    </div>
                                    <div class="ser_110_caption">

                                        <div class="ser_title098">
                                            <h4 class="_ser_title"><a
                                                    href="{{url('product/'.$product['slug'])}}"> {{$product['name']}} </a>
                                            </h4>
                                        </div>
                                        <div class="_oi0po"><i class="fa fa-bolt"></i> السعر  <strong
                                                class="theme-cl"> {{ number_format($product['price'],2)}} $ </strong>
                                        </div>
                                        <div class="add_to_cart">
                                            <form method="post" action="">
                                                @csrf
                                                <button class="global_button btn" type="submit"> اضف الي السلة  <i class="bi bi-bag"></i> </button>
                                            </form>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            {{$products->links()}}
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </section>
    <!-- ============================ Main Section End ================================== -->

@endsection
