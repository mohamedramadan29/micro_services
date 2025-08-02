<!-- Title -->
<title> @yield('title') </title>

<!-- Favicon -->
<link rel="icon" href="{{ URL::asset('assets/admin/img/logo_tabrat.png') }}" type="image/x-icon"/>
<!-- Icons css -->
<link href="{{ URL::asset('assets/admin/css/icons.css') }}" rel="stylesheet">
<!--  Custom Scroll bar-->
<link href="{{ URL::asset('assets/admin/plugins/mscrollbar/jquery.mCustomScrollbar.css') }}" rel="stylesheet"/>
<!--  Sidebar css -->
<link href="{{ URL::asset('assets/admin/plugins/sidebar/sidebar.css') }}" rel="stylesheet">
<!-- Sidemenu css -->
<link rel="stylesheet" href="{{ URL::asset('assets/admin/css-rtl/sidemenu.css') }}">
<!-- Internal Select2 css -->
<link href="{{ URL::asset('assets/admin/plugins/select2/css/select2.min.css') }}" rel="stylesheet">
<!--Internal  Datetimepicker-slider css -->
<link href="{{ URL::asset('assets/admin/plugins/amazeui-datetimepicker/css/amazeui.datetimepicker.css') }}"
      rel="stylesheet">
<link href="{{ URL::asset('assets/admin/plugins/jquery-simple-datetimepicker/jquery.simple-dtpicker.css') }}"
      rel="stylesheet">
<link href="{{ URL::asset('assets/admin/plugins/pickerjs/picker.min.css') }}" rel="stylesheet">

<!--- Style css -->
<link href="{{ URL::asset('assets/admin/css-rtl/style.css') }}" rel="stylesheet">
<!--- Dark-mode css -->
<link href="{{ URL::asset('assets/admin/css-rtl/style-dark.css') }}" rel="stylesheet">
<!---Skinmodes css-->
<link href="{{ URL::asset('assets/admin/css-rtl/skin-modes.css') }}" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/gh/kartik-v/bootstrap-fileinput@5.5.0/css/fileinput.min.css" media="all" rel="stylesheet" type="text/css" />
<meta name="csrf-token" content="{{ csrf_token() }}"/>
