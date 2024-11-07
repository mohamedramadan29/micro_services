@extends('website.layouts.master')
@section('title') من نحن  @endsection
@section('content')

    <!-- ============================ Page Title Start================================== -->
    <div class="hero-banner bg-cover side-effect"
         style="background:red url({{asset('assets/website/img/background3.webp')}})no-repeat;"
         data-overlay="6">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-md-12 text-center">
                    <h2 class="ipt-title mb-3"> "نفذها": منصة شاملة لبيع وشراء الخدمات المصغرة والكبيرة للشركات والأفراد </h2>
                    <div class="_ebl_link"><a style="color:#fff" href="#about_us" class="btn _loonking_job"> معرفه المزيد </a></div>
                </div>
            </div>
        </div>
    </div>
    <!-- ============================ Page Title End ================================== -->

    <!-- ========================== About Elements ============================= -->
    <section id="about_us">
        <div class="container">
            <div class="row align-items-center">

                <!-- Single Box -->
                <div class="col-lg-6 col-md-6 col-sm-12">
                    <img src="{{asset('assets/website/img/about_us.svg')}}" alt="" class="img-fluid about_us_image">
                </div>

                <!-- Single Box -->
                <div class="col-lg-6 col-md-6 col-sm-12">
                    <div class="about-captione text-right">
                        <h4> عن نفذها  </h4>
                        <br>
                        <h2> نفذها - منصتك الموثوقة لتنفيذ المشاريع والخدمات   </h2>
                        <br>
                        <p>
                            أهلاً بك في نفذها، وجهتك الموثوقة للحصول على خدمات احترافية من نخبة المستقلين في مختلف المجالات. سواء كنت صاحب مشروع يحتاج إلى حلول مبتكرة أو مستقلًا يرغب في تقديم مهاراته إلى سوق العمل، فإن نفذها يربط بين أصحاب الأعمال المحترفين ومقدمي الخدمات المتميزين بطريقة سلسة وآمنة.
                        </p>
                    </div>
                </div>

            </div>

        </div>
    </section>
    <!-- ========================== About Elements ============================= -->
    <!-- ============================ Call To Action Start ================================== -->
    <section class="call-to-act"
             style="background:#0b85ec url({{asset('assets/website/img/landing-bg.png')}}) no-repeat">
        <div class="container">
            <div class="row justify-content-center">

                <div class="col-lg-7 col-md-8">
                    <div class="clt-caption text-center mb-4">
                        <h2 class="text-light"> هل أنت جاهز لبدء مشروعك الخاص ؟ </h2>
                    </div>
                    <div class="inner-flexible-box subscribe-box">
                        <div class="input-group">
                            <button class="btn btn-primary start_job"> ابدا الان</button>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- ============================ Call To Action End ================================== -->
@endsection
