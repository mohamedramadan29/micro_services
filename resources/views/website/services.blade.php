@extends('website.layouts.master')
@section('title')
    {{ __('services.services') }}
@endsection
@section('content')
    <!-- ============================ Main Section Start ================================== -->
    <section class="gray-bg text-right profile_page" dir="rtl">
        <div class="container">
            <div class="row">

                <div class="col-lg-4 col-md-12 col-sm-12">
                    <div class="simple-sidebar sm-sidebar">

                        <div class="search-sidebar_header">
                            <h4 class="ssh_heading"> {{ __('services.the_search') }} </h4>

                        </div>

                        <!-- Find New Property -->
                        <div class="sidebar-widgets collapse miz_show" id="search_open" data-parent="#search_open">
                            <form action="{{ url('services') }}" method="get">
                                @csrf
                                <div class="search-inner p-0">

                                    <div class="filter-search-box pb-0">
                                        <div class="form-group">
                                            <input type="text" name="search" class="form-control"
                                                value="{{ request()->input('search') }}"
                                                placeholder="{{ __('services.search_services') }} ">
                                        </div>
                                    </div>
                                    <div class="filter_wraps">
                                        <!-- Job categories Search -->
                                        <div class="single_search_boxed">
                                            <div class="widget-boxed-header">
                                                <h4>
                                                    <a href="#categories" data-toggle="collapse" aria-expanded="true"
                                                        role="button"> حدد القسم </a>
                                                </h4>
                                            </div>
                                            <div class="widget-boxed-body collapse show" id="categories"
                                                data-parent="#categories" dir="rtl">
                                                <div class="side-list no-border">
                                                    <!-- Single Filter Card -->
                                                    <div class="single_filter_card">
                                                        <div class="card-body pt-0">
                                                            <div class="inner_widget_link">
                                                                <ul class="no-ul-list filter-list">
                                                                    @foreach ($categories as $category)
                                                                        <li>
                                                                            <label for="a1"
                                                                                class="checkbox-custom-label">
                                                                                {{ $category['name'] }}
                                                                            </label>
                                                                            <ul class="no-ul-list filter-list"
                                                                                style="padding-right: 25px">
                                                                                @foreach ($category['subCategories'] as $subCat)
                                                                                    <li>
                                                                                        <input id="{{ $subCat['id'] }}"
                                                                                            value="{{ $subCat['id'] }}"
                                                                                            class="checkbox-custom"
                                                                                            name="cat_ids[]" type="checkbox"
                                                                                            {{ in_array($subCat['id'], request()->input('cat_ids', [])) ? 'checked' : '' }}>
                                                                                        <label for="{{ $subCat['id'] }}"
                                                                                            class="checkbox-custom-label">
                                                                                            {{ $subCat['name'] }}
                                                                                        </label>
                                                                                    </li>
                                                                                @endforeach
                                                                            </ul>
                                                                        </li>
                                                                    @endforeach
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group filter_button pt-2">
                                        <button type="submit" class="btn btn-primary btn btn-theme-2 rounded full-width">
                                            بحث
                                        </button>
                                    </div>
                                </div>


                            </form>


                        </div>


                    </div>
                    <!-- Sidebar End -->

                </div>

                <!-- Item Wrap Start -->
                <div class="col-lg-8 col-md-12 col-sm-12">
                    <div class="row">
                        @foreach ($services as $serv)
                            <!-- Single Item -->
                            <div class="col-lg-4 col-md-6 col-sm-12">
                                <div class="ser_110 shadow_0">
                                    <div class="ser_110_thumb">
                                        <a href="{{ url('service/' . $serv['id'] . '-' . $serv['slug']) }}"
                                            class="ser_100_link"><img
                                                src="{{ asset('assets/uploads/services/' . $serv['image']) }}"
                                                class="img-fluid" alt=""></a>
                                    </div>
                                    <div class="ser_110_footer bott">
                                        <div class="_110_foot_left">
                                            <div class="_autho098">
                                                @if (empty($serv['user']['image']))
                                                    <img src="{{ asset('assets/website/img/avatar.png') }}"
                                                        class="img-fluid circle" alt="">
                                                @else
                                                    <img src="{{ asset('assets/uploads/users_image/' . $serv['user']['image']) }}"
                                                        class="img-fluid circle" alt="">
                                                @endif

                                                <img src="{{ asset('assets/website/img/verify.svg') }}" class="verified"
                                                    width="12" alt="">
                                            </div>

                                            <div class="_autho097">
                                                <h5>
                                                    <a href="{{ url('user/' . $serv['user']['user_name']) }}">{{ $serv['user']['user_name'] }}
                                                    </a>
                                                </h5>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="ser_110_caption">
                                        <div class="ser_rev098">
                                            @for ($i = 0; $i < 5; $i++)
                                                @if ($i < $serv['rate'])
                                                    <i class="fa fa-star filled"></i>
                                                @else
                                                    <i class="fa fa-star"></i>
                                                @endif
                                            @endfor
                                        </div>
                                        <div class="ser_title098">
                                            <h4 class="_ser_title"><a
                                                    href="{{ url('service/' . $serv['id'] . '-' . $serv['slug']) }}">
                                                    {{ $serv['name'] }} </a>
                                            </h4>
                                        </div>
                                        <div class="_oi0po price_section"><i class="fa fa-bolt"></i>
                                            {{ __('services.serive_price') }} <strong class="theme-cl">
                                                {{ number_format($serv['price'], 2) }} $ </strong>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            {{ $services->links() }}
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </section>
    <!-- ============================ Main Section End ================================== -->
@endsection
