{{-- Shown to freelancers the admin has not approved yet (client file «تعديلات», items 03 and 11). --}}
@php $me = auth()->user(); @endphp
@if ($me && $me->isFreelancer() && ! $me->isApprovedFreelancer())
    <div class="rf-notice {{ $me->freelancer_status === \App\Models\User::FREELANCER_REJECTED ? 'rf-notice-error' : '' }}" role="status">
        @if ($me->freelancer_status === \App\Models\User::FREELANCER_REJECTED)
            <strong>لم يُعتمد حسابك كمستقل.</strong>
            @if ($me->freelancer_review_note) {{ $me->freelancer_review_note }} @endif
            عدّل ملفك ثم أعد إرساله للمراجعة.
        @else
            <strong>حسابك كمستقل قيد المراجعة.</strong>
            تستطيع تصفح الطلبات، وتُفتح لك العروض بعد تأكيد بريدك الإلكتروني واعتماد فريق نفّذها لملفك.
        @endif
        <a href="{{ route('freelancer.profile.edit') }}">ملفي كمستقل</a>
    </div>
@endif
