@extends('website.layouts.master')
@section('title')
    سياسة الاستخدام
@endsection
@section('content')
    <!-- End Navigation -->
    <div class="clearfix"></div>
    <!-- ============================ Page Title Start================================== -->
    <div class="page-title" style="height: 400px; text-align: right">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-md-12">

                    <h2 class="ipt-title"> سياسة الاستخدام </h2>


                </div>
            </div>
        </div>
    </div>
    <!-- ============================ Page Title End ================================== -->

    <!-- ============================ Main Section Start ================================== -->
    <section class="min-sec text-right" dir="rtl">
        <div class="container">
            <div class="row">

                <!-- Single blog Grid -->
                <div class="col-lg-10 col-md-11 col-sm-12">
                    <div class="accordion" id="accordionExample">
                        <div class="card">
                            <div class="card-header" id="headingOne">
                                <h2 class="mb-0">
                                    <button class="btn btn-link btn-block text-right" type="button"
                                            data-toggle="collapse" data-target="#collapseOne" aria-expanded="true"
                                            aria-controls="collapseOne">
                                        كيف يحمي خمسات معلوماتي الشخصية؟
                                    </button>
                                </h2>
                            </div>

                            <div id="collapseOne" class="collapse show" aria-labelledby="headingOne"
                                 data-parent="#accordionExample">
                                <div class="card-body">

                                    نضمن لك في خمسات خصوصية معلوماتك ونحرص على حمايتها. لمعرفة المزيد عن ذلك في سياسة
                                    الخصوصية.
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header" id="headingTwo">
                                <h2 class="mb-0">
                                    <button class="btn btn-link btn-block text-right collapsed" type="button"
                                            data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false"
                                            aria-controls="collapseTwo">
                                        كيف أعدل معلومات حسابي؟
                                    </button>
                                </h2>
                            </div>
                            <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo"
                                 data-parent="#accordionExample">
                                <div class="card-body">
                                    تستطيع تغيير معلوماتك الشخصية في صفحة إعدادات حساب حسوب.
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header" id="headingThree">
                                <h2 class="mb-0">
                                    <button class="btn btn-link btn-block text-right collapsed" type="button"
                                            data-toggle="collapse" data-target="#collapseThree" aria-expanded="false"
                                            aria-controls="collapseThree">
                                        أين أجد رابط التسويق بالعمولة الخاص بي وكيف أربح من خلاله؟
                                    </button>
                                </h2>
                            </div>
                            <div id="collapseThree" class="collapse" aria-labelledby="headingThree"
                                 data-parent="#accordionExample">
                                <div class="card-body">
                                    يساعدك برنامج خمسات للتسويق بالعمولة على تحقيق دخل أعلى عبر دعوة أصدقائك لشراء
                                    الخدمات من موقع خمسات. بإمكانك معرفة رابط التسويق بالعمولة الخاص بك وباقي التفاصيل
                                    عبر صفحة التسويق بالعمولة.
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header" id="headingThree">
                                <h2 class="mb-0">
                                    <button class="btn btn-link btn-block text-right collapsed" type="button"
                                            data-toggle="collapse" data-target="#collapseThree" aria-expanded="false"
                                            aria-controls="collapseThree">
                                        لماذا يظهر التوقيت في خمسات مختلفاً عن التوقيت لدي؟
                                    </button>
                                </h2>
                            </div>
                            <div id="collapseThree" class="collapse" aria-labelledby="headingThree"
                                 data-parent="#accordionExample">
                                <div class="card-body">
                                    نعتمد في موقع خمسات توقيت غرينتش GMT كتوقيت افتراضي عالمي.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>
    <!-- ============================ Main Section End ================================== -->
@endsection
