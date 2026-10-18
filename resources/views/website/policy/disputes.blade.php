@extends('website.layouts.master')
@php
    $page = \App\Support\SiteContent::first('policy.page');
    $items = \App\Support\SiteContent::items('policy.items');
@endphp
@section('title')
    {{ $page['title'] }}
@endsection
@section('content')
    <section class="gray-bg text-right rf-page" dir="rtl">
        <div class="container">
            <div class="rf-card rf-policy">
                <span class="rf-badge">مشروعك ما بيوقف</span>
                <h1 class="rf-title">{{ $page['title'] }}</h1>
                @if ($page['intro'])<p class="rf-subtitle">{{ $page['intro'] }}</p>@endif
                <ol>
                    @foreach ($items as $item)
                        <li><h2>{{ $item['heading'] }}</h2><p>{!! nl2br(e($item['text'])) !!}</p></li>
                    @endforeach
                </ol>
                @if ($page['updated'])<p class="rf-muted mb-0">آخر تحديث: {{ $page['updated'] }}</p>@endif
            </div>
        </div>
    </section>
@endsection
