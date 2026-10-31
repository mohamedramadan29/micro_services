@extends('website.layouts.master')
@php
    use App\Http\Controllers\front\ProductController as P;
    $page = \App\Support\SiteContent::first('products.page');
    $countLabel = fn (int $n) => match (true) { $n === 1 => 'منتج واحد', $n === 2 => 'منتجان', $n >= 3 && $n <= 10 => $n . ' منتجات', default => $n . ' منتجًا' };
@endphp
@section('title'){{ $page['title'] }} | نفّذها@endsection
@section('meta_description'){{ \Illuminate\Support\Str::limit((string) $page['subtitle'], 160) }}@endsection
@section('content')
<section class="rf-pp rf-sl rf-pr" dir="rtl">
    <div class="container">
        <nav class="rf-pp-trail" aria-label="مسار الصفحة">
            <a href="{{ url('/') }}">الرئيسية</a> <span aria-hidden="true">‹</span> <span>{{ $page['title'] }}</span>
        </nav>
        <header class="rf-sl-head">
            <div>
                <h1>{{ $page['title'] }}</h1>
                @if ($page['subtitle'])<p>{{ $page['subtitle'] }}</p>@endif
            </div>
            @include('website.partials.list-search', ['placeholder' => $page['search_placeholder'], 'sorts' => P::SORTS, 'sort' => $sort])
        </header>

        @if ($products->isEmpty())
            <div class="rf-pp-card rf-sl-empty">
                <i class="bi bi-box-seam" aria-hidden="true"></i>
                <strong>{{ $page['empty'] }}</strong>
                <p></p>
                @if ($term !== '')<a href="{{ url('products') }}" class="btn btn-light">كل المنتجات</a>@endif
            </div>
        @else
            <p class="rf-sl-count">{{ $countLabel($products->total()) }}</p>
            <div class="rf-sl-grid">
                @foreach ($products as $product)
                    <article class="rf-sl-card">
                        <span class="rf-pp-work-img"><img src="{{ asset('assets/uploads/product_images/' . $product->image) }}" alt="" loading="lazy"></span>
                        <div class="rf-sl-body">
                            <small class="rf-rs-sub">{{ $product->type === 'physical' ? 'منتج يُشحن' : 'منتج رقمي' }}</small>
                            <h2><a href="{{ url('product/' . $product->slug) }}" class="rf-fl-link">{{ $product->name }}</a></h2>
                            @if ($product->short_description)<p class="rf-pr-short">{{ \Illuminate\Support\Str::limit(strip_tags((string) $product->short_description), 110) }}</p>@endif
                        </div>
                        <div class="rf-sl-foot">
                            @include('website.partials.product-price', ['product' => $product])
                        </div>
                    </article>
                @endforeach
            </div>
            <div class="mt-4">{{ $products->links() }}</div>
        @endif
    </div>
</section>
@endsection
