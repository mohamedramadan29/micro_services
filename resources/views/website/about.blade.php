@extends('website.layouts.master')
@php
    $about = \App\Support\SiteContent::first('about.page');
    $aboutSections = \App\Support\SiteContent::items('about.sections');
    $aboutImage = \App\Support\SiteContent::media($about['image']);
@endphp
@section('title'){{ $about['heading'] ?: 'من نحن' }} | نفّذها@endsection
@section('meta_description'){{ \Illuminate\Support\Str::limit((string) $about['hero_title'], 160) }}@endsection
@section('content')
<section class="rf-pp rf-ab" dir="rtl">
    <div class="container">
        <nav class="rf-pp-trail" aria-label="مسار الصفحة">
            <a href="{{ url('/') }}">الرئيسية</a> <span aria-hidden="true">‹</span> <span>{{ $about['heading'] ?: 'من نحن' }}</span>
        </nav>
        <header class="rf-sl-head rf-ab-head">
            <div>
                @if ($about['hero_small'])<p class="rf-ab-small">{{ $about['hero_small'] }}</p>@endif
                <h1>{{ $about['hero_title'] }}</h1>
            </div>
            @if ($about['hero_button'])
                <a href="#about_us" class="btn btn-light">{{ $about['hero_button'] }} <i class="bi bi-arrow-down" aria-hidden="true"></i></a>
            @endif
        </header>

        <div class="rf-ab-body" id="about_us">
            <figure class="rf-ab-figure nf-sheet">
                <img src="{{ $aboutImage ? $aboutImage->url() : asset('assets/website/img/about_us.svg') }}" alt="">
            </figure>
            <article class="rf-pp-card rf-ab-text">
                @if ($about['heading'])<h2>{{ $about['heading'] }}</h2>@endif
                @foreach ($aboutSections as $section)
                    <section>
                        <h3>{{ $section['heading'] }}</h3>
                        <p>{{ $section['text'] }}</p>
                    </section>
                @endforeach
            </article>
        </div>

        <div class="rf-pp-card rf-wk-cta">
            <div>
                <strong>جاهز تبدأ مشروعك؟</strong>
                <span>اكتب طلبك ويصلك أكثر من عرض من مستقلين معتمدين، أو اطلب من فريقنا يساعدك تختار.</span>
            </div>
            <div class="rf-wk-cta-actions">
                <a href="{{ route('requests.create') }}" class="btn btn-primary"><i class="bi bi-send" aria-hidden="true"></i> اطلب عرض سعر</a>
                <a href="{{ url('/services?help=1') }}" class="btn btn-light">ساعدوني أختار</a>
            </div>
        </div>
    </div>
</section>
@endsection
