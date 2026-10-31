@extends('website.layouts.master')
@php
    $page = \App\Support\SiteContent::first('testimonials.page');
    $filters = ['all' => 'الكل', 'featured' => 'مميز', '5' => '5 نجوم', '4' => '4 نجوم'];
    $filter = is_string(request('filter')) && array_key_exists(request('filter'), $filters) ? request('filter') : 'all';
    // Only approved, active reviews are ever shown (never invented quotes).
    $base = \App\Models\front\Review::active()->approved();
    $reviews = (clone $base)
        ->when($filter === 'featured', fn ($q) => $q->where('is_featured', true))
        ->when(in_array($filter, ['5', '4'], true), fn ($q) => $q->where('rating', (int) $filter))
        ->ordered()->get();
    $total = (clone $base)->count();
@endphp
@section('title'){{ $page['title'] }} | نفّذها@endsection
@section('meta_description'){{ \Illuminate\Support\Str::limit((string) $page['subtitle'], 160) }}@endsection
@section('content')
<section class="rf-pp rf-sl rf-tm" dir="rtl">
    <div class="container">
        <nav class="rf-pp-trail" aria-label="مسار الصفحة">
            <a href="{{ url('/') }}">الرئيسية</a> <span aria-hidden="true">‹</span> <span>{{ $page['title'] }}</span>
        </nav>
        <header class="rf-sl-head">
            <div>
                <h1>{{ $page['title'] }}</h1>
                @if ($page['subtitle'])<p>{{ $page['subtitle'] }}</p>@endif
            </div>
            @if ($page['cta_button'])
                <a href="{{ route('reviews.index') }}" class="btn btn-primary"><i class="bi bi-pencil-square" aria-hidden="true"></i> {{ $page['cta_button'] }}</a>
            @endif
        </header>

        @if ($total > 0)
            <nav class="rf-fl-chips" aria-label="تصفية الآراء">
                @foreach ($filters as $key => $label)
                    <a href="{{ $key === 'all' ? route('testimonials.index') : route('testimonials.index', ['filter' => $key]) }}" class="{{ $filter === (string) $key ? 'active' : '' }}" @if ($filter === (string) $key) aria-current="true" @endif>{{ $label }}</a>
                @endforeach
            </nav>
        @endif

        @if ($reviews->isEmpty())
            <div class="rf-pp-card rf-sl-empty">
                <i class="bi bi-chat-quote" aria-hidden="true"></i>
                <strong>{{ $page['empty'] }}</strong>
                <p></p>
                @if ($filter !== 'all')<a href="{{ route('testimonials.index') }}" class="btn btn-light">كل الآراء</a>@endif
            </div>
        @else
            <div class="rf-tm-grid">
                @foreach ($reviews as $review)
                    <figure class="rf-tm-card">
                        <div class="rf-tm-top">
                            <span class="rf-tm-stars" aria-label="{{ (int) $review->rating }} من 5">
                                @for ($i = 1; $i <= 5; $i++)<i class="bi {{ $i <= (int) $review->rating ? 'bi-star-fill' : 'bi-star' }}" aria-hidden="true"></i>@endfor
                            </span>
                            @if ($review->is_featured)<span class="rf-acc-pill ok">مميز</span>@endif
                        </div>
                        <blockquote>{{ $review->content }}</blockquote>
                        <figcaption>
                            <img src="{{ $review->client_image ? asset($review->client_image) : asset('assets/website/img/user-default.png') }}" alt="" width="44" height="44" loading="lazy">
                            <span>
                                <strong>{{ $review->client_name }}</strong>
                                @if ($review->client_position || $review->client_company)
                                    <small>{{ $review->client_position }}@if ($review->client_position && $review->client_company) · @endif{{ $review->client_company }}</small>
                                @endif
                            </span>
                        </figcaption>
                    </figure>
                @endforeach
            </div>
        @endif

        @if ($page['cta_title'])
            <div class="rf-pp-card rf-wk-cta">
                <div><strong>{{ $page['cta_title'] }}</strong></div>
                <div class="rf-wk-cta-actions">
                    <a href="{{ route('reviews.index') }}" class="btn btn-primary"><i class="bi bi-pencil-square" aria-hidden="true"></i> {{ $page['cta_button'] ?: 'أضف تقييمك' }}</a>
                </div>
            </div>
        @endif
    </div>
</section>
@endsection
