@extends('admin.layouts.master')

@section('title', $emailCampaign->name)
@section('css')
<link rel="stylesheet" href="{{ URL::asset('assets/plugins/datatable/css/dataTables.bootstrap4.min.css') }}">
@endsection

@section('content')
<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto">{{ $emailCampaign->name }}</h4>
        </div>
    </div>
    <div class="my-auto">
        @if(in_array($emailCampaign->status, ['draft', 'scheduled']))
            <a href="{{ route('admin.email.campaigns.send', $emailCampaign) }}" class="btn btn-success" onclick="return confirm('تأكيد إرسال الحملة؟')">
                <i class="fas fa-paper-plane"></i> إرسال الآن
            </a>
            <a href="{{ route('admin.email.campaigns.edit', $emailCampaign) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> تعديل
            </a>
        @endif
        <form action="{{ route('admin.email.campaigns.destroy', $emailCampaign) }}" method="POST" style="display:inline">
            @csrf
            <button type="submit" class="btn btn-danger" onclick="return confirm('تأكيد الحذف؟')">
                <i class="fas fa-trash"></i> حذف
            </button>
        </form>
        <a href="{{ route('admin.email.campaigns.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> رجوع
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h5>معلومات الحملة</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <strong>الحالة:</strong>
                        <span class="badge badge-{{ $emailCampaign->status_color }}">{{ $emailCampaign->status_label }}</span>
                    </div>
                    <div class="col-md-3">
                        <strong>القائمة:</strong> {{ $emailCampaign->emailList->name ?? '—' }}
                    </div>
                    <div class="col-md-3">
                        <strong>المستلمين:</strong> {{ $emailCampaign->total_recipients }}
                    </div>
                    <div class="col-md-3">
                        <strong>التتبع:</strong>
                        @if($emailCampaign->tracking_enabled)
                            <span class="badge badge-success">مفعل</span>
                        @else
                            <span class="badge badge-secondary">غير مفعل</span>
                        @endif
                    </div>
                </div>
                @if($emailCampaign->scheduled_at)
                <div class="row mt-2">
                    <div class="col-md-12">
                        <strong>مجدولة في:</strong> {{ $emailCampaign->scheduled_at->format('Y-m-d H:i') }}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@if(in_array($emailCampaign->status, ['sent', 'sending']))
<div class="row">
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="card">
            <div class="card-body text-center">
                <h6 class="text-muted">تم الإرسال</h6>
                <h3 class="text-primary">{{ $emailCampaign->sent_count }}/{{ $emailCampaign->total_recipients }}</h3>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="card">
            <div class="card-body text-center">
                <h6 class="text-muted">نسبة الفتح</h6>
                <h3 class="text-success">
                    @if($emailCampaign->sent_count > 0)
                        {{ round(($emailCampaign->open_count / $emailCampaign->sent_count) * 100, 1) }}%
                    @else
                        0%
                    @endif
                </h3>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="card">
            <div class="card-body text-center">
                <h6 class="text-muted">نسبة النقر</h6>
                <h3 class="text-warning">
                    @if($emailCampaign->sent_count > 0)
                        {{ round(($emailCampaign->click_count / $emailCampaign->sent_count) * 100, 1) }}%
                    @else
                        0%
                    @endif
                </h3>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="card">
            <div class="card-body text-center">
                <h6 class="text-muted">إجمالي الفتح</h6>
                <h3 class="text-info">{{ $emailCampaign->open_count }}</h3>
            </div>
        </div>
    </div>
</div>
@endif

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h5>معاينة الإيميل</h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <th style="width:150px;">الموضوع</th>
                        <td>{{ $emailCampaign->subject }}</td>
                    </tr>
                    <tr>
                        <th>المرسل</th>
                        <td>{{ $emailCampaign->sender_name }} &lt;{{ $emailCampaign->sender_email }}&gt;</td>
                    </tr>
                    @if($emailCampaign->cc_email)
                    <tr>
                        <th>CC</th>
                        <td>{{ $emailCampaign->cc_email }}</td>
                    </tr>
                    @endif
                    @if($emailCampaign->bcc_email)
                    <tr>
                        <th>BCC</th>
                        <td>{{ $emailCampaign->bcc_email }}</td>
                    </tr>
                    @endif
                    @if($emailCampaign->has_attachment && $emailCampaign->attachment_name)
                    <tr>
                        <th>المرفقات</th>
                        <td>
                            <i class="fas fa-paperclip"></i> {{ $emailCampaign->attachment_name }}
                        </td>
                    </tr>
                    @endif
                </table>
                <hr>
                <div class="border p-3" style="background:#f9f9f9; min-height:200px;">
                    {!! $emailCampaign->body !!}
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h5>المتابعات (Follow-ups)</h5>
                <a href="{{ route('admin.email.follow-ups.create', $emailCampaign) }}" class="btn btn-primary btn-sm float-left">
                    <i class="fas fa-plus"></i> إضافة متابعة
                </a>
            </div>
            <div class="card-body">
                @if($emailCampaign->followUps->isEmpty())
                    <p class="text-muted">لا توجد متابعات. يمكنك إضافة متابعات لإرسال إيميلات تلقائية لمن لم يفتح أو ينقر.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>الاسم</th>
                                    <th>المحفز</th>
                                    <th>بعد</th>
                                    <th>الموضوع</th>
                                    <th>الحالة</th>
                                    <th>إجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($emailCampaign->followUps as $fu)
                                <tr>
                                    <td>{{ $fu->name }}</td>
                                    <td>{{ $fu->trigger_label }}</td>
                                    <td>{{ $fu->delay_days }} يوم</td>
                                    <td>{{ $fu->subject }}</td>
                                    <td>
                                        @if($fu->is_active)
                                            <span class="badge badge-success">مفعلة</span>
                                        @else
                                            <span class="badge badge-secondary">متوقفة</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.email.follow-ups.edit', [$emailCampaign, $fu]) }}" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
                                        <form action="{{ route('admin.email.follow-ups.destroy', [$emailCampaign, $fu]) }}" method="POST" style="display:inline">
                                            @csrf
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('تأكيد الحذف؟')"><i class="fas fa-trash"></i></button>
                                        </form>
                                        <form action="{{ route('admin.email.follow-ups.toggle', [$emailCampaign, $fu]) }}" method="POST" style="display:inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm {{ $fu->is_active ? 'btn-secondary' : 'btn-success' }}">
                                                <i class="fas {{ $fu->is_active ? 'fa-pause' : 'fa-play' }}"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h5>المستلمين ({{ $recipients->total() }})</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered text-nowrap" id="data-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>البريد الإلكتروني</th>
                                <th>الاسم</th>
                                <th>الحالة</th>
                                <th>فتح</th>
                                <th>نقر</th>
                                <th>تاريخ الإرسال</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recipients as $index => $r)
                            <tr>
                                <td>{{ $recipients->firstItem() + $index }}</td>
                                <td>{{ $r->email }}</td>
                                <td>{{ $r->name ?? '—' }}</td>
                                <td>
                                    @php
                                        $statusMap = ['pending'=>'secondary', 'sent'=>'success', 'failed'=>'danger', 'bounced'=>'warning', 'unsubscribed'=>'info'];
                                    @endphp
                                    <span class="badge badge-{{ $statusMap[$r->status] ?? 'secondary' }}">
                                        {{ $r->status }}
                                    </span>
                                </td>
                                <td>{{ $r->opened_count > 0 ? 'نعم' : 'لا' }}</td>
                                <td>{{ $r->click_count > 0 ? 'نعم' : 'لا' }}</td>
                                <td>{{ $r->sent_at ? $r->sent_at->format('Y-m-d H:i') : '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $recipients->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="{{ URL::asset('assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ URL::asset('assets/plugins/datatable/js/dataTables.bootstrap4.min.js') }}"></script>
<script>
    $(document).ready(function() {
        $('#data-table').DataTable({
            responsive: true,
            language: { url: "//cdn.datatables.net/plug-ins/1.10.19/i18n/Arabic.json" }
        });
    });
</script>
@endsection
