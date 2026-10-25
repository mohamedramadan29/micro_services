<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Metadata for SEO and Social Sharing -->
    {{-- Pages may set meta_description / meta_keywords / og_title / og_image (e.g. blog articles). --}}
    <meta name="description"
        content="@hasSection('meta_description')@yield('meta_description')@else نفذها هو منصة للخدمات المصغّرة والمشاريع، تجمع بين مقدمي الخدمات المستقلين وأصحاب المشاريع الباحثين عن إنجاز مهامهم بسرعة وكفاءة. سواء كنت تبحث عن تصميم، كتابة محتوى، أو تطوير، ستجد الخبرة المطلوبة هنا.@endif">
    <meta name="keywords" content="@hasSection('meta_keywords')@yield('meta_keywords')@else نفذها, خدمات مصغرة, مشاريع, مستقلين, تصميم, كتابة, تطوير, خدمات رقمية@endif">
    <meta name="author" content="نفذها">
    <meta property="og:title" content="@hasSection('og_title')@yield('og_title')@else نفذها - منصة الخدمات المصغرة والمشاريع@endif">
    <meta property="og:description" content="@hasSection('meta_description')@yield('meta_description')@else نفذها هو وجهتك المثالية للبحث عن محترفين لتنفيذ خدماتك وأعمالك.@endif">
    <meta property="og:image" content="@hasSection('og_image')@yield('og_image')@else{{asset('assets/website/img/logo.png')}}@endif">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@hasSection('og_title')@yield('og_title')@else نفذها - منصة الخدمات المصغرة والمشاريع@endif">
    <meta name="twitter:description"
        content="@hasSection('meta_description')@yield('meta_description')@else اكتشف عالمًا من الخدمات المصغّرة التي يقدّمها محترفون مستقلون جاهزون لإنجاز مشاريعك.@endif">
    <meta name="twitter:image" content="@hasSection('og_image')@yield('og_image')@else{{asset('assets/website/img/logo.png')}}@endif">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Favicon and Title -->
    <link rel="icon" href="{{asset('assets/website/img/favicon.png')}}" type="image/x-icon">
    <title>@yield('title')</title>

    <!-- CSS Files -->
    {{-- laravel-notify first, so its Tailwind reset (button backgrounds) cannot override the site styles --}}
    @notifyCss
    <link href="{{asset('assets/website/css/plugins.css')}}" rel="stylesheet">
    <link rel="stylesheet" href="{{ \App\Support\AssetVersion::url('assets/website/css/styles.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.rtl.min.css"
        integrity="sha384-gXt9imSW0VcJVHezoNQsP+TNrjYXoGcrqBZJpry9zJt8PCQjobwmhMGaDHTASo9N" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    @php
        // Brand colour from the admin settings (website_color); the theme reads --main-color.
        $brandColor = \App\Models\admin\Setting::first()?->website_color;
    @endphp
    @if ($brandColor && preg_match('/^#[0-9a-fA-F]{6}$/', $brandColor))
        <style>:root { --main-color: {{ $brandColor }}; }</style>
    @endif
    {{-- «الرسومات التنفيذية»: the site's visual world (fonts + design layer), loaded after the theme. --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Alexandria:wght@500;600;700;800&family=Readex+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ \App\Support\AssetVersion::url('assets/website/css/nafizha-world.css') }}">
    <script>
        // Start the drawing motion only when the page is visible and motion is welcome; otherwise it stays drawn.
        (function (d) {
            if (d.hidden || (window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches)) return;
            requestAnimationFrame(function () { d.documentElement.classList.add('nf-drawing'); });
        })(document);
        // Floating help buttons show after the first screen on phones (so they never cover the hero).
        addEventListener('scroll', function () { document.body && document.body.classList.toggle('nf-scrolled', scrollY > innerHeight * .6); }, { passive: true });
    </script>
    @yield('css')
    <!-- Notifications and Livewire -->
    @toastifyCss

    @livewireStyles
</head>
