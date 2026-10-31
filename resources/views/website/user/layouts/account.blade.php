{{--
    Account page frame (same as balance/dashboard): side menu + the page column.
    Sections: title, account_title (breadcrumb + page heading), account (content).
--}}
@extends('website.layouts.master')
@section('content')
<section class="gray-bg pt-4 text-right profile_page" dir="rtl">
    <div class="container-fluid">
        <div class="row m-0">
            <div class="col-xl-3 col-lg-4 col-md-12 col-sm-12">
                @include('website.service-requests.partials.dashboard-nav')
            </div>
            <div class="col-xl-9 col-lg-8 col-md-12 col-sm-12 rf-acc">
                <div class="bredcrumb_wrap">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ url('dashboard') }}">لوحتي</a></li>
                            @hasSection('account_parent')@yield('account_parent')@endif
                            <li class="breadcrumb-item active" aria-current="page">@yield('account_title')</li>
                        </ol>
                    </nav>
                </div>
                @yield('account')
            </div>
        </div>
    </div>
</section>
@endsection
