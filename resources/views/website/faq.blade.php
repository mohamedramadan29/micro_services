@extends('website.layouts.master')
@section('title')  الاسئلة الشائعه  @endsection
@section('content')
    <!-- End Navigation -->
    <div class="clearfix"></div>
    <!-- ============================ Page Title Start================================== -->
    <div class="page-title" style="height: 400px; text-align: right">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-md-12">

                    <h2 class="ipt-title"> الاسئلة الشائعة </h2>


                </div>
            </div>
        </div>
    </div>
    <!-- ============================ Page Title End ================================== -->

    <!-- ============================ Main Section Start ================================== -->
    <section class="min-sec text-right faq_section" dir="rtl">
        <div class="container">
            <div class="row">

                <!-- Single blog Grid -->
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <div class="accordion" id="accordionPanelsStayOpenExample">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="panelsStayOpen-headingOne">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseOne" aria-expanded="true" aria-controls="panelsStayOpen-collapseOne">
                                    ما هو موقع نفذها؟

                                </button>
                            </h2>
                            <div id="panelsStayOpen-collapseOne" class="accordion-collapse collapse show" aria-labelledby="panelsStayOpen-headingOne">
                                <div class="accordion-body">
                                    نفذها هو منصة إلكترونية تجمع بين أصحاب المشاريع والمستقلين لتنفيذ الخدمات بمهنية واحترافية. يوفر الموقع بيئة آمنة للتواصل وإتمام المشاريع، ويغطي مجموعة متنوعة من الخدمات التي تشمل التصميم، البرمجة، التسويق، الترجمة، وغيرها.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="panelsStayOpen-headingTwo">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseTwo" aria-expanded="false" aria-controls="panelsStayOpen-collapseTwo">
                                    كيف يمكنني التسجيل في موقع نفذها؟
                                </button>
                            </h2>
                            <div id="panelsStayOpen-collapseTwo" class="accordion-collapse collapse" aria-labelledby="panelsStayOpen-headingTwo">
                                <div class="accordion-body">
                                    يمكنك التسجيل بكل سهولة من خلال النقر على زر "التسجيل" في أعلى الصفحة الرئيسية. اختر ما إذا كنت ترغب في التسجيل كصاحب مشروع أو كمستقل، واملأ البيانات المطلوبة.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="panelsStayOpen-headingThree">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseThree" aria-expanded="false" aria-controls="panelsStayOpen-collapseThree">
                                    كيف أبدأ في توظيف مستقل لتنفيذ مشروعي؟
                                </button>
                            </h2>
                            <div id="panelsStayOpen-collapseThree" class="accordion-collapse collapse" aria-labelledby="panelsStayOpen-headingThree">
                                <div class="accordion-body">
                                    بعد التسجيل في الموقع، يمكنك استعراض ملفات المستقلين وتقييماتهم، أو نشر مشروعك ليقوم المستقلون بتقديم عروضهم عليه. بمجرد اختيار المستقل المناسب، يمكنك التواصل معه لتحديد متطلبات المشروع.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="panelsStayOpen-heading4">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapse4" aria-expanded="false" aria-controls="panelsStayOpen-collapse4">
                                    كيف يمكنني ضمان جودة العمل على نفذها؟
                                </button>
                            </h2>
                            <div id="panelsStayOpen-collapse4" class="accordion-collapse collapse" aria-labelledby="panelsStayOpen-heading4">
                                <div class="accordion-body">
                                    يعتمد نفذها على آلية التقييم والتعليقات من أصحاب المشاريع السابقين، مما يتيح لك الاطلاع على أداء المستقلين السابق. كما يمكنك متابعة تقدم المشروع عبر المنصة لضمان تحقيق الجودة المطلوبة.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="panelsStayOpen-heading5">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapse5" aria-expanded="false" aria-controls="panelsStayOpen-collapse5">
                                    هل يوجد نظام دفع آمن في نفذها؟
                                </button>
                            </h2>
                            <div id="panelsStayOpen-collapse5" class="accordion-collapse collapse" aria-labelledby="panelsStayOpen-heading5">
                                <div class="accordion-body">
                                    نعم، يوفر نفذها نظام دفع آمن يضمن حقوق الطرفين. يتم تحويل الأموال إلى المستقل بعد تأكيد صاحب المشروع إتمام العمل بنجاح.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="panelsStayOpen-heading6">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapse6" aria-expanded="false" aria-controls="panelsStayOpen-collapse6">
                                    ما هي أنواع المشاريع التي يمكنني طلبها على الموقع؟
                                </button>
                            </h2>
                            <div id="panelsStayOpen-collapse6" class="accordion-collapse collapse" aria-labelledby="panelsStayOpen-heading6">
                                <div class="accordion-body">
                                    يمكنك طلب مشاريع في مختلف المجالات، من التصميم إلى البرمجة والتسويق وكتابة المحتوى والترجمة والمزيد. يمكنك تصفح أقسام الخدمات لاختيار المجال المناسب لمشروعك.
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
