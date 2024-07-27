@include('website.layouts.header')
<body class="blue-skin">

<div id="main-wrapper">
@include('website.layouts.navbar')

@yield('content')

@include('website.layouts.footer')
@include('notify::components.notify')
@yield('js')

</body>
</html>
