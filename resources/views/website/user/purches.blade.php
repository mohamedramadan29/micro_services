@extends('website.user.layouts.account')
@section('title')خدمات اشتريتها@endsection
@section('account_title')خدمات اشتريتها@endsection
@section('account')
    <div class="rf-acc-card">
        <h3>خدمات اشتريتها <a href="{{ route('services.ready') }}">خدمات جاهزة</a></h3>
        @forelse ($purches as $purche)
            <div class="rf-acc-row">
                <div>
                    <a href="{{ url('service/' . $purche->service_id . '-' . $purche->slug) }}"><strong>{{ $purche->service_name }}</strong></a>
                    <small>
                        @if ($purche->seller)<i class="bi bi-person" aria-hidden="true"></i> المستقل: @if ($purche->seller->user_name)<a href="{{ url('user/' . $purche->seller->user_name) }}">{{ $purche->seller->name }}</a>@else{{ $purche->seller->name }}@endif · @endif{{ $purche->created_at?->diffForHumans() }}
                    </small>
                </div>
                <span class="rf-acc-num">{{ \App\Support\Money::show($purche->service_price) }}</span>
                <span class="rf-acc-pill info">{{ $purche->status }}</span>
            </div>
        @empty
            <p class="rf-acc-empty">لم تشترِ خدمات جاهزة بعد. <a href="{{ route('services.ready') }}">تصفح الخدمات الجاهزة</a></p>
        @endforelse
    </div>
@endsection
