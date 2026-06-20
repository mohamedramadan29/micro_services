@extends('admin.layouts.master')

@section('title', 'القوائم البريدية')
@section('css')
<link rel="stylesheet" href="{{ URL::asset('assets/plugins/datatable/css/dataTables.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ URL::asset('assets/plugins/datatable/css/buttons.bootstrap4.min.css') }}">
@endsection

@section('content')
<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto">القوائم البريدية</h4>
        </div>
    </div>
    <div class="my-auto">
        <a href="{{ route('admin.email.lists.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> قائمة جديدة
        </a>
    </div>
</div>

<div class="row row-sm">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h5>جميع القوائم البريدية</h5>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                <div class="table-responsive">
                    <table class="table table-bordered text-nowrap" id="data-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>الاسم</th>
                                <th>جهات الاتصال</th>
                                <th>الحالة</th>
                                <th>تاريخ الإنشاء</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($emailLists as $index => $list)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $list->name }}</td>
                                <td>{{ $list->contacts_count ?? 0 }}</td>
                                <td>
                                    @if($list->is_active)
                                        <span class="badge badge-success">نشط</span>
                                    @else
                                        <span class="badge badge-danger">غير نشط</span>
                                    @endif
                                </td>
                                <td>{{ $list->created_at->format('Y-m-d') }}</td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('admin.email.lists.show', $list) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.email.lists.edit', $list) }}" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.email.lists.destroy', $list) }}" method="POST" style="display:inline">
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
