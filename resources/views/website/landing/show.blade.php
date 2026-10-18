@extends('website.layouts.master')
@php
    use App\Support\SiteContent;
    $og = $page->ogImage;
@endphp
@section('title')
    {{ $page->title }}
@endsection
@if ($page->meta_description)
    @section('meta_description'){{ $page->meta_description }}@endsection
@endif
@section('og_title'){{ $page->title }}@endsection
@if ($og)
    @section('og_image'){{ $og->url() }}@endsection
@endif
@if (! $page->show_site_nav)
    @section('site_nav')
        <header class="rf-lp-topbar" dir="rtl">
            <div class="container">
                <a href="{{ url('/') }}"><img src="{{ asset('assets/website/img/logo.png') }}" alt="نفّذها" height="34"></a>
                @if ($page->header_button_text && $page->header_button_link)
                    <a href="{{ SiteContent::url($page->header_button_link) }}" class="rf-sv-btn rf-sv-btn-primary">{{ $page->header_button_text }}</a>
                @endif
            </div>
        </header>
    @endsection
@endif
@section('content')
    <div class="rf-lp {{ $page->show_site_nav ? 'rf-lp-with-nav' : '' }}" dir="rtl">
        @if ($preview)
            <div class="rf-preview-bar">
                معاينة صفحة الهبوط «{{ $page->title }}»: تشاهد المسودة{{ $page->is_published ? '' : '، والصفحة غير منشورة للزوار' }}.
                <a href="{{ route('admin.landing.edit', $page) }}">رجوع للتعديل</a>
            </div>
        @endif
        @forelse ($sections as $section)
            @php
                $f = $section['fields'];
                $bg = SiteContent::media($f['bg_image']);
                $style = collect([
                    $f['bg_color'] ? 'background-color: ' . $f['bg_color'] : null,
                    $bg ? 'background-image: url(' . $bg->url() . ')' : null,
                    $f['text_color'] ? '--lp-text: ' . $f['text_color'] . '; --lp-heading: ' . $f['text_color'] : null,
                    $f['accent_color'] ? '--lp-accent: ' . $f['accent_color'] : null,
                ])->filter()->implode('; ');
            @endphp
            <section class="rf-lp-section rf-lp-type-{{ $section['type'] }}{{ $bg ? ' rf-lp-has-bg' : '' }}"
                @if ($f['anchor']) id="{{ $f['anchor'] }}" @endif @if ($style) style="{{ $style }}" @endif>
                <div class="container">
                    @include('website.landing.sections.' . $section['type'], ['f' => $f, 'items' => $section['items']])
                </div>
            </section>
        @empty
            <section class="rf-lp-section"><div class="container"><p class="text-center">لا توجد أقسام ظاهرة في هذه الصفحة بعد.</p></div></section>
        @endforelse
    </div>
@endsection
