@extends('admin.layouts.master')
@section('title')
    مشاهدة المشرفين
@endsection
@section('css')
    <link href="{{ URL::asset('assets/admin.php/plugins/datatable/css/dataTables.bootstrap4.min.css') }}" rel="stylesheet"/>
    <link href="{{ URL::asset('assets/admin.php/plugins/datatable/css/buttons.bootstrap4.min.css') }}" rel="stylesheet">
    <link href="{{ URL::asset('assets/admin.php/plugins/datatable/css/responsive.bootstrap4.min.css') }}" rel="stylesheet"/>
    <link href="{{ URL::asset('assets/admin.php/plugins/datatable/css/jquery.dataTables.min.css') }}" rel="stylesheet">
    <link href="{{ URL::asset('assets/admin.php/plugins/datatable/css/responsive.dataTables.min.css') }}" rel="stylesheet">
    <link href="{{ URL::asset('assets/admin.php/plugins/select2/css/select2.min.css') }}" rel="stylesheet">
@endsection
@section('content')
		<!-- Main-error-wrapper -->
		<div class="main-error-wrapper  page page-h ">
			<img src="{{URL::asset('assets/admin.php/img/media/404.png')}}" class="error-page" alt="error">
			<h2>أُووبس. الصفحة التي كنت تبحث عنها غير موجودة.</h2>
			<a class="btn btn-outline-danger" href="{{ url('/' . $page='admin.php/dashboard') }}">الرئيسية</a>
		</div>
		<!-- /Main-error-wrapper -->
@endsection
@section('js')
@endsection
