@extends('website.layouts.master')
@section('title')
    تخصصاتي
@endsection
@section('content')

    <section class="gray-bg pt-4 text-right profile_page" dir="rtl">
        <div class="container-fluid">
            <div class="row m-0">
                <div class="col-xl-3 col-lg-4 col-md-12 col-sm-12">
                    <div class="dashboard-navbar">
                        <div class="d-user-avater">
                            @if (Auth::user()->image != '')
                                <img src="{{ optional(Auth::user())->avatarUrl(128) }}"
                                    class="img-fluid rounded" alt="">
                            @else
                                <img src="{{ asset('assets/website/img/avatar.png') }}" class="img-fluid rounded"
                                    alt="">
                            @endif
                            <h4> {{ Auth::user()->user_name }} </h4>
                            <span> {{ Auth::user()->email }} </span>
                        </div>
                        @include('website.layouts.dashboard-sidebar')
                    </div>
                </div>

                <div class="col-xl-9 col-lg-8 col-md-12 col-sm-12">
                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <div class="bredcrumb_wrap">
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="{{ url('/') }}"> الرئيسية </a></li>
                                        <li class="breadcrumb-item active" aria-current="page"> تخصصاتي </li>
                                    </ol>
                                </nav>
                            </div>
                        </div>
                    </div>

                    <form method="post" action="{{ route('specializations.update') }}">
                        @csrf
                        <div class="_dashboard_content">
                            <div class="_dashboard_content_header">
                                <div class="_dashboard__header_flex">
                                    <h4><i class="bi bi-tags mr-1"></i> تخصصاتي </h4>
                                </div>
                            </div>

                            <div class="_dashboard_content_body">
                                <p class="mb-3"> اختر الخدمات التي تعمل بها، وستظهر لك طلبات العملاء في هذه الخدمات فقط. </p>

                                @forelse ($categories as $category)
                                    <div class="specialization-group">
                                        <h5> {{ $category->name }} </h5>
                                        <div class="specialization-items">
                                            @foreach ($category->subCategories as $subCategory)
                                                <input type="checkbox" class="btn-check" name="specializations[]"
                                                    id="spec_{{ $subCategory->id }}" value="{{ $subCategory->id }}"
                                                    @checked(in_array($subCategory->id, old('specializations', $selected)))>
                                                <label class="specialization-chip" for="spec_{{ $subCategory->id }}">
                                                    {{ $subCategory->name }}
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-muted"> لا توجد خدمات متاحة حاليًا. </p>
                                @endforelse
                            </div>
                        </div>

                        @if ($categories->isNotEmpty())
                            <button type="submit" class="btn btn-save btn-primary"> حفظ التخصصات <i class="fa fa-save"></i>
                            </button>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection
