@extends('admin.layouts.master')

@section('title', 'الحملات البريدية')
@section('css')
<link rel="stylesheet" href="{{ URL::asset('assets/plugins/datatable/css/dataTables.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ URL::asset('assets/plugins/datatable/css/buttons.bootstrap4.min.css') }}">
@endsection

@section('content')
<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto">الحملات البريدية</h4>
        </div>
    </div>
    <div class="my-auto">
        <a href="{{ route('admin.email.campaigns.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> حملة جديدة
        </a>
    </div>
</div>

<div class="row row-sm">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h5>جميع الحملات</h5>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                <div class="table-responsive">
                    <table class="table table-bordered text-nowrap" id="data-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>الاسم</th>
                                <th>القائمة</th>
                                <th>الحالة</th>
                                <th>المرسل</th>
                                <th>الفتح</th>
                                <th>النقر</th>
                                <th>التاريخ</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($campaigns as $index => $campaign)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $campaign->name }}</td>
                                <td>{{ $campaign->emailList->name ?? '—' }}</td>
                                <td>
                                    <span class="badge badge-{{ $campaign->status_color }}">{{ $campaign->status_label }}</span>
                                </td>
                                <td>{{ $campaign->sent_count }}/{{ $campaign->total_recipients }}</td>
                                <td>{{ $campaign->open_count }}</td>
                                <td>{{ $campaign->click_count }}</td>
                                <td>{{ $campaign->created_at->format('Y-m-d') }}</td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('admin.email.campaigns.show', $campaign) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if(in_array($campaign->status, ['draft', 'scheduled']))
                                        <a href="{{ route('admin.email.campaigns.edit', $campaign) }}" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endif
                                        <form action="{{ route('admin.email.campaigns.destroy', $campaign) }}" method="POST" style="display:inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('تأكيد الحذف؟')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
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
