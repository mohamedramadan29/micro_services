<!DOCTYPE html>
<html lang="ar" >
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Metadata for SEO and Social Sharing -->
    <meta name="description" content="نفذها هو منصة للخدمات المصغّرة والمشاريع، تجمع بين مقدمي الخدمات المستقلين وأصحاب المشاريع الباحثين عن إنجاز مهامهم بسرعة وكفاءة. سواء كنت تبحث عن تصميم، كتابة محتوى، أو تطوير، ستجد الخبرة المطلوبة هنا.">
    <meta name="keywords" content="نفذها, خدمات مصغرة, مشاريع, مستقلين, تصميم, كتابة, تطوير, خدمات رقمية">
    <meta name="author" content="نفذها">
    <meta property="og:title" content="نفذها - منصة الخدمات المصغرة والمشاريع">
    <meta property="og:description" content="نفذها هو وجهتك المثالية للبحث عن محترفين لتنفيذ خدماتك وأعمالك.">
    <meta property="og:image" content="{{asset('assets/website/img/logo.png')}}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://nafizha.com/"> <!-- استبدل برابط موقعك -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="نفذها - منصة الخدمات المصغرة والمشاريع">
    <meta name="twitter:description" content="اكتشف عالمًا من الخدمات المصغّرة التي يقدّمها محترفون مستقلون جاهزون لإنجاز مشاريعك.">
    <meta name="twitter:image" content="{{asset('assets/website/img/logo.png')}}">

    <!-- Favicon and Title -->
    <link rel="icon" href="{{asset('assets/website/img/favicon.png')}}" type="image/x-icon">
    <title>@yield('title')</title>

    <!-- CSS Files -->
    <link href="{{asset('assets/website/css/plugins.css')}}" rel="stylesheet">
    <link href="{{asset('assets/website/css/styles.css')}}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.rtl.min.css" integrity="sha384-gXt9imSW0VcJVHezoNQsP+TNrjYXoGcrqBZJpry9zJt8PCQjobwmhMGaDHTASo9N" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    @yield('css')
    <!-- Notifications and Livewire -->
    @notifyCss
    @toastifyCss

    @livewireStyles
</head>


