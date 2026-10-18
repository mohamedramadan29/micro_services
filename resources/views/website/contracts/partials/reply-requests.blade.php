{{-- «طلب رد من الطرفين»: the team's questions with a deadline; each side answers here. --}}
@php $replyRequests = $contract->replyRequests; @endphp
@if ($replyRequests->isNotEmpty())
    <div class="rf-card-section mt-4" id="reply-requests">
        <h2 class="rf-section-title">أسئلة إدارة نفّذها</h2>
        @foreach ($replyRequests as $reply)
            @php
                $side = $isClient ? 'client' : ($isFreelancer ? 'freelancer' : null);
                $mine = $side ? $reply->{$side . '_reply'} : null;
            @endphp
            <div class="rf-addendum {{ $reply->isOpen() ? 'rf-addendum-proposed' : '' }}">
                <div class="rf-change-head">
                    <strong>{{ $reply->question }}</strong>
                    <span class="rf-status">{{ $reply->isOpen() ? 'الرد حتى ' . $reply->due_at->format('Y-m-d') : 'مغلق' }}</span>
                </div>
                @if ($isAdmin || $side === 'client')
                    <p class="mb-1"><span class="rf-muted">رد العميل:</span> {{ $reply->client_reply ?? '— لم يرد بعد' }}</p>
                @endif
                @if ($isAdmin || $side === 'freelancer')
                    <p class="mb-1"><span class="rf-muted">رد المستقل:</span> {{ $reply->freelancer_reply ?? '— لم يرد بعد' }}</p>
                @endif
                @if ($side && $reply->isOpen())
                    <form method="POST" action="{{ route('contracts.replies.answer', [$contract, $reply]) }}" class="mt-2">
                        @csrf
                        <textarea name="reply" rows="2" maxlength="3000" class="form-control mb-1" placeholder="اكتب ردك لإدارة نفّذها">{{ $mine }}</textarea>
                        <button class="btn rf-btn-primary btn-sm">{{ $mine ? 'تعديل ردي' : 'إرسال ردي' }}</button>
                    </form>
                @endif
                @if ($isAdmin && $reply->isOpen())
                    <form method="POST" action="{{ route('contracts.replies.close', [$contract, $reply]) }}" class="mt-1">@csrf<button class="btn rf-btn-outline btn-sm">إغلاق الطلب</button></form>
                @endif
            </div>
        @endforeach
    </div>
@endif
