@extends('website.user.layouts.account')
@section('title')مشتريات المنتجات@endsection
@section('account_title')مشتريات المنتجات@endsection
@section('account')
    @php $paidTone = fn ($s) => $s === 'تم الدفع' ? 'ok' : (in_array($s, ['فشل الدفع', 'ملغي'], true) ? 'bad' : 'info'); @endphp
    <div class="rf-acc-card">
        <h3>مشتريات المنتجات <a href="{{ url('products') }}">المنتجات</a></h3>
        @forelse ($purches as $purch)
            <div class="rf-acc-row">
                <div>
                    @if ($purch->product)<a href="{{ url('product/' . $purch->product->slug) }}"><strong>{{ $purch->product_name }}</strong></a>@else<strong>{{ $purch->product_name }}</strong>@endif
                    <small>
                        {{ $purch->created_at?->translatedFormat('j F Y') }}
                        @if ($purch->country || $purch->city || $purch->address) · <i class="bi bi-truck" aria-hidden="true"></i> {{ collect([$purch->country, $purch->city, $purch->address])->filter()->implode('، ') }}@endif
                    </small>
                </div>
                <span class="rf-acc-num">{{ \App\Support\Money::show($purch->price) }}</span>
                @if ($purch->order_status === 'تم الدفع' && $purch->product?->type == 'digital' && $purch->product?->digital_file)
                    <a href="{{ route('download.product', $purch->id) }}" class="btn btn-primary btn-sm"><i class="bi bi-download" aria-hidden="true"></i> تنزيل الملف</a>
                @else
                    <span class="rf-acc-pill {{ $paidTone($purch->order_status) }}">{{ $purch->order_status }}</span>
                @endif
            </div>
        @empty
            <p class="rf-acc-empty">لا توجد مشتريات منتجات بعد. <a href="{{ url('products') }}">تصفح المنتجات</a></p>
        @endforelse
    </div>
@endsection
