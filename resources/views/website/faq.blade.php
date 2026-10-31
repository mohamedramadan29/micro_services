@extends('website.layouts.master')
@php
    $faq = \App\Support\SiteContent::first('faq.page');
    $faqItems = \App\Support\SiteContent::items('faq.items');
@endphp
@section('title'){{ $faq['title'] }} | نفّذها@endsection
@section('content')
{{-- Questions are managed in admin «إدارة محتوى الواجهة». --}}
<section class="rf-pp rf-faq" dir="rtl">
    <div class="container">
        <nav class="rf-pp-trail" aria-label="مسار الصفحة">
            <a href="{{ url('/') }}">الرئيسية</a> <span aria-hidden="true">‹</span> <span>{{ $faq['title'] }}</span>
        </nav>
        <header class="rf-sl-head">
            <div>
                <h1>{{ $faq['title'] }}</h1>
                @if ($faq['text'])<p style="white-space: pre-line">{{ $faq['text'] }}</p>@endif
            </div>
        </header>

        <div class="rf-faq-list">
            @foreach ($faqItems as $i => $item)
                <details class="rf-faq-item" @if ($i === 0) open @endif>
                    <summary>{{ $item['question'] }}</summary>
                    <div class="rf-faq-answer">{{ $item['answer'] }}</div>
                </details>
            @endforeach
        </div>

        <div class="rf-pp-card rf-wk-cta">
            <div>
                <strong>لم تجد إجابة سؤالك؟</strong>
                <span>اكتب لفريق نفّذها ونرد عليك.</span>
            </div>
            <div class="rf-wk-cta-actions">
                <a href="{{ url('/services?help=1') }}" class="btn btn-primary">ساعدوني أختار</a>
                <a href="{{ url('tickets') }}" class="btn btn-light">الدعم الفني</a>
            </div>
        </div>
    </div>
</section>
@endsection
