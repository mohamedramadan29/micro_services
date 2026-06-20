@extends('admin.layouts.master')

@section('title', 'تقارير البريد الإلكتروني')
@section('css')
<link rel="stylesheet" href="{{ URL::asset('assets/plugins/datatable/css/dataTables.bootstrap4.min.css') }}">
@endsection

@section('content')
<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto">تقارير البريد الإلكتروني</h4>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="card">
            <div class="card-body text-center">
                <h6 class="text-muted">إجمالي الحملات</h6>
                <h3 class="text-primary">{{ $totalCampaigns }}</h3>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="card">
            <div class="card-body text-center">
                <h6 class="text-muted">إجمالي الإيميلات المرسلة</h6>
                <h3 class="text-success">{{ $totalSent }}</h3>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="card">
            <div class="card-body text-center">
                <h6 class="text-muted">معدل الفتح</h6>
                <h3 class="text-warning">{{ $overallOpenRate }}%</h3>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="card">
            <div class="card-body text-center">
                <h6 class="text-muted">معدل النقر</h6>
                <h3 class="text-info">{{ $overallClickRate }}%</h3>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h5>حدود الإرسال اليومية</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-3">
                        <h6>مرسل اليوم</h6>
                        <h4 class="text-primary">{{ $todayCount }}</h4>
                    </div>
                    <div class="col-3">
                        <h6>آخر ساعة</h6>
                        <h4 class="text-info">{{ $lastHourCount }}</h4>
                    </div>
                    <div class="col-3">
                        <h6>عبر SMTP (اليوم)</h6>
                        <h4 class="text-success">{{ $smtpCount }}</h4>
                    </div>
                    <div class="col-3">
                        <h6>عبر Gmail API (اليوم)</h6>
                        <h4 class="text-warning">{{ $gmailCount }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5>الحملات حسب الحالة</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-4">
                        <h6>مسودة</h6>
                        <h4>{{ $campaignsByStatus['draft'] }}</h4>
                    </div>
                    <div class="col-4">
                        <h6>مجدولة</h6>
                        <h4>{{ $campaignsByStatus['scheduled'] }}</h4>
                    </div>
                    <div class="col-4">
                        <h6>قيد الإرسال</h6>
                        <h4>{{ $campaignsByStatus['sending'] }}</h4>
                    </div>
                    <div class="col-4 mt-3">
                        <h6>تم الإرسال</h6>
                        <h4>{{ $campaignsByStatus['sent'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5>آخر الحملات</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0">
                        <thead>
                            <tr>
                                <th>الاسم</th>
                                <th>الحالة</th>
                                <th>المرسل</th>
                                <th>التاريخ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentCampaigns as $campaign)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.email.campaigns.show', $campaign) }}">{{ $campaign->name }}</a>
                                </td>
                                <td><span class="badge badge-{{ $campaign->status_color }}">{{ $campaign->status_label }}</span></td>
                                <td>{{ $campaign->sent_count }}/{{ $campaign->total_recipients }}</td>
                                <td>{{ $campaign->created_at->format('Y-m-d') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
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
