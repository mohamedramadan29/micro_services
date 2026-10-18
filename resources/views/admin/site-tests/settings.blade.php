@extends('admin.layouts.master')
@php use App\Services\SiteTests\SiteTestSettings; use App\Models\SiteTest; @endphp
@section('title')
    إعداد الفحص
@endsection
@section('content')
    <div class="breadcrumb-header justify-content-between">
        <div class="my-auto">
            <div class="d-flex">
                <h4 class="content-title mb-0 my-auto">إعداد الفحص</h4>
                <span class="text-muted mt-1 tx-13 mr-2 mb-0">/ اختبر موقعك مجاناً</span>
            </div>
        </div>
        <div class="my-auto d-flex" style="gap: 6px">
            <a href="{{ route('admin.content.index', ['page' => 'sitetest']) }}" class="btn btn-light">نصوص الصفحة والبطاقة</a>
            <a href="{{ route('admin.site-tests.index') }}" class="btn btn-light"><i class="fas fa-arrow-right"></i> اختبارات المواقع</a>
        </div>
    </div>
    @include('admin.catalog.partials.flash')

    <div class="row row-sm">
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header"><h6 class="mb-0">إعدادات التشغيل</h6></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.site-tests.settings.save') }}">
                        @csrf
                        <label class="d-block"><input type="checkbox" name="enabled" value="1" @checked($settings['enabled'])> الخدمة مفعّلة</label>
                        <p class="text-muted small mb-2">مصدر الفحص: Google PageSpeed Insights API {{ config('services.pagespeed.key') ? '(المفتاح مضبوط)' : '— المفتاح غير مضبوط في .env (PAGESPEED_API_KEY)' }}</p>
                        <div class="form-group">
                            <label>مجالات التقرير التي يختار منها العميل</label>
                            @foreach (SiteTestSettings::CATEGORIES as $key => $category)
                                <label class="d-block"><input type="checkbox" name="categories[]" value="{{ $key }}" @checked(in_array($key, $settings['categories'], true))> {{ $category['label'] }}</label>
                            @endforeach
                        </div>
                        <div class="form-group">
                            <label>الأجهزة المتاحة</label>
                            @foreach (SiteTest::DEVICES as $key => $label)
                                <label class="d-block"><input type="checkbox" name="devices[]" value="{{ $key }}" @checked(in_array($key, $settings['devices'], true))> {{ $label }}</label>
                            @endforeach
                        </div>
                        <div class="form-group">
                            <label>أقصى عدد مشكلات تظهر للعميل (1 إلى 3)</label>
                            <input type="number" name="max_issues" min="1" max="3" class="form-control" value="{{ $settings['max_issues'] }}">
                        </div>
                        <div class="form-group">
                            <label>حد الفحوصات لكل زائر في الساعة</label>
                            <input type="number" name="hourly_limit" min="1" max="100" class="form-control" value="{{ $settings['hourly_limit'] }}">
                        </div>
                        <div class="form-group">
                            <label>مدة حفظ نتائج الفحص بالأيام (0 = للأبد)</label>
                            <input type="number" name="retention_days" min="0" max="3650" class="form-control" value="{{ $settings['retention_days'] }}">
                            <small class="text-muted">بعدها تُحذف الفحوصات التي لم يترك صاحبها بياناته ولم ترتبط بحساب أو طلب، وتُحذف البيانات الخام فقط من الباقي (تبقى الدرجات والمشكلات).</small>
                        </div>
                        <label class="d-block"><input type="checkbox" name="show_offer_immediately" value="1" @checked($settings['show_offer_immediately'])> إظهار بطاقة العرض فور انتهاء الفحص (وإلا بعد إرسال التقرير)</label>
                        <div class="form-group mt-2">
                            <label>رسالة فشل المحرك أو نقص البيانات</label>
                            <textarea name="failure_message" rows="3" class="form-control">{{ $settings['failure_message'] }}</textarea>
                        </div>
                        <button class="btn btn-primary">حفظ</button>
                    </form>
                </div>
            </div>

            <div class="card" id="questions">
                <div class="card-header"><h6 class="mb-0">أسئلة نموذج «التقرير الكامل»</h6></div>
                <div class="card-body">
                    <p class="text-muted small">تظهر للعميل مع بيانات التواصل عند طلب التقرير الكامل، وتصل إجاباته في صفحة الفحص وفي الطلب المنشور للمستقلين. اترك العنوان فارغًا في السطر الأخير لعدم الإضافة. خيارات القائمة: كل خيار في سطر.</p>
                    <form method="POST" action="{{ route('admin.site-tests.questions.save') }}">
                        @csrf
                        @php $questionRows = array_merge((array) $settings['questions'], [['key' => '', 'label' => '', 'type' => 'text', 'options' => [], 'required' => false, 'active' => true]]); @endphp
                        @foreach ($questionRows as $i => $question)
                            <div class="border rounded p-2 mb-2 {{ $question['key'] === '' ? 'bg-light' : '' }}">
                                <input type="hidden" name="questions[{{ $i }}][key]" value="{{ $question['key'] }}">
                                <div class="form-row">
                                    <div class="col-7 mb-1"><input type="text" name="questions[{{ $i }}][label]" class="form-control" value="{{ $question['label'] }}" placeholder="{{ $question['key'] === '' ? 'سؤال جديد…' : 'نص السؤال' }}"></div>
                                    <div class="col-5 mb-1">
                                        <select name="questions[{{ $i }}][type]" class="form-control">
                                            @foreach (SiteTestSettings::QUESTION_TYPES as $type => $typeLabel)<option value="{{ $type }}" @selected($question['type'] === $type)>{{ $typeLabel }}</option>@endforeach
                                        </select>
                                    </div>
                                </div>
                                <textarea name="questions[{{ $i }}][options]" rows="2" class="form-control mb-1" placeholder="خيارات القائمة (لنوع «اختيار» فقط)">{{ implode("\n", (array) $question['options']) }}</textarea>
                                <div class="form-row mb-1">
                                    <div class="col-3"><input type="number" name="questions[{{ $i }}][order]" min="0" max="99" class="form-control form-control-sm" value="{{ $i }}" title="الترتيب"></div>
                                    <div class="col-5">
                                        <select name="questions[{{ $i }}][show_if]" class="form-control form-control-sm" title="يظهر فقط إذا">
                                            <option value="">يظهر دائمًا</option>
                                            @foreach ((array) $settings['questions'] as $other)
                                                @continue($other['key'] === $question['key'])
                                                <option value="{{ $other['key'] }}" @selected(($question['show_if'] ?? null) === $other['key'])>إذا أجاب «{{ $other['label'] }}» بـ</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-4"><input type="text" name="questions[{{ $i }}][show_value]" class="form-control form-control-sm" value="{{ $question['show_value'] ?? '' }}" placeholder="القيمة (مثل: متجر)"></div>
                                </div>
                                <label class="mr-2"><input type="checkbox" name="questions[{{ $i }}][active]" value="1" @checked(! empty($question['active']))> ظاهر</label>
                                <label class="mr-2"><input type="checkbox" name="questions[{{ $i }}][required]" value="1" @checked(! empty($question['required']))> مطلوب</label>
                                @if ($question['key'] !== '')<label class="mr-2 text-danger"><input type="checkbox" name="questions[{{ $i }}][delete]" value="1"> حذف</label>@endif
                            </div>
                        @endforeach
                        <button class="btn btn-primary">حفظ الأسئلة</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card">
                <div class="card-header"><h6 class="mb-0">ربط النتائج بالخدمات</h6></div>
                <div class="card-body">
                    <p class="text-muted small">اربط رمز كل نتيجة يعرفها المحرك بالخدمة التي تحلها. البطاقة تعرض على العميل خدمة أهم مشكلة لها قاعدة؛ المشكلات بلا قاعدة تبقى لمراجعتك. لا تضف قاعدة لا يقابلها فحص فعلي.</p>
                    <form method="POST" action="{{ route('admin.site-tests.rules.save') }}" class="row align-items-end">
                        @csrf
                        <div class="form-group col-md-4">
                            <label>رمز النتيجة</label>
                            <input type="text" name="audit_code" class="form-control" dir="ltr" list="known-codes" placeholder="meta-description" value="{{ old('audit_code') }}">
                            <datalist id="known-codes">
                                @foreach ($knownCodes as $code)<option value="{{ $code }}">@endforeach
                            </datalist>
                        </div>
                        <div class="form-group col-md-4">
                            <label>الخدمة</label>
                            <select name="sub_category_id" class="form-control">
                                <option value="">— اختر —</option>
                                @foreach ($services as $service)
                                    <option value="{{ $service->id }}" @selected((int) old('sub_category_id') === $service->id)>{{ $service->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-2">
                            <label>الأولوية</label>
                            <select name="priority" class="form-control"><option value="high">عالية</option><option value="medium" selected>متوسطة</option></select>
                        </div>
                        <div class="form-group col-md-2"><button class="btn btn-primary btn-block">إضافة</button></div>
                        <div class="form-group col-12 mb-0">
                            <input type="text" name="label" class="form-control" maxlength="255" placeholder="اسم وصفي للنتيجة (اختياري) مثل: وصف الصفحة مفقود">
                        </div>
                    </form>
                    <div class="table-responsive mt-3">
                        <table class="table table-bordered mb-0">
                            <thead><tr><th>رمز النتيجة</th><th>الوصف</th><th>الخدمة</th><th>الأولوية</th><th></th></tr></thead>
                            <tbody>
                                @forelse ($rules as $rule)
                                    <tr>
                                        <td dir="ltr">{{ $rule->audit_code }}</td>
                                        <td>{{ $rule->label }}</td>
                                        <td>{{ $rule->service?->name }}</td>
                                        <td>{{ \App\Models\SiteTestIssue::PRIORITIES[$rule->priority] ?? $rule->priority }}</td>
                                        <td>
                                            <form method="POST" action="{{ route('admin.site-tests.rules.destroy', $rule) }}" onsubmit="return confirm('حذف القاعدة؟')">@csrf<button class="btn btn-sm btn-outline-danger">حذف</button></form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted">لا توجد قواعد بعد. بدون قواعد لا تظهر بطاقة العرض إلا إذا اخترت الخدمة يدويًا في صفحة الفحص.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
