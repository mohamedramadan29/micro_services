@extends('website.layouts.master')
@section('title')
    {{ $employee->name }} - الملف الشخصي
@endsection
@section('content')
    <!-- ============================ Page Title Start================================== -->
    <div class="page-title" style="height: 350px;text-align: right">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-md-12">
                    <h2 class="ipt-title">الرئيسية </h2>
                    <span class="ipn-subtitle"> الملف الشخصي للموظف </span>
                </div>
            </div>
        </div>
    </div>
    <!-- ============================ Page Title End ================================== -->

    <!-- ============================ Main Section Start ================================== -->
    <section class="gray-light min-sec text-right" dir="rtl">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-12">
                    <div class="employee-profile-card">
                        <div class="employee-profile-image">
                            <img src="{{ $employee->profile_image }}" alt="{{ $employee->name }}">
                        </div>
                        <div class="employee-profile-info">
                            <h3>{{ $employee->name }}</h3>
                            <p class="job-title">{{ $employee->job_title }}</p>
                            <div class="employee-rating">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fa fa-star {{ $i <= $employee->rate ? 'text-warning' : 'text-muted' }}"></i>
                                @endfor
                                <span class="rating-count">({{ $employee->rate_count }} تقييم)</span>
                            </div>
                            <div class="employee-contact">
                                <p><i class="fa fa-envelope"></i> {{ $employee->email }}</p>
                                <p><i class="fa fa-phone"></i> {{ $employee->phone }}</p>
                                <p><i class="fa fa-map-marker"></i> {{ $employee->address }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8 col-md-12">
                    <div class="employee-details-card">
                        <div class="card-header">
                            <h4>نبذة عن الموظف</h4>
                        </div>
                        <div class="card-body">
                            <p>{{ $employee->about }}</p>
                        </div>
                    </div>

                    <div class="employee-details-card">
                        <div class="card-header">
                            <h4>المهارات</h4>
                        </div>
                        <div class="card-body">
                            <div class="skills-list">
                                @foreach(explode(',', $employee->skills) as $skill)
                                    <span class="skill-tag">{{ trim($skill) }}</span>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="employee-details-card">
                        <div class="card-header">
                            <h4>الخبرات</h4>
                        </div>
                        <div class="card-body">
                            <div class="experience-list">
                                @foreach($employee->experiences as $experience)
                                    <div class="experience-item">
                                        <h5>{{ $experience->title }}</h5>
                                        <p class="company">{{ $experience->company }}</p>
                                        <p class="period">{{ $experience->from_date }} - {{ $experience->to_date }}</p>
                                        <p class="description">{{ $experience->description }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- ============================ Main Section End ================================== -->
@endsection

@section('styles')
<style>
    .employee-profile-card {
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 0 15px rgba(0,0,0,0.1);
        margin-bottom: 30px;
        overflow: hidden;
    }

    .employee-profile-image {
        height: 300px;
        overflow: hidden;
    }

    .employee-profile-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .employee-profile-info {
        padding: 20px;
        text-align: center;
    }

    .employee-profile-info h3 {
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

    .rating-count {
        color: #666;
        font-size: 14px;
    }

    .employee-contact {
        text-align: right;
        margin-top: 20px;
    }

    .employee-contact p {
        margin-bottom: 10px;
        color: #666;
    }

    .employee-contact i {
        margin-left: 10px;
        color: #007bff;
    }

    .employee-details-card {
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 0 15px rgba(0,0,0,0.1);
        margin-bottom: 30px;
    }

    .card-header {
        padding: 20px;
        border-bottom: 1px solid #eee;
    }

    .card-header h4 {
        margin: 0;
        color: #333;
    }

    .card-body {
        padding: 20px;
    }

    .skills-list {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .skill-tag {
        background: #f8f9fa;
        padding: 5px 15px;
        border-radius: 20px;
        color: #666;
    }

    .experience-item {
        margin-bottom: 20px;
        padding-bottom: 20px;
        border-bottom: 1px solid #eee;
    }

    .experience-item:last-child {
        margin-bottom: 0;
        padding-bottom: 0;
        border-bottom: none;
    }

    .experience-item h5 {
        color: #333;
        margin-bottom: 5px;
    }

    .experience-item .company {
        color: #007bff;
        margin-bottom: 5px;
    }

    .experience-item .period {
        color: #666;
        font-size: 14px;
        margin-bottom: 10px;
    }

    .experience-item .description {
        color: #666;
    }
</style>
@endsection
