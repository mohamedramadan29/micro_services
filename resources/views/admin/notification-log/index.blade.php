@extends('admin.layouts.master')
@section('title')
    سجل الإشعارات
@endsection
@section('content')
    <div class="breadcrumb-header justify-content-between">
        <div class="my-auto">
            <div class="d-flex">
                <h4 class="content-title mb-0 my-auto">سجل الإشعارات</h4>
                <span class="text-muted mt-1 tx-13 mr-2 mb-0">/ هل وصل كل إشعار (الجرس والبريد) أم فشل — آخر 90 يومًا</span>
            </div>
        </div>
    </div>
    @if ($failed)
        <div class="alert alert-danger">{{ $failed }} إشعار فشل إرساله في آخر 7 أيام. راجع إعدادات البريد (SMTP) وتشغيل <code>queue:work</code>.</div>
    @endif
    <div class="card">
        <div class="card-body">
            <form method="GET" class="d-flex flex-wrap mb-3" style="gap: 6px">
                <select name="status" class="form-control" style="max-width: 160px">
                    <option value="">كل الحالات</option>
                    <option value="sent" @selected(request('status') === 'sent')>أُرسل</option>
                    <option value="failed" @selected(request('status') === 'failed')>فشل</option>
                </select>
                <select name="channel" class="form-control" style="max-width: 160px">
                    <option value="">كل القنوات</option>
                    <option value="database" @selected(request('channel') === 'database')>جرس الموقع</option>
                    <option value="mail" @selected(request('channel') === 'mail')>البريد</option>
                </select>
                <button class="btn btn-light">تطبيق</button>
            </form>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead><tr><th>الوقت</th><th>الحالة</th><th>القناة</th><th>إلى</th><th>الإشعار</th><th>السبب</th></tr></thead>
                    <tbody>
                        @forelse ($logs as $log)
                            <tr>
                                <td class="text-nowrap">{{ $log->created_at->format('Y-m-d H:i') }}</td>
                                <td><span class="badge {{ $log->status === 'failed' ? 'badge-danger' : 'badge-success' }}">{{ $log->status === 'failed' ? 'فشل' : 'أُرسل' }}</span></td>
                                <td>{{ $log->channel === 'mail' ? 'البريد' : ($log->channel === 'database' ? 'الجرس' : $log->channel) }}</td>
                                <td>{{ $log->recipient ?? '—' }}</td>
                                <td>{{ $log->text ?? $log->notification }}</td>
                                <td class="small text-danger">{{ $log->error }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">لا شيء بعد.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $logs->links() }}</div>
        </div>
    </div>
@endsection
