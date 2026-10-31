@extends('website.layouts.master')
@section('title')أقسام الخدمات | نفّذها@endsection
@section('content')
{{-- Same groups and services as /services; each group opens its own page. --}}
<section class="rf-pp rf-sl rf-sg" dir="rtl">
    <div class="container">
        <nav class="rf-pp-trail" aria-label="مسار الصفحة">
            <a href="{{ url('/') }}">الرئيسية</a> <span aria-hidden="true">‹</span> <span>أقسام الخدمات</span>
        </nav>
        <header class="rf-sl-head">
            <div>
                <h1>أقسام الخدمات</h1>
                <p>{{ $categories->count() }} أقسام رئيسية. اختر قسمًا لترى خدماته، أو <a href="{{ route('services.index') }}">ابحث في كل الخدمات</a>.</p>
            </div>
        </header>

        <div class="rf-sg-groups">
            @foreach ($categories as $group)
                @php $ready = $group->subCategories->sum(fn ($s) => (int) ($readyCounts[$s->id] ?? 0)); @endphp
                <article class="rf-sg-group nf-sheet">
                    <a class="rf-sg-group-head" href="{{ url('category/service/' . $group->slug) }}">
                        <span class="rf-sv-group-icon" aria-hidden="true">
                            @if ($group->image && file_exists(public_path('assets/uploads/service_category/' . $group->image)))
                                <img src="{{ asset('assets/uploads/service_category/' . $group->image) }}" alt="">
                            @else
                                <i class="bi {{ $group->icon ?: 'bi-grid' }}"></i>
                            @endif
                        </span>
                        <span>
                            <strong>{{ $group->name }}</strong>
                            <small>{{ $group->subCategories->count() }} خدمة @if ($ready) · {{ $ready }} جاهزة @endif</small>
                        </span>
                    </a>
                    <ul class="rf-sg-names">
                        @foreach ($group->subCategories->take(5) as $sub)
                            <li>{{ $sub->name }}</li>
                        @endforeach
                        @if ($group->subCategories->count() > 5)<li class="more">و{{ $group->subCategories->count() - 5 }} خدمات أخرى</li>@endif
                    </ul>
                    <a href="{{ url('category/service/' . $group->slug) }}" class="rf-sg-more">عرض خدمات القسم <i class="bi bi-arrow-left" aria-hidden="true"></i></a>
                </article>
            @endforeach
        </div>
    </div>
</section>
@endsection
