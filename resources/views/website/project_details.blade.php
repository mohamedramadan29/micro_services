@extends('website.layouts.master')
@section('title')
    {{ $project['title'] }}
@endsection
@section('content')
    <section class="gray-bg text-right" dir="rtl">
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
        <div class="container">
            <div class="main_hero_section">
                <div>
                    <h4> {{ $project['title'] }} </h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="#"> الرئيسية </a></li>
                            <li class="breadcrumb-item"><a href="#"> المشاريع </a></li>
                            <li class="breadcrumb-item active" aria-current="page"> {{ $project['title'] }} </li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <a class="btn btn-global-button" href="{{ url('my/project/add') }}"> اضف مشروعك الان <i
                            class="fa fa-plus"></i> </a>
                </div>
            </div>
            <div class="project_details">
                <div class="row">
                    <div class="col-lg-8">
                        <div class="steps-container">
                            <div class="step active">
                                <div class="step-icon">
                                    <i class="bi bi-check-circle"></i>
                                </div>
                                <p>نشر المشروع</p>
                            </div>
                            <div class="step active">
                                <div class="step-icon">
                                    <i class="bi bi-check-circle"></i>
                                </div>
                                <p>تلقي العروض</p>
                            </div>
                            <div class="step">
                                <div class="step-icon">
                                    <i class="bi bi-circle"></i>
                                </div>
                                <p>تنفيذ المشروع</p>
                            </div>
                            <div class="step">
                                <div class="step-icon">
                                    <i class="bi bi-circle"></i>
                                </div>
                                <p>استلام المشروع</p>
                            </div>
                        </div>

                        <div class="project-details">
                            <h3>تفاصيل المشروع</h3>
                            <p>السلام عليكم<br>عندي استفسار...</p>
                            <div class="project-file">
                                <a href="#">
                                    <i class="bi bi-file-earmark"></i>
                                    <span>1732098919.png</span>
                                </a>
                            </div>
                        </div>
                        <div class="project-skills">
                            <h4>مهارات مطلوبة</h4>
                            <div class="skills">
                                <span>التسويق</span>
                                <span>تسويق الفيسبوك</span>
                            </div>
                        </div>
                        <div class="offer-form">
                            <h3> اضف عرضك الان </h3>
                            @php
                                $public_setting = App\Models\admin\Setting::first();
                                $website_commission = floatval($public_setting['website_commission']);
                            @endphp
                            @if (Auth::check())
                                <form action="{{ url('project/add-offer') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <div class="form-row">
                                        <div class="form-group">
                                            <input type="hidden" name="project_id" value="{{ $project['id'] }}">
                                            <label for="execution_time">مدة التنفيذ (بالأيام)</label>
                                            <input required max="90" min="1" class="form-control"
                                                type="number" id="execution_time" name="execution_time"
                                                placeholder="أدخل عدد الأيام">
                                        </div>
                                        <div class="form-group">
                                            <label for="offer_value">قيمة عرضك (بالدولار)</label>
                                            <input required min="10" max="5000" class="form-control"
                                                type="number" id="offer_value" name="offer_value"
                                                placeholder="أدخل القيمة">
                                        </div>
                                        <div class="form-group">
                                            <label for="earnings">سوف تحصل على (بالدولار)</label>
                                            <input class="form-control" type="text" id="earnings" name="earnings"
                                                disabled value="0">
                                        </div>

                                    </div>

                                    <div class="form-group">
                                        <label for="offer_details">تفاصيل عرضك</label>
                                        <textarea required class="form-control" id="offer_details" name="offer_details" rows="5"
                                            placeholder="تفاصيل العرض" required></textarea>
                                    </div>

                                    <div class="form-group">
                                        <label for="file_upload">إرفاق ملفات</label>
                                        <input class="form-control" type="file" id="file_upload" name="file_upload">
                                    </div>
                                    <button type="submit" class="submit-btn btn global_button">تقديم عرضك</button>
                                    <ul class="form-tips">
                                        <li>لا تستخدم وسائل تواصل خارجية.</li>
                                        <li>لا تضع روابط خارجية، قم بالاهتمام بعرض أعمالك بدلاً منها.</li>
                                        <li><a href="#">اقرأ هنا كيفية تقديم عرض مميز على أي مشروع</a></li>
                                    </ul>
                                </form>
                                <script>
                                    // نسبة العمولة
                                    const websiteCommission = {{ $website_commission }};

                                    // عناصر الحقول
                                    const offerValueInput = document.getElementById('offer_value');
                                    const earningsInput = document.getElementById('earnings');

                                    // تحديث الأرباح ديناميكيًا
                                    offerValueInput.addEventListener('input', function() {
                                        const offerValue = parseFloat(this.value) || 0; // الحصول على قيمة العرض
                                        const websiteCommissionValue = (offerValue * websiteCommission) / 100; // حساب عمولة الموقع
                                        const userEarnings = offerValue - websiteCommissionValue; // الأرباح للمستخدم

                                        // تحديث حقل الأرباح
                                        earningsInput.value = userEarnings.toFixed(2); // تعيين القيمة مع تنسيق عشري
                                    });
                                </script>
                            @else
                                <div class="logins_buttons">
                                    <a href="{{ url('register') }}" class="btn btn-primary"> حساب جديد </a>
                                    <a href="{{ url('login') }}" class="btn btn-outline-primary"> تسجيل دخول </a>
                                </div>
                            @endif

                        </div>
                        <div class="project_offers">
                            <h3> العروض المقدمة </h3>
                            @foreach ($project['Offers'] as $offer)
                                <div class="offer">
                                    <div class="user_info">
                                        <div>
                                            @if ($offer['User']['image'] != '')
                                                <img
                                                    src="{{ asset('assets/uploads/users_image/' . $offer['User']['image']) }}">
                                            @else
                                                <img src="{{ asset('assets/uploads/user.png') }}" alt="">
                                            @endif

                                        </div>
                                        <div>
                                            <a href="{{ url('user/' . $offer['User']['user_name']) }}">
                                                <p> {{ $offer['User']['name'] }} </p>
                                                <span>
                                                    {{ optional($offer->User)->job_title }}
                                                </span>
                                                <span style="display: block"> <i class="bi bi-calendar-fill"></i>
                                                    {{ $project['created_at']->diffForHumans() }} </span>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="proposal">
                                        <p>
                                            @if (Auth::check() && Auth::user()->id == $project['user_id'])
                                                {{ $offer['proposal'] }}
                                            @else
                                                {{ Str::limit($offer['proposal'], 150, '...') }}
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                    </div>
                    <div class="col-lg-4">
                        <div class="project-status">
                            <h3>بطاقة المشروع</h3>
                            <ul>
                                <li><span>حالة المشروع:</span> مفتوح</li>
                                <li><span>تاريخ النشر:</span> منذ 5 ساعات</li>
                                <li><span>المدة المتاحة:</span> 1 أيام</li>
                                <li><span>الميزانية:</span> 10 - 25 $</li>
                                <li><span>عدد المتقدمين:</span> 2</li>
                                <li><span>متوسط العروض:</span> 17 $</li>
                            </ul>
                            <div class="user_info">
                                <div>
                                    <img src="{{ asset('assets/uploads/users_image/' . $project['User']['image']) }}">
                                </div>
                                <div>
                                    <p> {{ $project['User']['name'] }} </p>
                                    <span>
                                        {{ optional($project->User)->job_title }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="project-status">
                            <h3> شارك </h3>
                            <!-- AddToAny BEGIN -->
                            <div class="sharing">
                                <div class="a2a_kit a2a_kit_size_32 a2a_default_style">
                                    <a class="a2a_dd" href="https://www.addtoany.com/share"></a>
                                    <a class="a2a_button_facebook"></a>
                                    <a class="a2a_button_whatsapp"></a>
                                    <a class="a2a_button_telegram"></a>
                                    <a class="a2a_button_linkedin"></a>
                                    <a class="a2a_button_threads"></a>
                                    <a class="a2a_button_x"></a>
                                    <a class="a2a_button_twitter"></a>
                                    <a class="a2a_button_snapchat"></a>
                                </div>
                                <script defer src="https://static.addtoany.com/menu/page.js"></script>
                                <!-- AddToAny END -->
                            </div>

                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
    <!-- ============================ Main Section End ================================== -->
@endsection