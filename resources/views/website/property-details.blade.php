@extends('website.layouts.master')
@section('title')
    {{ $property['title'] }}
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
                    <h4> {{ $property['title'] }} </h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ url('/') }}"> الرئيسية </a></li>
                            <li class="breadcrumb-item"><a href="{{ url('products') }}"> العقارات </a></li>
                            <li class="breadcrumb-item active" aria-current="page"> {{ $property['title'] }} </li>
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
                            <a href="{{ asset('assets/uploads/properities/' . ($property->ProperityFirstImage->image ?? 'default.jpg')) }}"
                                data-lightbox="product-gallery">
                                <img src="{{ asset('assets/uploads/properities/' . ($property->ProperityFirstImage->image ?? 'default.jpg')) }}"
                                    class="product_image img-fluid" alt="{{ $property['title'] }}">
                            </a>
                        </div>
                        <!-- السلايدر -->
                        <div class="swiper mySwiper">
                            <div class="swiper-wrapper">
                                <!-- الصورة الرئيسية في السلايدر -->
                                <div class="swiper-slide">
                                    <a href="{{ asset('assets/uploads/properities/' . ($property->ProperityFirstImage->image ?? 'default.jpg')) }}"
                                        data-lightbox="product-gallery">
                                        <img src="{{ asset('assets/uploads/properities/' . ($property->ProperityFirstImage->image ?? 'default.jpg')) }}"
                                            class="img-fluid" alt="{{ $property['title'] }}">
                                    </a>
                                </div>

                                <!-- الصور الإضافية -->
                                @foreach ($property['ProperityImages'] as $image)
                                    <div class="swiper-slide">
                                        <a href="{{ asset('assets/uploads/properities/' . $image->image) }}"
                                            data-lightbox="product-gallery">
                                            <img src="{{ asset('assets/uploads/properities/' . $image->image) }}"
                                                class="img-fluid" alt="{{ $property['title'] }}">
                                        </a>
                                    </div>
                                @endforeach
                            </div>

                            <!-- أزرار التنقل -->
                            <div class="swiper-button-next"></div>
                            <div class="swiper-button-prev"></div>
                        </div>


                        <div class="_jb_summary_caption">
                            <h4 class="product_name mt-3"> {{ $property['title'] }} </h4>
                        </div>
                        <div class="_view_dis_908 price_section">
                            <ul class="exlio_list p-2">
                                <li> السعر <span style="color: #3fb699"> {{ number_format($property['price'], 2) }}
                                        {{ $property['currency']?:'دولار' }}</span>
                                </li>

                                <li> نوع العقار <span style="color: #3fb699"> {{ $property['type'] }}</span> </li>
                                <li> القسم <span style="color: #3fb699"> {{ $property['category'] }}</span> </li>
                                <li> المساحة <span style="color: #3fb699"> {{ $property['area'] }} م٢ </span> </li>
                                @if ($property['rooms'] > 0 && $property['rooms'] != null)
                                    <li> عدد الغرف <span style="color: #3fb699"> {{ $property['rooms'] }} </span> </li>
                                @endif
                                @if ($property['bathrooms'] > 0 && $property['bathrooms'] != null)
                                    <li> عدد الحمامات <span style="color: #3fb699"> {{ $property['bathrooms'] }} </span>
                                    </li>
                                @endif


                            </ul>
                        </div>
                        <div class="_jb_summary_body">
                            {{-- <div class="_view_dis_908 d-flex">

                                <button type="button" class="btn global_button" data-bs-toggle="modal"
                                    data-bs-target="#exampleModal">
                                    شراء المنتج <i class="bi bi-bag"></i>
                                </button>
                            </div> --}}
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
                                    {!! $property['description'] !!}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="_job_detail_box">
                        <div class="_wrap_box_slice">
                            <div class="_job_detail_single">
                                <!-- عرض وصف المنتج -->
                                @if ($property['features'] != null)
                                    @php
                                        $property_features = explode(',', $property['features']);
                                    @endphp
                                    <ul class="list-unstyled">
                                        @foreach ($property_features as $feature)
                                            <li> <i style="color: #3fb699" class="fa fa-check"></i> {{ $feature }}
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif

                            </div>
                            <div class="_job_detail_single">
                                <!-- عرض وصف المنتج -->
                                @if ($property['location'] != null)
                                    <ul class="list-unstyled">
                                        <li> <i style="color: #3fb699" class="bi bi-geo-alt-fill"></i> {{ $property['location'] }}
                                        </li>
                                    </ul>
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
