@include('website.layouts.header')
<body class="blue-skin">

<div id="main-wrapper">
@include('website.layouts.navbar')

@yield('content')
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
@include('notify::components.notify')
@yield('js')

</body>
</html>
