@extends('website.layouts.master')
@section('title')
    ملفي كمستقل
@endsection
@section('content')
    <section class="rf-st-hero rf-st-hero-small" dir="rtl">
        <div class="container">
            <h1>ملفي كمستقل</h1>
            <p>يراجعه فريق نفّذها قبل ظهورك في صفحة المستقلين وفتح العروض لك.</p>
        </div>
    </section>
    <section class="rf-st" dir="rtl">
        <div class="container">
            <div class="rf-st-card">
                <h2 class="rf-st-h2">الحالة:
                    @if ($user->isApprovedFreelancer())
                        <span class="text-success">معتمد</span>
                    @else
                        {{ \App\Models\User::FREELANCER_STATUSES[$user->freelancer_status] ?? 'قيد المراجعة' }}
                    @endif
                </h2>
                <ul class="mb-0">
                    <li>توثيق الحساب:
                        @if ($user->isVerifiedFreelancer())
                            تم ({{ $user->email_verified_at ? 'بريدك الإلكتروني مؤكد' : 'وثّقه فريق نفّذها' }})
                        @else
                            أكّد بريدك الإلكتروني من الرابط الذي أرسلناه إلى {{ $user->email }}.
                            <form method="POST" action="{{ route('email.verify.resend') }}" class="d-inline">@csrf
                                <button class="rf-btn-link">إرسال الرابط مرة أخرى</button></form>
                        @endif
                    </li>
                    <li>الصورة الاحترافية: {{ $user->professional_photo ? 'أضافها الفريق' : 'يضيفها الفريق عند الاعتماد' }}</li>
                    <li>تخصصاتك: {{ $specializations->isNotEmpty() ? $specializations->implode('، ') : 'لم تختر بعد' }} — <a href="{{ route('specializations.edit') }}">تعديل التخصصات</a></li>
                    <li><a href="{{ route('user.portfolio.index') }}">نماذج أعمالك</a> تساعد الفريق على اعتمادك.</li>
                </ul>
                @if ($user->freelancer_status === \App\Models\User::FREELANCER_REJECTED && $user->freelancer_review_note)
                    <div class="rf-notice rf-notice-error mt-3">ملاحظة الفريق: {{ $user->freelancer_review_note }}</div>
                @endif
            </div>

            <form method="POST" action="{{ route('freelancer.profile.update') }}" class="rf-st-card" novalidate>
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="rf-st-label" for="fp-whatsapp">رقم واتساب <span class="rf-st-tag">مطلوب</span></label>
                        <input type="tel" id="fp-whatsapp" name="whatsapp" data-country-picker class="form-control" dir="ltr" maxlength="30" value="{{ old('whatsapp', $user->whatsapp) }}" placeholder="+20 10X XXX XXXX">
                        @error('whatsapp')<div class="rf-field-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="rf-st-label" for="fp-availability">حالة التوفر</label>
                        <select id="fp-availability" name="availability" class="form-control">
                            @foreach (\App\Models\User::AVAILABILITY as $key => $label)
                                <option value="{{ $key }}" @selected(old('availability', $user->availability ?? 'available') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="rf-st-label" for="fp-bio">نبذة قصيرة <span class="rf-st-tag">مطلوب</span></label>
                        <textarea id="fp-bio" name="bio" rows="3" class="form-control" maxlength="500" placeholder="ماذا تقدم؟ وما خبرتك؟">{{ old('bio', $user->bio) }}</textarea>
                        @error('bio')<div class="rf-field-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="rf-st-label" for="fp-languages">اللغات</label>
                        <input type="text" id="fp-languages" name="languages" class="form-control" maxlength="255" value="{{ old('languages', $user->languages) }}" placeholder="العربية، الإنجليزية">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="rf-st-label" for="fp-price">يبدأ سعري من (ج.م)</label>
                        <input type="number" id="fp-price" name="starting_price" min="0" step="1" class="form-control" value="{{ old('starting_price', $user->starting_price ? (int) $user->starting_price : '') }}">
                        @error('starting_price')<div class="rf-field-error">{{ $message }}</div>@enderror
                    </div>
                </div>
                <button class="rf-sv-btn rf-sv-btn-primary">{{ $user->freelancer_status === \App\Models\User::FREELANCER_REJECTED ? 'حفظ وإعادة الإرسال للمراجعة' : 'حفظ' }}</button>
            </form>
        </div>
    </section>
@endsection
