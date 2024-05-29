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
                    <h2 class="ipt-title mb-3"> خمسات لبيع وشراء الخدمات المصغرة </h2>
                    <p class="text-light mb-4">
                        خمسات هو السوق العربي الأول لبيع وشراء الخدمات المصغرة، يجمع خمسات بين الشباب العربي المستعد
                        لتقديم الخدمات وبين فئة المشترين المستعدين لشراء هذه الخدمات، وبذلك يوفر دخلاً مناسباً للشباب
                        العربي وخدمات مميزة بسعر اقتصادي للأفراد والشركات الناشئة. </p>
                    <div class="_ebl_link"><a href="#" class="btn _loonking_job"> معرفه المزيد </a></div>
                </div>
            </div>
        </div>
    </div>
    <!-- ============================ Page Title End ================================== -->

    <!-- ========================== About Elements ============================= -->
    <section>
        <div class="container">
            <div class="row align-items-center">

                <!-- Single Box -->
                <div class="col-lg-6 col-md-6 col-sm-12">
                    <img src="{{asset('assets/website/img/co-6.jpg')}}" alt="" class="img-fluid">
                </div>

                <!-- Single Box -->
                <div class="col-lg-6 col-md-6 col-sm-12">
                    <div class="about-captione text-right">
                        <h6 class="text-blue"> عن خمسات </h6>
                        <h2> لبيع وشراء الخدمات المصغرة </h2>
                        <p>

                            خمسات هو السوق العربي الأول لبيع وشراء الخدمات المصغرة، يجمع خمسات بين الشباب العربي المستعد
                            لتقديم الخدمات وبين فئة المشترين المستعدين لشراء هذه الخدمات، وبذلك يوفر دخلاً مناسباً
                            للشباب العربي وخدمات مميزة بسعر اقتصادي للأفراد والشركات الناشئة.
                        </p>
                        <p>
                            أطلق خمسات في أغسطس 2010 بواسطة المدون رءوف شبايك وقد حصل الموقع على المركز الأول في مسابقة
                            عالم التقنية لأفضل مشاريع الويب العربية لعام 2011. أعلنت شركة حسوب عن شرائها لموقع خمسات
                            بتاريخ 1/7/2012.
                        </p>

                        <p>
                            خمسات تابع لشركة حسوب، Hsoub Limited مسجّلة في المملكة المتحدة برقم 07571594.
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
