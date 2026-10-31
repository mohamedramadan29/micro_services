@extends('website.user.layouts.account')
@section('title')تفاصيل التذكرة@endsection
@section('account_parent')<li class="breadcrumb-item"><a href="{{ url('tickets') }}">تذاكري</a></li>@endsection
@section('account_title')تذكرة رقم {{ $ticket_id }}@endsection
@section('account')
    @php $me = Auth::user(); @endphp
    <div class="rf-acc-card">
        <h3>تذكرة رقم {{ $ticket_id }} <a href="{{ url('tickets') }}">كل التذاكر</a></h3>
        <ol class="rf-tk-thread">
            @foreach ($messages as $message)
                @php $mine = $message['sender_type'] == 'user'; @endphp
                <li class="{{ $mine ? 'me' : 'team' }}">
                    <img src="{{ $mine ? $me->avatarUrl(128) : asset('assets/website/img/favicon.png') }}" alt="" width="36" height="36">
                    <div>
                        <strong>{{ $mine ? 'أنت' : 'فريق دعم نفّذها' }}</strong>
                        <p>{{ $message['content'] }}</p>
                        @if ($message['file'])
                            <a href="{{ asset('assets/uploads/tickets/' . $message['file']) }}" target="_blank" rel="noopener" class="rf-tk-file"><i class="bi bi-paperclip" aria-hidden="true"></i> عرض المرفق</a>
                        @endif
                        @if (! empty($message['created_at']))<small>{{ \Illuminate\Support\Carbon::parse($message['created_at'])->diffForHumans() }}</small>@endif
                    </div>
                </li>
            @endforeach
        </ol>
        <form method="POST" action="{{ url('message/create/' . $ticket_id) }}" enctype="multipart/form-data" class="rf-tk-form rf-tk-reply">
            @csrf
            <label for="tk-reply">ردك</label>
            <textarea id="tk-reply" name="message" rows="4" required class="form-control" placeholder="اكتب رسالتك"></textarea>
            <label for="tk-file">إرفاق صورة (اختياري)</label>
            <input id="tk-file" type="file" name="file" class="form-control" accept="image/*">
            <button type="submit" class="btn btn-primary mt-3"><i class="bi bi-send" aria-hidden="true"></i> إرسال</button>
        </form>
    </div>
@endsection
