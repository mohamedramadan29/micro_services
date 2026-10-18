@extends('website.layouts.master')
@section('title')
    معاينة نموذج {{ $form->subCategory->name }}
@endsection
@section('content')
    <section class="gray-bg text-right rf-page" dir="rtl">
        <div class="container">
            <div class="rf-preview-banner" role="status">
                <i class="bi bi-eye"></i>
                معاينة للأدمن: هذا هو النموذج كما يراه العميل.
                الحالة الآن: <strong>{{ $form->statusLabel() }}</strong>.
                الإرسال معطّل في المعاينة.
            </div>

            <div class="rf-card">
                <div class="rf-card-header">
                    <span class="rf-badge">{{ $form->subCategory->category?->name }}</span>
                    <h1 class="rf-title">اطلب عرضًا لمشروعك</h1>
                    <p class="rf-subtitle">الخدمة: <strong>{{ $form->subCategory->name }}</strong> — الأسئلة تتغيّر حسب الخدمة، وبياناتك تُحفظ مع كل إرسال.</p>
                </div>

                <form onsubmit="return false" novalidate>
                    @include('website.service-requests.partials.form-fields', ['form' => $form, 'client' => Auth::user()])

                    <div class="rf-submit">
                        <p class="rf-submit-title">ماذا تريد الآن؟</p>
                        <div class="rf-submit-actions">
                            <button type="button" class="btn rf-btn-primary" disabled>أريد تنفيذ المشروع</button>
                            <button type="button" class="btn rf-btn-secondary" disabled>ما بدي أنفذ هلأ، بدي أشوف الأسعار</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection
@section('js')
    <script src="{{ \App\Support\AssetVersion::url('assets/website/js/service-request-form.js') }}"></script>
@endsection
