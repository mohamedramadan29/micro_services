@extends('admin.layouts.master')

@section('title', $emailList->name)
@section('css')
<link rel="stylesheet" href="{{ URL::asset('assets/admin/plugins/datatable/css/dataTables.bootstrap4.min.css') }}">
@endsection

@section('content')
<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto">{{ $emailList->name }}</h4>
        </div>
    </div>
    <div class="my-auto">
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addContactModal">
            <i class="fas fa-plus"></i> إضافة جهة اتصال
        </button>
        <form action="{{ route('admin.email.lists.import.users', $emailList) }}" method="POST" style="display:inline">
            @csrf
            <button type="submit" class="btn btn-info" onclick="return confirm('استيراد جميع مستخدمين الموقع النشطين؟')">
                <i class="fas fa-users"></i> استيراد من المستخدمين
            </button>
        </form>
        <a href="{{ route('admin.email.lists.import', $emailList) }}" class="btn btn-success">
            <i class="fas fa-file-csv"></i> استيراد CSV
        </a>
        <a href="{{ route('admin.email.lists.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> رجوع
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h5>معلومات القائمة</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <strong>الاسم:</strong> {{ $emailList->name }}
                    </div>
                    <div class="col-md-4">
                        <strong>جهات الاتصال:</strong> {{ $emailList->contacts_count ?? 0 }}
                    </div>
                    <div class="col-md-4">
                        <strong>الحالة:</strong>
                        @if($emailList->is_active)
                            <span class="badge badge-success">نشط</span>
                        @else
                            <span class="badge badge-danger">غير نشط</span>
                        @endif
                    </div>
                </div>
                @if($emailList->description)
                <div class="row mt-3">
                    <div class="col-md-12">
                        <strong>الوصف:</strong> {{ $emailList->description }}
                    </div>
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
                <h5>جهات الاتصال ({{ $contacts->total() }})</h5>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                @if($contacts->isEmpty())
                    <div class="text-center py-5">
                        <i class="fas fa-users" style="font-size: 64px; color: #ccc;"></i>
                        <h5 class="mt-3">لا توجد جهات اتصال</h5>
                        <p class="text-muted">أضف جهات اتصال يدوياً، أو استوردهم من ملف CSV، أو استورد مستخدمين الموقع.</p>
                    </div>
                @else
                <div class="table-responsive">
                    <table class="table table-bordered text-nowrap" id="data-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>البريد الإلكتروني</th>
                                <th>الاسم</th>
                                <th>رقم الهاتف</th>
                                <th>الحالة</th>
                                <th>تاريخ الإضافة</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($contacts as $index => $contact)
                            <tr>
                                <td>{{ $contacts->firstItem() + $index }}</td>
                                <td>{{ $contact->email }}</td>
                                <td>{{ $contact->name ?? '—' }}</td>
                                <td>{{ $contact->phone ?? '—' }}</td>
                                <td>
                                    @if($contact->unsubscribed_at)
                                        <span class="badge badge-danger">ملغي</span>
                                    @elseif($contact->is_active)
                                        <span class="badge badge-success">نشط</span>
                                    @else
                                        <span class="badge badge-secondary">غير نشط</span>
                                    @endif
                                </td>
                                <td>{{ $contact->created_at->format('Y-m-d') }}</td>
                                <td>
                                    <form action="{{ route('admin.email.lists.contact.destroy', [$emailList, $contact]) }}" method="POST" style="display:inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('تأكيد الحذف؟')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $contacts->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Add Contact Modal --}}
<div class="modal fade" id="addContactModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('admin.email.lists.contact.store', $emailList) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">إضافة جهة اتصال</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>البريد الإلكتروني <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="form-group">
                        <label>الاسم</label>
                        <input type="text" class="form-control" name="name">
                    </div>
                    <div class="form-group">
                        <label>رقم الهاتف</label>
                        <input type="text" class="form-control" name="phone">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">إضافة</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">إلغاء</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="{{ URL::asset('assets/admin/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ URL::asset('assets/admin/plugins/datatable/js/dataTables.bootstrap4.min.js') }}"></script>
<script>
    $(document).ready(function() {
        $('#data-table').DataTable({
            responsive: true,
            language: { url: "//cdn.datatables.net/plug-ins/1.10.19/i18n/Arabic.json" }
        });
    });
</script>
@endsection
