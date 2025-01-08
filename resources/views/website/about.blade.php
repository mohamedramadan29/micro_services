@extends('website.layouts.master')
@section('title')
    من نحن
@endsection
@section('content')
    <!-- ============================ Page Title Start================================== -->
    <div class="hero-banner bg-cover side-effect"
        style="background:red url({{ asset('assets/website/img/background3.webp') }})no-repeat;" data-overlay="6">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-md-12 text-center">
                    <h2 style='font-weight:bold'> كل ما تحتاجة من خدمة </h2>
                    <h2 class="ipt-title mb-3"> نفذها </br> منصة شاملة لبيع وشراء الخدمات المصغرة والكبيرة للشركات والأفراد
                    </h2>
                    <div class="_ebl_link"><a style="color:#fff" href="#about_us" class="btn _loonking_job"> معرفه المزيد </a>
                    </div>
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
                    <img src="{{ asset('assets/website/img/about_us.svg') }}" alt=""
                        class="img-fluid about_us_image">
                </div>

                <!-- Single Box -->
                <div class="col-lg-6 col-md-6 col-sm-12">
                    <div class="about-captione text-right">
                        <h4> عن نفذها </h4>
                        <br>
                        <h2 style='font-size:20px'> منصتك الموثوقة لتنفيذ المشاريع والخدمات </h2>

                        <p>
                            نفذها هي منصة مبتكرة تربط بين أصحاب المشاريع وأفضل الخبرات المستقلة. سواء كنت تبحث عن مصمم
                            جرافيك محترف، أو مبرمج متمرس، أو كاتب محتوى مبدع، فستجد كل ما تحتاجه في مكان واحد. نحن نوفر لك
                            الحلول التي تحتاجها لتنفيذ مشروعك بنجاح.
                        </p>
                        <h2 style='font-size:20px'> التركيز على الثقة والاحترافية </h2>
                        <p> نفذها هي بيئة عمل آمنة وموثوقة تجمع بين أصحاب الأعمال والمستقلين المحترفين. استعن بخبراتنا
                            الواسعة في إدارة المشاريع وتأكد من حصولك على أفضل النتائج. نحن نضمن لك جودة الخدمات والتسليم في
                            الوقت المحدد </p>
                        <h2 style='font-size:20px'> التركيز على التنوع والمرونة </h2>
                        <p> نفذها هي منصة متنوعة تقدم لك مجموعة واسعة من الخدمات والمهارات. سواء كان مشروعك كبيراً أو
                            صغيراً، طويل الأجل أو قصير الأجل، فلدينا المستقلون المناسبون لتلبية احتياجاتك. استمتع بالمرونة
                            في اختيار فريق عملك وتعاون مع أفضل المواهب </p>
                    </div>
                </div>

            </div>

        </div>
    </section>
    <!-- ========================== About Elements ============================= -->
    <!-- ########################################### Presit Section ######################## -->

    <section class="call-to-act gift_section"
        style="background:#3fb697 url({{ asset('assets/website/img/landing-bg.png') }}) no-repeat">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12">
                    <div class="inner-flexible-box subscribe-box">
                        <img class="animate-pulse" src="{{ asset('assets/website/img/gift.png') }}" alt="">
                    </div>
                    <div class="clt-caption text-center mb-4">
                        <h2 class="text-light">هدية مميزة بانتظارك!</h2>

                        <p class="text-light">


                            بمجرد أن تصل قيمة مشترياتك إلى <strong> 1000 دولار </strong> ، ستُفاجأ برصيد مجاني بقيمة
                            <strong> 50 دولار </strong> سيضاف تلقائيًا إلى حسابك! استمتع بتسوقك معنا واحصل على المزيد.


                        </p>
                    </div>
                </div>
            </div>
        </div>

    </section>

    <!------------------------------------- End Present Section ##################### -->
@endsection
