@extends('website.user.layouts.account')
@section('title')تذاكري@endsection
@section('account_title')الدعم الفني (تذاكري)@endsection
@section('account')
    <div class="rf-acc-card">
        <h3>تذاكري <a href="{{ url('ticket/create') }}"><i class="bi bi-plus-lg" aria-hidden="true"></i> تذكرة جديدة</a></h3>
        @forelse ($tickets as $ticket)
            <div class="rf-acc-row">
                <div>
                    <a href="{{ url('ticket/' . $ticket->id) }}"><strong>{{ $ticket->title }}</strong></a>
                    <small>تذكرة رقم <span class="rf-acc-num">{{ $ticket->id }}</span> · {{ $ticket->created_at?->diffForHumans() }}</small>
                </div>
                @if ((int) $ticket['re-send'] === 1)
                    <span class="rf-acc-pill ok">وصل رد الفريق</span>
                @else
                    <span class="rf-acc-pill warn">بانتظار رد الفريق</span>
                @endif
            </div>
        @empty
            <p class="rf-acc-empty">لا توجد تذاكر بعد. عندك مشكلة أو سؤال؟ <a href="{{ url('ticket/create') }}">افتح تذكرة</a> وفريق نفّذها يرد عليك.</p>
        @endforelse
    </div>
@endsection
