@include('website.layouts.header')
<body class="blue-skin">

<a class="nf-skip-link" href="#site-content">تجاوز القائمة إلى المحتوى</a>
<div id="main-wrapper">
@if (\App\Support\SiteContent::previewing())
    <div class="rf-preview-bar" dir="rtl">
        وضع المعاينة: تشاهد تعديلات «إدارة محتوى الواجهة» غير المنشورة، والزوار لا يرونها بعد.
        <a href="{{ route('admin.content.preview.stop') }}">الخروج من المعاينة</a>
    </div>
@endif
@hasSection('site_nav')
    @yield('site_nav')
@else
    @include('website.layouts.navbar')
@endif

<main id="site-content" tabindex="-1">
@yield('content')
</main>
@if (Session::has('Success_message'))
@php
    toastify()->success(\Illuminate\Support\Facades\Session::get('Success_message'));
@endphp
@endif
@if ($errors->any())
@foreach ($errors->all() as $error)
    @php
        toastify()->error($error);
    @endphp
@endforeach
@endif
@include('website.layouts.footer')
</div>
@include('website.layouts.partials.analytics')
@include('notify::components.notify')
@yield('js')

</body>
</html>
