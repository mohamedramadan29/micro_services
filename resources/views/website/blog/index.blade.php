@extends('website.layouts.master')
@php
    $page = \App\Support\SiteContent::first('blog.page');
    $heroBackground = \App\Support\SiteContent::media($page['hero_background']);
@endphp
@section('title')
    {{ $category ? $category->name . ' — ' : '' }}{{ $page['title'] }}
@endsection
@section('meta_description'){{ $category?->meta_description ?: $page['subtitle'] }}@endsection
@section('content')
    <section class="rf-sv-hero" dir="rtl" @if ($heroBackground) style="background-image: url({{ $heroBackground->url() }})" @endif>
        <div class="container">
            <h1>{{ $page['title'] }}</h1>
            @if ($page['subtitle'])<p>{{ $page['subtitle'] }}</p>@endif
            <form method="GET" class="rf-fl-search" role="search">
                @if ($category)<input type="hidden" name="category" value="{{ $category->slug }}">@endif
                <input type="search" name="q" value="{{ request('q') }}" placeholder="{{ $page['search_placeholder'] }}" aria-label="{{ $page['search_placeholder'] }}">
                <button type="submit" class="rf-sv-btn rf-sv-btn-primary">ابحث</button>
            </form>
        </div>
    </section>

    <section class="rf-sv" dir="rtl">
        <div class="container">
            <nav class="rf-fl-chips" aria-label="أقسام المدونة">
                <a href="{{ route('blogCategories', array_filter(['q' => request('q')])) }}" class="{{ $category ? '' : 'active' }}">{{ $page['all_label'] }}</a>
                @foreach ($categories as $item)
                    <a href="{{ route('blogCategories', array_filter(['category' => $item->slug, 'q' => request('q')])) }}" class="{{ $category?->id === $item->id ? 'active' : '' }}">{{ $item->name }}</a>
                @endforeach
            </nav>

            <div class="rf-fl-section-head">
                <h2>{{ $category ? $category->name : $page['latest_title'] }}</h2>
                <span>{{ $blogs->total() }} مقال</span>
            </div>
            @if ($category?->description)<p class="rf-bl-intro">{{ $category->description }}</p>@endif

            @if ($blogs->isEmpty())
                <p class="rf-sv-empty">{{ $page['empty'] }}</p>
            @else
                <div class="rf-bl-grid">
                    @foreach ($blogs as $blog)
                        @include('website.blog.partials.card', ['readButton' => $page['read_button']])
                    @endforeach
                </div>
                <div class="mt-4">{{ $blogs->links() }}</div>
            @endif
        </div>
    </section>
@endsection
