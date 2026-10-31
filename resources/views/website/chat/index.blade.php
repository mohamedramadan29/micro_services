@extends('website.user.layouts.account')
@section('title')المحادثات@endsection
@section('account_title')المحادثات@endsection
@section('account')
    <div class="rf-acc-card">
        <h3>المحادثات</h3>
        @forelse ($conversations as $conversation)
            @php $otherUser = $conversation->sender_id == Auth::id() ? $conversation->receiver : $conversation->sender; @endphp
            <a class="rf-acc-row rf-ch-row" href="{{ url('chat-main/' . $conversation->id) }}">
                <img src="{{ $otherUser?->avatarUrl(128) ?? asset('assets/uploads/user.png') }}" alt="" width="44" height="44" loading="lazy">
                <div>
                    <strong>{{ $otherUser?->name ?? 'مستخدم محذوف' }}</strong>
                    <small>{{ \Illuminate\Support\Str::limit($conversation->lastMessage?->body ?? 'لا توجد رسائل بعد', 90) }}</small>
                </div>
                @if ($conversation->lastMessage)<span class="rf-ch-time">{{ $conversation->lastMessage->created_at?->diffForHumans() }}</span>@endif
            </a>
        @empty
            <p class="rf-acc-empty">لا توجد محادثات بعد. تبدأ المحادثة من صفحة أي خدمة جاهزة بزر «اسأل المستقل قبل الطلب».</p>
        @endforelse
    </div>
@endsection
