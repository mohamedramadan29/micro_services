@extends('website.layouts.master')
@php
    $page = \App\Support\SiteContent::first('reviews.page');
    $starLabels = [5 => '5 نجوم', 4 => '4 نجوم', 3 => '3 نجوم', 2 => '2 نجمتان', 1 => 'نجمة واحدة'];
    $starNames = [1 => 'ضعيف جدًا', 2 => 'ضعيف', 3 => 'جيد', 4 => 'جيد جدًا', 5 => 'ممتاز'];
@endphp
@section('title'){{ $page['title'] }} | نفّذها@endsection
@section('meta_description'){{ \Illuminate\Support\Str::limit((string) $page['subtitle'], 160) }}@endsection
@section('content')
<section class="rf-pp rf-sl rf-rv" dir="rtl">
    <div class="container">
        <nav class="rf-pp-trail" aria-label="مسار الصفحة">
            <a href="{{ url('/') }}">الرئيسية</a> <span aria-hidden="true">‹</span>
            <a href="{{ route('testimonials.index') }}">آراء العملاء</a> <span aria-hidden="true">‹</span> <span>{{ $page['title'] }}</span>
        </nav>
        <header class="rf-sl-head">
            <div>
                <h1>{{ $page['title'] }}</h1>
                @if ($page['subtitle'])<p>{{ $page['subtitle'] }}</p>@endif
            </div>
        </header>

        <div class="rf-pp-layout">
            <div class="rf-pp-main">
                <div class="rf-pp-card">
                    @if ($page['form_title'])<h2>{{ $page['form_title'] }}</h2>@endif
                    @if ($page['form_text'])<p class="rf-rv-lead">{{ $page['form_text'] }}</p>@endif

                    @if ($errors->any())
                        <div class="alert alert-danger" role="alert">
                            @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
                        </div>
                    @endif

                    <form method="post" action="{{ route('front.reviews.post') }}" class="rf-rv-form" data-track-start="review">
                        @csrf
                        <label for="rv-name">الاسم <span aria-hidden="true">*</span></label>
                        <input type="text" id="rv-name" class="form-control" name="name" required maxlength="120" autocomplete="name" placeholder="اكتب اسمك" value="{{ old('name', auth()->user()?->name) }}">

                        <fieldset class="rf-rv-rate">
                            <legend>التقييم <span aria-hidden="true">*</span></legend>
                            <div class="rf-rv-stars">
                                @for ($star = 5; $star >= 1; $star--)
                                    <input type="radio" name="rating" value="{{ $star }}" id="star{{ $star }}" @checked((int) old('rating') === $star) @if ($star === 5) required @endif>
                                    <label for="star{{ $star }}" title="{{ $starNames[$star] }}"><i class="bi bi-star-fill" aria-hidden="true"></i><span class="visually-hidden">{{ $starLabels[$star] }} — {{ $starNames[$star] }}</span></label>
                                @endfor
                            </div>
                            <div class="rf-rv-scale"><small>{{ $starNames[1] }}</small><small>{{ $starNames[5] }}</small></div>
                        </fieldset>

                        <label for="rv-notes">ملاحظاتك <span aria-hidden="true">*</span></label>
                        <textarea id="rv-notes" class="form-control" rows="5" name="notes" required maxlength="2000" placeholder="{{ $page['notes_placeholder'] }}">{{ old('notes') }}</textarea>

                        <button type="submit" class="btn btn-primary w-100 mt-3"><i class="bi bi-send" aria-hidden="true"></i> إرسال التقييم</button>
                    </form>
                </div>
            </div>

            <aside class="rf-pp-side">
                @if ($ratingStats)
                    <div class="rf-pp-card rf-rv-stats">
                        <h2>التقييم الإجمالي</h2>
                        <div class="rf-rv-overall">
                            <div class="overall-rating">{{ $ratingStats['average'] }}</div>
                            <div class="rf-rv-stars-show" aria-label="{{ $ratingStats['average'] }} من 5">
                                @for ($i = 0; $i < $ratingStats['full_stars']; $i++)<i class="bi bi-star-fill"></i>@endfor
                                @if ($ratingStats['half_star'])<i class="bi bi-star-half"></i>@endif
                                @for ($i = 0; $i < $ratingStats['empty_stars']; $i++)<i class="bi bi-star"></i>@endfor
                            </div>
                            <small>من {{ $ratingStats['total'] }} تقييم</small>
                        </div>
                        <h3>توزيع التقييمات</h3>
                        @foreach ($ratingStats['distribution'] as $star => $percent)
                            <div class="rf-rv-bar">
                                <span>{{ $starLabels[$star] }}</span>
                                <span class="rf-rv-track"><span style="width: {{ $percent }}%;"></span></span>
                                <span class="fw-bold">{{ $percent }}%</span>
                            </div>
                        @endforeach
                    </div>
                @endif
                <div class="rf-pp-card rf-pp-cta">
                    <h2>آراء العملاء</h2>
                    <p>اقرأ ما كتبه عملاء نفّذها بعد مراجعة الفريق.</p>
                    <a href="{{ route('testimonials.index') }}" class="btn btn-light w-100">عرض الآراء</a>
                </div>
            </aside>
        </div>
    </div>
</section>
@endsection
