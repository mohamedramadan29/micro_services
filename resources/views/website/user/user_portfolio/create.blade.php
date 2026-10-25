@extends('website.layouts.master')
@section('title')
    إضافة عمل جديد
@endsection
@section('content')
    <section class="gray-bg pt-4 text-right profile_page" dir="rtl">
        <div class="container-fluid">
            <div class="row m-0">
                <div class="col-xl-3 col-lg-4 col-md-12 col-sm-12">
                    @include('website.service-requests.partials.dashboard-nav')
                </div>
                <div class="col-xl-9 col-lg-8 col-md-12 col-sm-12">
                    <div class="bredcrumb_wrap">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ url('dashboard') }}">لوحتي</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('user.portfolio.index') }}">معرض أعمالي</a></li>
                                <li class="breadcrumb-item active" aria-current="page">إضافة عمل جديد</li>
                            </ol>
                        </nav>
                    </div>
                    @include('website.user.user_portfolio.partials.form', ['portfolio' => null])
                </div>
            </div>
        </div>
    </section>
@endsection
