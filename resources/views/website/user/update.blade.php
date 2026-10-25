@extends('website.layouts.master')
@section('title')
    تعديل الحساب
@endsection
@section('content')
    @use('App\Models\User')
    @php
        $type = old('account_type', $user->account_type);
        $types = [
            User::TYPE_CLIENT => ['عميل', 'أطلب خدمات وأستقبل عروض المستقلين'],
            User::TYPE_FREELANCER => ['مستقل', 'أنفّذ الخدمات وأقدّم العروض على طلبات العملاء'],
            User::TYPE_EMPLOYEE => ['موظف', 'أبحث عن وظيفة'],
        ];
    @endphp

    <section class="gray-bg pt-4 text-right profile_page" dir="rtl">
        <div class="container-fluid">
            <div class="row m-0">
                <div class="col-xl-3 col-lg-4 col-md-12 col-sm-12">
                    @include('website.service-requests.partials.dashboard-nav')
                </div>

                <div class="col-xl-9 col-lg-8 col-md-12 col-sm-12">
                    <div class="bredcrumb_wrap">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ url('dashboard') }}"> لوحتي </a></li>
                                <li class="breadcrumb-item active" aria-current="page"> تعديل الحساب</li>
                            </ol>
                        </nav>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="post" enctype="multipart/form-data" action="{{ url('update-account') }}" autocomplete="off" class="rf-acc-form">
                        @csrf
                        <div class="_dashboard_content">
                            <div class="_dashboard_content_header">
                                <div class="_dashboard__header_flex">
                                    <h4><i class="fa fa-user mr-1"></i> معلومات الحساب </h4>
                                </div>
                            </div>
                            <div class="_dashboard_content_body">
                                <div class="row">
                                    <div class="col-auto">
                                        <div class="custom-file avater_uploads">
                                            <img src="{{ $user->image ? $user->avatarUrl(256) : asset('assets/website/img/avatar.png') }}" class="img-fluid rounded" alt="">
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="row">
                                            <div class="col-xl-12">
                                                <div class="form-group">
                                                    <label> الصورة الشخصية </label>
                                                    <input type="file" name="image" accept="image/jpeg,image/png,image/webp" class="form-control with-light">
                                                    @if ($user->isFreelancer())
                                                        <small class="text-muted d-block mt-1">صورتك الاحترافية التي تظهر للعملاء: صورة واضحة لوجهك، 400×400 بكسل على الأقل. يراجعها فريق نفّذها مع اعتماد حسابك.</small>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col-xl-6">
                                                <div class="form-group">
                                                    <label> الاسم </label>
                                                    <input required type="text" name="name" class="form-control with-light" value="{{ old('name', $user->name) }}">
                                                </div>
                                            </div>
                                            <div class="col-xl-6">
                                                <div class="form-group">
                                                    <label> البريد الإلكتروني </label>
                                                    <input dir="ltr" required type="email" class="form-control with-light" name="email" value="{{ old('email', $user->email) }}">
                                                    @if ($user->email_verified_at)
                                                        <small class="text-success d-block mt-1"><i class="bi bi-patch-check-fill"></i> مؤكد</small>
                                                    @else
                                                        <small class="text-warning d-block mt-1">غير مؤكد — راجع بريدك أو <button type="submit" form="rf-resend-verify" class="rf-acc-linkbtn">أعد إرسال رابط التأكيد</button></small>
                                                    @endif
                                                    <small class="text-muted d-block">تغيير البريد يحتاج تأكيدًا جديدًا.</small>
                                                </div>
                                            </div>
                                            <div class="col-xl-6">
                                                <div class="form-group">
                                                    <label> رقم الهاتف </label>
                                                    <input required type="tel" dir="ltr" data-country-picker class="form-control with-light" name="phone" value="{{ old('phone', $user->phone) }}">
                                                </div>
                                            </div>
                                            <div class="col-xl-6">
                                                <div class="form-group">
                                                    <label> المسمى الوظيفي <small class="text-muted">(اختياري)</small></label>
                                                    <input type="text" class="form-control with-light" name="job_title" maxlength="100" value="{{ old('job_title', $user->job_title) }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if ($user->account_type !== User::TYPE_ADMIN)
                            <div class="_dashboard_content">
                                <div class="_dashboard_content_header">
                                    <div class="_dashboard__header_flex">
                                        <h4><i class="bi bi-person-gear mr-1"></i> نوع الحساب </h4>
                                    </div>
                                </div>
                                <div class="_dashboard_content_body">
                                    <div class="rf-acc-types">
                                        @foreach ($types as $value => [$label, $hint])
                                            <label class="rf-acc-type {{ $type === $value ? 'on' : '' }}">
                                                <input type="radio" name="account_type" value="{{ $value }}" @checked($type === $value) required>
                                                <strong>{{ $label }}</strong>
                                                <small>{{ $hint }}</small>
                                            </label>
                                        @endforeach
                                    </div>
                                    @if (! $user->isFreelancer())
                                        <p class="text-muted small mt-2 mb-0" id="rf-to-freelancer" @if ($type !== User::TYPE_FREELANCER) hidden @endif>
                                            عند التحويل إلى «مستقل» يراجع فريق نفّذها ملفك قبل أن تقدّم عروضًا.
                                        </p>
                                    @endif
                                    <div class="form-group mt-3" id="rf-acc-whatsapp" @if ($type !== User::TYPE_FREELANCER) hidden @endif>
                                        <label> رقم واتساب @if (! $user->whatsapp)<span class="text-danger">*</span>@endif</label>
                                        <input type="tel" dir="ltr" data-country-picker maxlength="30" class="form-control with-light" name="whatsapp"
                                               value="{{ old('whatsapp', $user->whatsapp) }}" placeholder="10X XXX XXXX">
                                        <small class="text-muted d-block mt-1">رقم عليه واتساب (اختر رمز الدولة من القائمة)، للتواصل معك من فريق نفّذها فقط.</small>
                                    </div>
                                </div>
                            </div>
                        @else
                            <input type="hidden" name="account_type" value="{{ User::TYPE_CLIENT }}">
                        @endif

                        <div class="_dashboard_content">
                            <div class="_dashboard_content_header">
                                <div class="_dashboard__header_flex">
                                    <h4><i class="ti-settings mr-1"></i> نبذة عنك </h4>
                                </div>
                            </div>
                            <div class="_dashboard_content_body">
                                <div class="form-group mb-0">
                                    <label> {{ $user->isFreelancer() ? 'نبذة عنك (تظهر للعملاء في ملفك)' : 'النبذة التعريفية' }} @unless ($user->isFreelancer())<small class="text-muted">(اختياري)</small>@endunless</label>
                                    <textarea name="info" rows="3" maxlength="1000" class="form-control with-light">{{ old('info', $user->isFreelancer() ? ($user->bio ?: $user->info) : $user->info) }}</textarea>
                                    @if ($user->isFreelancer())
                                        <small class="text-muted d-block mt-1">ماذا تقدّم؟ وما خبرتك؟ يراجعها فريق نفّذها مع اعتماد حسابك.</small>
                                    @endif
                                </div>
                            </div>
                        </div>

                        @if ($user->isFreelancer())
                            @php
                                $fChecklist = $user->freelancerChecklist();
                                $fSpecs = $user->specializations()->orderBy('name')->pluck('name');
                                $fRejected = $user->freelancer_status === User::FREELANCER_REJECTED;
                            @endphp
                            <div class="_dashboard_content" id="rf-freelancer">
                                <div class="_dashboard_content_header">
                                    <div class="_dashboard__header_flex">
                                        <h4><i class="bi bi-person-badge"></i> ملفك كمستقل </h4>
                                        @if ($user->isApprovedFreelancer())
                                            <span class="rf-acc-pill stamp">معتمد</span>
                                        @elseif ($fRejected)
                                            <span class="rf-acc-pill bad">غير معتمد</span>
                                        @else
                                            <span class="rf-acc-pill warn">قيد المراجعة</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="_dashboard_content_body">
                                    @if ($fRejected && $user->freelancer_review_note)
                                        <div class="rf-notice rf-notice-error mb-3"><strong>ملاحظة الفريق:</strong> {{ $user->freelancer_review_note }} — عدّل ملفك واحفظه ليُراجع من جديد.</div>
                                    @elseif (! $user->isApprovedFreelancer())
                                        <p class="text-muted small">يراجع فريق نفّذها ملفك قبل ظهورك في صفحة المستقلين وفتح العروض لك.</p>
                                    @endif
                                    <ul class="rf-fp-checks">
                                        <li class="{{ $fChecklist['email'] ? 'ok' : '' }}"><i class="bi {{ $fChecklist['email'] ? 'bi-check-circle-fill' : 'bi-circle' }}"></i>
                                            توثيق الحساب: {{ $user->isVerifiedFreelancer() ? ($user->email_verified_at ? 'بريدك مؤكد' : 'وثّقه فريق نفّذها') : 'أكّد بريدك من الرابط المرسل إليك' }}</li>
                                        <li class="{{ $fChecklist['photo'] ? 'ok' : '' }}"><i class="bi {{ $fChecklist['photo'] ? 'bi-check-circle-fill' : 'bi-circle' }}"></i>
                                            الصورة الاحترافية: {{ $fChecklist['photo'] ? 'تمت إضافتها' : 'ارفعها من «الصورة الشخصية» بالأعلى' }}</li>
                                        <li class="{{ $fChecklist['profile'] ? 'ok' : '' }}"><i class="bi {{ $fChecklist['profile'] ? 'bi-check-circle-fill' : 'bi-circle' }}"></i>
                                            نبذة عنك: {{ $fChecklist['profile'] ? 'مكتوبة' : 'اكتبها في «نبذة عنك» بالأسفل' }}</li>
                                        <li class="{{ $fChecklist['specializations'] ? 'ok' : '' }}"><i class="bi {{ $fChecklist['specializations'] ? 'bi-check-circle-fill' : 'bi-circle' }}"></i>
                                            تخصصاتك: {{ $fSpecs->isNotEmpty() ? $fSpecs->implode('، ') : 'لم تختر بعد' }} — <a href="{{ route('specializations.edit') }}">تعديل</a></li>
                                        <li class="{{ $fChecklist['portfolio'] ? 'ok' : '' }}"><i class="bi {{ $fChecklist['portfolio'] ? 'bi-check-circle-fill' : 'bi-circle' }}"></i>
                                            نماذج أعمالك تساعد الفريق على اعتمادك — <a href="{{ route('user.portfolio.index') }}">معرض أعمالي</a></li>
                                    </ul>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="fp-availability"> حالة التوفر </label>
                                                <select id="fp-availability" name="availability" class="form-control with-light">
                                                    @foreach (User::AVAILABILITY as $key => $label)
                                                        <option value="{{ $key }}" @selected(old('availability', $user->availability ?? 'available') === $key)>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="fp-languages"> اللغات <small class="text-muted">(اختياري)</small></label>
                                                <input type="text" id="fp-languages" name="languages" maxlength="255" class="form-control with-light" value="{{ old('languages', $user->languages) }}" placeholder="العربية، الإنجليزية">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group mb-0">
                                                <label for="fp-price"> يبدأ سعري من (ج.م) <small class="text-muted">(اختياري)</small></label>
                                                <input type="number" id="fp-price" name="starting_price" min="0" step="1" dir="ltr" class="form-control with-light" value="{{ old('starting_price', $user->starting_price ? (int) $user->starting_price : '') }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="_dashboard_content">
                            <div class="_dashboard_content_header">
                                <div class="_dashboard__header_flex">
                                    <h4><i class="ti-lock mr-1"></i> تغيير كلمة المرور <small class="text-muted">(اتركها فارغة إن لم ترد التغيير)</small></h4>
                                </div>
                            </div>
                            <div class="_dashboard_content_body">
                                <div class="row">
                                    <div class="col-xl-4">
                                        <div class="form-group">
                                            <label> كلمة المرور الحالية </label>
                                            <input type="password" name="old_password" autocomplete="off" class="form-control with-light">
                                        </div>
                                    </div>
                                    <div class="col-xl-4">
                                        <div class="form-group">
                                            <label> كلمة المرور الجديدة </label>
                                            <input type="password" name="new_password" autocomplete="new-password" minlength="8" class="form-control with-light">
                                        </div>
                                    </div>
                                    <div class="col-xl-4">
                                        <div class="form-group">
                                            <label> تأكيد كلمة المرور </label>
                                            <input type="password" name="confirm_password" autocomplete="new-password" class="form-control with-light">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-save btn-primary"> {{ $user->isFreelancer() && $user->freelancer_status === User::FREELANCER_REJECTED ? 'حفظ وإعادة الإرسال للمراجعة' : 'حفظ التغييرات' }} <i class="fa fa-save"></i></button>
                    </form>
                    <form method="post" action="{{ route('email.verify.resend') }}" id="rf-resend-verify" hidden>@csrf</form>
                </div>
            </div>
        </div>
    </section>

    <script>
        (function () {
            var radios = document.querySelectorAll('.rf-acc-types input[name="account_type"]');
            var whatsapp = document.getElementById('rf-acc-whatsapp');
            var note = document.getElementById('rf-to-freelancer');
            radios.forEach(function (radio) {
                radio.addEventListener('change', function () {
                    var freelancer = radio.value === @json(User::TYPE_FREELANCER);
                    document.querySelectorAll('.rf-acc-type').forEach(function (l) { l.classList.toggle('on', l.contains(radio)); });
                    if (whatsapp) whatsapp.hidden = !freelancer;
                    if (note) note.hidden = !freelancer;
                });
            });
        })();
    </script>
@endsection
