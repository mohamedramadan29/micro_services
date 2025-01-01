@extends('website.layouts.master')
@section('title')
    منتجات نفذها
@endsection
@section('content')
    <!-- ============================ Main Section Start ================================== -->
    <section class="gray-bg text-right profile_page" dir="rtl">
        <div class="container">
            <div class="row">
                <!-- Item Wrap Start -->
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <div class="row">
                        @foreach ($products as $product)
                            <!-- Single Item -->
                            <div class="col-lg-3 col-md-6 col-sm-12">
                                <div class="ser_110 shadow_0">
                                    <div class="ser_110_thumb">
                                        <a href="{{ url('product/' . $product['slug']) }}" class="ser_100_link"><img
                                                src="{{ asset('assets/uploads/product_images/' . $product['image']) }}"
                                                class="img-fluid" alt=""></a>
                                    </div>
                                    <div class="ser_110_footer bott">


                                    </div>
                                    <div class="ser_110_caption">

                                        <div class="ser_title098">
                                            <h4 class="_ser_title"><a href="{{ url('product/' . $product['slug']) }}">
                                                    {{ $product['name'] }} </a>
                                            </h4>
                                            <p style="color: #595656;font-size: 14px;padding-bottom: 10px;">
                                                {{ \Illuminate\Support\Str::words($product['description'], 12) }}
                                            </p>
                                        </div>
                                        <div class="_oi0po"><i class="fa fa-bolt"></i> السعر

                                            @if ($product['discount'] > 0)
                                                <strong class="theme-cl" style="opacity: .5; text-decoration:line-through">
                                                    {{ number_format($product['price'], 2) }} $ </strong>
                                                <strong class="theme-cl"> {{ number_format($product['discount'],2) }} $ </strong>
                                            @else
                                                <strong class="theme-cl"> {{ number_format($product['price'], 2) }} $
                                                </strong>
                                            @endif
                                        </div>
                                        {{--                                        <div class="add_to_cart"> --}}
                                        {{--                                            <form method="post" action=""> --}}
                                        {{--                                                @csrf --}}
                                        {{--                                                <button class="global_button btn" type="submit"> اضف الي السلة  <i class="bi bi-bag"></i> </button> --}}
                                        {{--                                            </form> --}}
                                        {{--                                        </div> --}}
                                    </div>

                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            {{ $products->links() }}
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </section>
    <!-- ============================ Main Section End ================================== -->
@endsection
