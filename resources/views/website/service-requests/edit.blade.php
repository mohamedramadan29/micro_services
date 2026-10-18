@extends('website.layouts.master')
@section('title')
    تعديل الطلب {{ $serviceRequest->number }}
@endsection
@section('content')
    <section class="gray-bg text-right rf-page" dir="rtl">
        <div class="container">
            <div class="rf-card">
                <div class="rf-card-header">
                    <span class="rf-badge">{{ $serviceRequest->number }} · {{ $serviceRequest->service_name }}</span>
                    <h1 class="rf-title">تعديل الطلب</h1>
                    <p class="rf-subtitle">عدّل ما تريد ثم احفظ. يبقى طلبك {{ $serviceRequest->isInquiry() ? 'استفسارًا خاصًا لدى فريقنا' : 'منشورًا للمستقلين' }} كما هو.</p>
                </div>

                @if ($offersCount > 0)
                    <div class="rf-notice rf-notice-warning">
                        وصلك {{ $offersCount }} {{ $offersCount === 1 ? 'عرض' : 'عروض' }} على هذا الطلب. سنُعلم أصحابها بالتعديل ليراجعوا عروضهم، وتظهر لهم علامة «الطلب عُدّل بعد عرضك».
                    </div>
                @endif
                @if ($errors->any())
                    <div class="rf-notice rf-notice-error" role="alert"><strong>راجع الحقول المظللة بالأسفل.</strong> إذا كنت أرفقت ملفات جديدة، أعد إرفاقها قبل الحفظ.</div>
                @endif

                <form method="POST" action="{{ route('requests.update', $serviceRequest) }}" enctype="multipart/form-data" id="request-form">
                    @csrf
                    @if ($serviceRequest->isStaged())
                        @include('website.service-requests.partials.staged-fields')
                    @endif
                    @include('website.service-requests.partials.form-fields', ['form' => $form, 'client' => $client])
                    <div class="rf-submit">
                        <div class="rf-submit-actions">
                            <button type="submit" class="btn rf-btn-primary">حفظ التعديلات</button>
                            <a href="{{ route('requests.show', $serviceRequest) }}" class="btn rf-btn-secondary">رجوع دون حفظ</a>
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
