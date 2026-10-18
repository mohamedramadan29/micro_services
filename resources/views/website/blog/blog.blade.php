@extends('website.layouts.master')
@php
    $page = \App\Support\SiteContent::first('blog.page');
    $date = \Illuminate\Support\Carbon::parse($blog->publish_date);
@endphp
@section('title')
    {{ $blog->meta_title ?: $blog->name }}
@endsection
@section('meta_description'){{ $blog->meta_desc ?: $blog->excerpt(160) }}@endsection
@section('meta_keywords'){{ $blog->meta_keywords }}@endsection
@section('og_title'){{ $blog->meta_title ?: $blog->name }}@endsection
@if ($blog->image)
    @section('og_image'){{ $blog->Image() }}@endsection
@endif
@section('content')
    <article dir="rtl">
        <header class="rf-bl-hero">
            <div class="container">
                @unless ($blog->isPublished())
                    <div class="rf-notice rf-notice-error mb-3">معاينة للأدمن: هذا المقال غير منشور للزوار (مسودة أو مجدول).</div>
                @endunless
                <nav class="rf-bl-crumbs" aria-label="مسار التصفح">
                    <a href="{{ route('blogCategories') }}">{{ $page['title'] }}</a>
                    @if ($blog->Category)
                        <span>/</span><a href="{{ route('blogCategories', ['category' => $blog->Category->slug]) }}">{{ $blog->Category->name }}</a>
                    @endif
                </nav>
                <h1>{{ $blog->name }}</h1>
                <div class="rf-bl-meta">
                    @if ($blog->Author)<span><i class="bi bi-person"></i> {{ $blog->Author->name }}</span>@endif
                    <span><i class="bi bi-calendar3"></i> {{ $date->format('Y-m-d') }}</span>
                    <span><i class="bi bi-clock"></i> {{ $blog->readMinutes() }} د قراءة</span>
                </div>
            </div>
        </header>

        <div class="container rf-bl-article">
            @if ($blog->image)
                <img src="{{ $blog->Image() }}" alt="{{ $blog->name }}" class="rf-bl-cover">
            @endif
            @if ($blog->short_desc)<p class="rf-bl-lead">{{ $blog->short_desc }}</p>@endif
            {{-- Written by admins in the editor (headings, images, links). --}}
            <div class="rf-bl-content">{!! $blog->desc !!}</div>
        </div>
    </article>

    @if ($related->isNotEmpty())
        <section class="rf-sv" dir="rtl">
            <div class="container">
                <div class="rf-fl-section-head"><h2>{{ $page['related_title'] }}</h2></div>
                <div class="rf-bl-grid">
                    @foreach ($related as $item)
                        @include('website.blog.partials.card', ['blog' => $item, 'readButton' => $page['read_button']])
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
