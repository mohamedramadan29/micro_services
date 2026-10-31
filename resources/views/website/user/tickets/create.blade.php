@extends('website.user.layouts.account')
@section('title')تذكرة جديدة@endsection
@section('account_parent')<li class="breadcrumb-item"><a href="{{ url('tickets') }}">تذاكري</a></li>@endsection
@section('account_title')تذكرة جديدة@endsection
@section('account')
    <div class="rf-acc-card">
        <h3>تذكرة جديدة</h3>
        <p class="rf-acc-empty mt-0">اكتب مشكلتك أو سؤالك بالتفصيل، ويرد عليك فريق نفّذها هنا.</p>
        <form action="{{ url('ticket/create') }}" method="POST" class="rf-tk-form" data-track-start="ticket">
            @csrf
            <label for="tk-title">عنوان التذكرة</label>
            <input id="tk-title" class="form-control" name="title" required maxlength="190" value="{{ old('title') }}" placeholder="مثال: مشكلة في الدفع">
            <label for="tk-message">رسالتك</label>
            <textarea id="tk-message" name="message" rows="6" required class="form-control" placeholder="اشرح ما حدث، ورقم الطلب إن وجد">{{ old('message') }}</textarea>
            <button type="submit" class="btn btn-primary mt-3"><i class="bi bi-send" aria-hidden="true"></i> إرسال التذكرة</button>
        </form>
    </div>
@endsection
