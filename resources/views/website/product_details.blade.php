@extends('website.layouts.master')
@section('title')
    {{ $product['name'] }}
@endsection
@section('content')
    <!-- ============================ Page Title Start================================== -->
    <div class="page-title bg-cover" style="background:url( {{ asset('assets/website/img/bn-2.jpg') }})no-repeat;"
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
                            <img src="{{ asset('assets/uploads/product_images/' . $product['image']) }}"
                                class="product_image img-fluid" alt="">
                        </div>

                        <div class="_jb_summary_caption">
                            <h4 class="product_name"> {{ $product['name'] }} </h4>
                        </div>
                        <div class="_view_dis_908 price_section">
                            <ul class="exlio_list">
                                @if($product['discount'] > 0)
                                    <li> السعر <span style="color: #3fb699"> {{ number_format($product['discount'], 2) }} $</span>
                                        <span style="text-decoration: line-through;margin-left:10px"> {{ number_format($product['price'], 2) }}  $</span>
                                    </li>

                                    @else
                                <li> السعر <span style="color: #3fb699"> {{ number_format($product['price'], 2) }} $</span>
                                </li>

                                @endif
                                                         </ul>
                        </div>
                        <div class="_jb_summary_body">
                            <div class="_view_dis_908 d-flex">

                                <button type="button" class="btn global_button" data-bs-toggle="modal"
                                    data-bs-target="#exampleModal">
                                    شراء المنتج <i class="bi bi-bag"></i>
                                </button>
                            </div>
                            <!-- Modal -->
                            <div class="modal fade buy_services_model" id="exampleModal" tabindex="-1"
                                aria-labelledby="exampleModalLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h1 class="modal-title fs-5" id="exampleModalLabel"> شراء المنتج </h1>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"> X </button>
                                        </div>
                                        <div class="modal-body">
                                            <ul>
                                                <li> منصة نفذها تضمن حقوقك بنسبة 100% .</li>
                                                <li> لا تتردد ابداً في التواصل معنا إذا احتجت أي مساعدة وسنسعد
                                                    بخدمتك.
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="modal-footer">
                                            @if (Auth::check())
                                                <form style="width: 100%" method="post"
                                                    action="{{ url('product_order') }}">
                                                    <div class="form-group">
                                                        <label> الدولة </label>
                                                        <input style="height: 45px" class="form-control" name="country"
                                                            required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label> المدينة </label>
                                                        <input style="height: 45px" class="form-control" name="city"
                                                            required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label> العنوان بشكل تفصيلي </label>
                                                        <input style="height: 45px" class="form-control" name="address"
                                                            required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label> ملاحظات اضافية </label>
                                                        <textarea style="height: 90px" name="note" id="" class="form-control"></textarea>
                                                    </div>
                                                    <input type="hidden" name="product_id" value="{{ $product['id'] }}">
                                                    <input type="hidden" name="product_name"
                                                        value="{{ $product['name'] }}">
                                                    <input type="hidden" name="product_price"
                                                        value="{{ $product['price'] }}">
                                                    <input type="hidden" name="qty" value="1">
                                                    @csrf
                                                    <button type="submit" class="btn global_button"> شراء المنتج <i
                                                            class="bi bi-bag"></i></button>
                                                </form>
                                            @else
                                                <a href="{{ url('login') }}" type="button" class="btn btn-primary"> سجل
                                                    دخولك الان لتكملة الشراء </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
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
                                    {{ $product['description'] }}
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
