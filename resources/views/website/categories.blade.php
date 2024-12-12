@extends('website.layouts.master')
@section('title')
    اقسام الخدمات
@endsection
@section('content')

    <!-- ============================ Main Section Start ================================== -->
    <section class="gray-bg text-right category_page profile_page" dir="rtl">
        <div class="container">
            <div class="row">
                <!-- Item Wrap Start -->
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <!-- Filter Search -->
                            <div class="_filt_tag786">
                                <div class="_tag782">
                                    <div class="_tag780"> {{count($categories)}} اقسام رئيسية</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <!-- Single Item -->
                        @foreach($categories as $category)
                            <div class="col-lg-3 col-md-6 col-sm-12">
                                <div class="ser_110 category_data">
                                    <div class="ser_110_thumb">
                                        <a href="{{url('category/'.$category['slug'])}}" class="ser_100_link">
                                            <img src=" {{asset('assets/uploads/service_category/'.$category['image'])}}"
                                                 class="img-fluid" alt="">
                                        </a>
                                    </div>
                                    <div class="ser_110_footer bott">
                                        <div class="_110_foot_left">
                                            <div>
                                                <h5>
                                                    <a href="{{url('category/'.$category['slug'])}}"> {{$category['name']}} </a>
                                                </h5>
                                                <br>
                                                <span> {{ $category->countSubCategories() }}  اقسام فرعية  </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            {{$categories->links()}}
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </section>
    <!-- ============================ Main Section End ================================== -->
 
@endsection
