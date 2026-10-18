{{-- Admin bell: unread notifications that carry a site link (request alerts, inquiries, refunds). --}}
@php
    $adminNotifications = Auth::user()->unreadNotifications->filter(fn ($n) => isset($n->data['url']));
@endphp
<div class="dropdown nav-item main-header-notification">
    <a class="new nav-link" href="#" data-toggle="dropdown" aria-label="الإشعارات">
        <svg xmlns="http://www.w3.org/2000/svg" class="header-icon-svgs" viewBox="0 0 24 24" fill="none" stroke="currentColor"
            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
        </svg>
        @if ($adminNotifications->isNotEmpty())
            <span class="badge badge-danger" style="position:absolute; top:6px; left:2px; font-size:10px">{{ $adminNotifications->count() }}</span>
        @endif
    </a>
    <div class="dropdown-menu">
        <div class="menu-header-content bg-primary text-right">
            <div class="d-flex align-items-center">
                <h6 class="dropdown-title mb-1 tx-15 text-white font-weight-semibold">الإشعارات</h6>
                @if ($adminNotifications->isNotEmpty())
                    <form method="POST" action="{{ route('admin.notifications.read-all') }}" class="mr-auto">
                        @csrf
                        <button type="submit" class="badge badge-pill badge-warning border-0">قراءة الكل</button>
                    </form>
                @endif
            </div>
            <p class="dropdown-title-text subtext mb-0 text-white op-6 pb-0 tx-12">{{ $adminNotifications->count() }} غير مقروء</p>
        </div>
        <div class="main-notification-list Notification-scroll">
            @forelse ($adminNotifications->take(15) as $notification)
                @php $tone = $notification->data['tone'] ?? null; @endphp
                <a class="d-flex p-3 border-bottom" href="{{ route('notifications.open', $notification->id) }}"
                    @if ($tone) style="border-right: 4px solid {{ ['success' => '#22c55e', 'danger' => '#ef4444', 'info' => '#3b82f6'][$tone] ?? '#3b82f6' }};" @endif>
                    <div class="mr-3">
                        <h5 class="notification-label mb-1" style="white-space: normal">{{ $notification->data['text'] ?? '' }}</h5>
                        @if (! empty($notification->data['details']))
                            <div class="notification-subtext" style="white-space: normal">{{ $notification->data['details'] }}</div>
                        @endif
                        <div class="notification-subtext">{{ $notification->created_at->diffForHumans() }}</div>
                    </div>
                </a>
            @empty
                <p class="p-3 mb-0 text-muted text-center">لا توجد إشعارات جديدة</p>
            @endforelse
        </div>
        <div class="dropdown-footer">
            <a href="{{ route('admin.requests.index', ['tab' => 'followup']) }}">تنبيهات المتابعة</a>
        </div>
    </div>
</div>
