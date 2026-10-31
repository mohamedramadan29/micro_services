@extends('website.layouts.master')
@section('title'){{ $category->name }} | نفّذها@endsection
@section('meta_description'){{ \Illuminate\Support\Str::limit(strip_tags((string) $category->description), 160) }}@endsection
@section('content')
<section class="rf-pp rf-sl rf-sg" dir="rtl">
    <div class="container">
        <nav class="rf-pp-trail" aria-label="مسار الصفحة">
            <a href="{{ url('/') }}">الرئيسية</a> <span aria-hidden="true">‹</span>
            <a href="{{ url('categories') }}">أقسام الخدمات</a> <span aria-hidden="true">‹</span> <span>{{ $category->name }}</span>
        </nav>
        <header class="rf-sl-head">
            <div>
                <h1>{{ $category->name }}</h1>
                <p>@if ($category->description){{ $category->description }} @endif{{ $sub_categories->count() }} خدمة في هذا القسم.</p>
            </div>
        </header>

        @if ($sub_categories->isEmpty())
            <div class="rf-pp-card rf-sl-empty">
                <i class="bi bi-grid" aria-hidden="true"></i>
                <strong>لا توجد خدمات في هذا القسم الآن.</strong>
                <p></p>
                <a href="{{ route('services.index') }}" class="btn btn-light">كل الخدمات</a>
            </div>
        @else
            <ul class="rf-sg-list rf-pp-card">
                @foreach ($sub_categories as $sub)
                    @include('website.partials.catalog-service-row', ['sub' => $sub, 'group' => null, 'hasForm' => isset($withForm[$sub->id]), 'ready' => (int) ($readyCounts[$sub->id] ?? 0)])
                @endforeach
            </ul>
        @endif

        <div class="rf-pp-card rf-wk-cta">
            <div>
                <strong>مش متأكد أي خدمة تناسبك؟</strong>
                <span>اكتب لنا ما تحتاجه وفريق نفّذها يساعدك تختار.</span>
            </div>
            <div class="rf-wk-cta-actions">
                <a href="{{ url('/services?help=1') }}" class="btn btn-primary">ساعدوني أختار</a>
                <a href="{{ route('projects.new') }}" class="btn btn-light">انشر مشروعك</a>
            </div>
        </div>
    </div>
</section>
@endsection
