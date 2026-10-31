@extends('website.user.layouts.account')
@section('title')طلبات شراء خدماتي@endsection
@section('account_title')طلبات شراء خدماتي@endsection
@section('account')
    <div class="rf-acc-card">
        <h3>طلبات شراء خدماتي <a href="{{ url('service/index') }}">خدماتي المعروضة</a></h3>
        @forelse ($orders as $order)
            <div class="rf-acc-row">
                <div>
                    <a href="{{ url('service/' . $order->service_id . '-' . $order->slug) }}"><strong>{{ $order->service_name }}</strong></a>
                    <small><i class="bi bi-person" aria-hidden="true"></i> العميل: {{ $order->buyer?->name ?? '—' }} · {{ $order->created_at?->diffForHumans() }}</small>
                </div>
                <span class="rf-acc-num">{{ \App\Support\Money::show($order->service_price) }}</span>
                <span class="rf-acc-pill info">{{ $order->status }}</span>
            </div>
        @empty
            <p class="rf-acc-empty">لا توجد طلبات شراء لخدماتك حتى الآن. <a href="{{ url('service/index') }}">راجع خدماتك المعروضة</a></p>
        @endforelse
    </div>
@endsection
