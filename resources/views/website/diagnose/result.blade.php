@extends('website.layouts.master')
@php
    $page = \App\Support\SiteContent::first('diagnose.page');
    $withFrom = fn (?string $url) => $url ? $url . (str_contains($url, '?') ? '&' : '?') . 'from=diagnose' : null;
@endphp
@section('title')
    {{ $page['result_title'] }}
@endsection
@section('content')
    <section class="gray-bg text-right rf-page" dir="rtl">
        <div class="container">
            <div class="rf-card rf-dx">
                <div class="rf-card-header">
                    @if ($page['badge'])<span class="rf-badge">{{ $page['badge'] }}</span>@endif
                    <h1 class="rf-title">{{ $page['result_title'] }}</h1>
                </div>

                <dl class="rf-answers">
                    <dt>هدفك</dt><dd>{{ $group?->name }}</dd>
                    <dt>الخدمة المقترحة</dt><dd>{{ $service?->name ?? 'لم تحدد بعد — سيساعدك فريقنا' }}</dd>
                    @if ($diagnosis['description'])<dt>وصفك</dt><dd class="rf-problem">{{ $diagnosis['description'] }}</dd>@endif
                    @if ($diagnosis['urgency'])<dt>التوقيت</dt><dd>{{ $urgency[$diagnosis['urgency']] ?? '' }}</dd>@endif
                </dl>

                @if ($service)
                    <p class="rf-muted">راجع الملخص ثم اختر كيف تكمل. ما كتبته يُنقل معك ويمكنك تعديله.</p>
                    <div class="rf-dx-next">
                        @if ($requestUrl)
                            <a href="{{ $withFrom($requestUrl) }}" class="btn rf-btn-primary"><i class="bi bi-ui-checks"></i> اطلب عرضًا لـ«{{ $service->name }}»</a>
                        @endif
                        <a href="{{ route('projects.new', ['service' => $service->id]) }}" class="btn rf-btn-secondary"><i class="bi bi-plus-square"></i> اكتب مشروعك وانشره مباشرة</a>
                        <a href="{{ route('services.index', ['help' => 1, 'service' => $service->id]) }}" class="btn rf-btn-outline"><i class="bi bi-life-preserver"></i> ساعدوني أختار</a>
                    </div>
                @else
                    <p>{{ $page['result_unsure'] }}</p>
                    <div class="rf-dx-next">
                        <a href="{{ route('services.index', ['help' => 1]) }}" class="btn rf-btn-primary"><i class="bi bi-life-preserver"></i> اطلب مساعدة فريق نفّذها</a>
                        <a href="{{ route('projects.new') }}" class="btn rf-btn-secondary"><i class="bi bi-plus-square"></i> اكتب مشروعك وانشره مباشرة</a>
                    </div>
                @endif
                <p class="mt-3 mb-0"><a href="{{ route('diagnose') }}" class="rf-btn-link">أعد التشخيص</a></p>
            </div>
        </div>
    </section>
@endsection
