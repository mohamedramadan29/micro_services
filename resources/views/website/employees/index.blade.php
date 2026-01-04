@extends('website.layouts.master')
@section('title')
    الموظفين
@endsection
@section('content')
    <!-- ============================ Hero Banner  Start================================== -->
    <div class="hero-banner bg-cover center"
        style="background:#00000057 url({{ asset('assets/website/img/jober_hero.jpeg') }}) no-repeat;" data-overlay="7">
        <div class="container">
            <h1>الموظفين</h1>
            <form class="mt-4" dir="rtl" method="get" action="{{ url('employees') }}">
                <div class="row justify-content-center">
                    <div class="col-lg-8 col-md-10 col-sm-12">
                        <div class="banner-search style-2">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control lio-rad"
                                    placeholder="بحث عن موظف  " value="{{ request()->input('search') }}">
                                <div class="input-group-append">
                                    <button type="submit" class="btn bt-round btn--2"> بحث <i
                                            class="ti-search"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <br>
    <!-- ============================ Hero Banner End ================================== -->

    <!-- ============================ Main Section Start ================================== -->
    <section class="gray-light min-sec text-right" dir="rtl">
        <div class="container">
            <div class="col-lg-12 col-md-12 col-sm-12 project_page" style="margin-top: 30px">

                @foreach ($employees as $employee)
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="ser_110">
                                <div class="project">
                                    <div class="row">
                                        <div class="col-9">
                                            <div class="project_data">
                                                <h5>
                                                    {{-- <a href="{{ url('employee/' . $employee->user_name) }}">  --}}
                                                    <a href="#">
                                                        {{ $employee->name }} </a>
                                                </h5>
                                                <p>
                                                    {{ Str::limit($employee->info, 150, '...') }}
                                                </p>
                                                <div class="mb-1">
                                                    <div class="user_info">
                                                        <div>
                                                            <img
                                                                src="{{ asset('assets/uploads/users_image/' . $employee->image) }}">
                                                        </div>
                                                        <div>
                                                            <p> {{ $employee->name }} </p>
                                                            <span>
                                                                {{ $employee->job_title }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>


                                </div>

                            </div>
                        </div>
                    </div>
                @endforeach

                <div class="row">
                    <div class="col-lg-12">
                        {{ $employees->links() }}
                    </div>
                </div>
            </div>

        </div>
    </section>
    <!-- ============================ Main Section End ================================== -->
@endsection

@section('styles')
    <style>
        .employee-card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            overflow: hidden;
        }

        .employee-image {
            height: 200px;
            overflow: hidden;
        }

        .employee-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .employee-info {
            padding: 20px;
            text-align: center;
        }

        .employee-info h4 {
            margin-bottom: 10px;
            color: #333;
        }

        .job-title {
            color: #666;
            margin-bottom: 15px;
        }

        .employee-rating {
            margin-bottom: 15px;
        }

        .employee-rating i {
            margin: 0 2px;
        }
    </style>
@endsection
