{{-- Client ↔ site team conversation about this request (client side). Expects $serviceRequest with adminMessages. --}}
<div class="_dashboard_content" id="team-messages">
    <div class="_dashboard_content_header">
        <div class="_dashboard__header_flex">
            <h4><i class="bi bi-headset mr-1"></i> تواصل مع فريق نفّذها</h4>
        </div>
    </div>
    <div class="_dashboard_content_body rf-chat">
        <p class="rf-muted mb-0">لأي سؤال عن طلبك أو الأسعار. هذه الرسائل بينك وبين فريقنا فقط، ولا يراها المستقلون.</p>
        @if ($serviceRequest->adminMessages->isNotEmpty())
            <div class="rf-messages" role="log" aria-label="رسائل فريق نفّذها">
                @foreach ($serviceRequest->adminMessages as $message)
                    <div class="rf-message {{ $message->from_admin ? '' : 'rf-message-mine' }}">
                        <span class="rf-message-author">{{ $message->from_admin ? 'فريق نفّذها' : 'أنت' }}</span>
                        <p>{{ $message->body }}</p>
                        <span class="rf-message-time">{{ $message->created_at->format('Y-m-d H:i') }}</span>
                    </div>
                @endforeach
            </div>
        @endif
        <form method="POST" action="{{ route('requests.messages.store', $serviceRequest) }}" class="mt-2">
            @csrf
            <label for="team-body" class="visually-hidden">رسالتك لفريق نفّذها</label>
            <textarea id="team-body" name="body" rows="3" maxlength="3000" class="form-control" required placeholder="اكتب سؤالك أو ردك…">{{ old('body') }}</textarea>
            @error('body')<div class="rf-field-error">{{ $message }}</div>@enderror
            <div class="rf-submit-actions mt-2"><button type="submit" class="btn rf-btn-primary">إرسال</button></div>
        </form>
    </div>
</div>
