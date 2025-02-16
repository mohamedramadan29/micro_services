@extends('website.layouts.master')
@section('title')
    {{ $product['name'] }}
@endsection
@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css" rel="stylesheet">
@endsection
@section('content')
    <!-- ============================ Main Section Start ================================== -->
    <section class="gray-bg text-right" dir="rtl">
        <div class="container">
            <div class="main_hero_section">
                <div>
                    <h4> {{ $product['name'] }} </h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="#"> الرئيسية </a></li>
                            <li class="breadcrumb-item"><a href="#"> المنتجات  </a></li>
                            <li class="breadcrumb-item active" aria-current="page"> {{ $product['name'] }} </li>
                        </ol>
                    </nav>
                </div>

            </div>
            <br>
            <div class="row">
                <div class="col-lg-5 col-md-12 col-sm-12">
                    <div class="_jb_summary light_box">
                        <div class="_jb_summary_largethumb">
                            <!-- الصورة الرئيسية -->
                            <a href="{{ asset('assets/uploads/product_images/' . $product['image']) }}"
                                data-lightbox="product-gallery">
                                <img src="{{ asset('assets/uploads/product_images/' . $product['image']) }}"
                                    class="product_image img-fluid" alt="Main Product Image">
                            </a>
                        </div>
                        <!-- السلايدر -->
                        <div class="swiper mySwiper">
                            <div class="swiper-wrapper">
                                <!-- الصورة الرئيسية في السلايدر -->
                                <div class="swiper-slide">
                                    <a href="{{ asset('assets/uploads/product_images/' . $product['image']) }}"
                                        data-lightbox="product-gallery">
                                        <img src="{{ asset('assets/uploads/product_images/' . $product['image']) }}"
                                            class="img-fluid" alt="Main Product Image">
                                    </a>
                                </div>

                                <!-- الصور الإضافية -->
                                @foreach ($product['gallary'] as $image)
                                    <div class="swiper-slide">
                                        <a href="{{ asset('assets/uploads/product_gallery/' . $image['image']) }}"
                                            data-lightbox="product-gallery">
                                            <img src="{{ asset('assets/uploads/product_gallery/' . $image['image']) }}"
                                                class="img-fluid" alt="Gallery Image">
                                        </a>
                                    </div>
                                @endforeach
                            </div>

                            <!-- أزرار التنقل -->
                            <div class="swiper-button-next"></div>
                            <div class="swiper-button-prev"></div>
                        </div>


                        <div class="_jb_summary_caption">
                            <h4 class="product_name"> {{ $product['name'] }} </h4>
                        </div>
                        <div class="_view_dis_908 price_section">
                            <ul class="exlio_list">
                                @if ($product['discount'] > 0)
                                    <li> السعر <span style="color: #3fb699"> {{ number_format($product['discount'], 2) }}
                                            $</span>
                                        <span style="text-decoration: line-through;margin-left:10px">
                                            {{ number_format($product['price'], 2) }} $</span>
                                    </li>
                                @else
                                    <li> السعر <span style="color: #3fb699"> {{ number_format($product['price'], 2) }}
                                            $</span>
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
                                                <li style="color: red"> تترواح مدة الشحن بين 10 - 15 يوم من تاريخ الطلب
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="modal-footer">
                                            @if (Auth::check())
                                                <form style="width: 100%" method="post"
                                                    action="{{ url('product_order') }}">
                                                    <div class="form-group">
                                                        <label> الاسم </label>
                                                        <input style="height: 45px" class="form-control" name="name"
                                                            required value="{{ Auth::user()->name }}">
                                                    </div>
                                                    <div class="form-group">
                                                        <label> رقم الهاتف </label>
                                                        <input style="height: 45px" class="form-control" name="phone"
                                                            required value="{{ Auth::user()->phone }}">
                                                    </div>
                                                    <div class="form-group">
                                                        <label> البريد الالكتروني </label>
                                                        <input style="height: 45px" class="form-control" name="email"
                                                            required value="{{ Auth::user()->email }}">
                                                    </div>
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
                                                    <input type="hidden" name="product_id"
                                                        value="{{ $product['id'] }}">
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
                                                <a href="{{ url('login') }}" type="button" class="btn btn-primary">
                                                    سجل
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
                <div class="col-lg-7 col-md-12 col-sm-12">
                    <div class="_job_detail_box">
                        <div class="_wrap_box_slice">
                            <div class="_job_detail_single">
                                <!-- عرض وصف المنتج -->
                                <p>
                                    {!! $product['description'] !!}
                                </p>

                                <!-- عرض الفيديو -->
                                @if (!empty($product['video']))
                                    <div class="video-container" style="margin-top: 20px;">
                                        <video controls width="100%"
                                            style="border-radius: 10px; box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);">
                                            <source
                                                src="{{ asset('assets/uploads/product_videos/' . $product['video']) }}"
                                                type="video/mp4">
                                            <!-- نص بديل إذا كان المتصفح لا يدعم الفيديو -->
                                            Your browser does not support the video tag.
                                        </video>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>


            </div>
        </div>
    </section>
    <!-- ============================ Main Section End ================================== -->
@endsection

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>

    <script>
        var swiper = new Swiper(".mySwiper", {
            slidesPerView: 2, // عرض صورتين في وقت واحد
            // spaceBetween: 2, // المسافة بين الصور
            navigation: {
                nextEl: ".swiper-button-next",
                prevEl: ".swiper-button-prev",
            },
            loop: true, // التمرير بشكل دائري
            // centeredSlides: true, // وضع الصورة النشطة في المنتصف
        });
    </script>
@endsection
