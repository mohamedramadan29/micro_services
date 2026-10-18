{{-- Generic notification (data: text, url). Opening it marks it read. --}}
<li>
    <a class="dropdown-item" href="{{ route('notifications.open', $notification->id) }}">
        {{ $notification['data']['text'] ?? '' }}
        <br>
        <span class="timer"> <i class="fa fa-clock"></i> {{ $notification->created_at->diffForHumans() }} </span>
    </a>
</li>
<hr>
