@extends('admin.layouts.master')
@php use App\Support\StaffPermissions as P; @endphp
@section('title')
    أدوار الموظفين
@endsection
@section('content')
    <div class="breadcrumb-header justify-content-between">
        <div class="my-auto">
            <div class="d-flex">
                <h4 class="content-title mb-0 my-auto">أدوار الموظفين</h4>
                <span class="text-muted mt-1 tx-13 mr-2 mb-0">/ قرارات «مشروعك ما بيوقف»: الخلافات وتغيير المستقل</span>
            </div>
        </div>
    </div>
    @include('admin.catalog.partials.flash')
    <div class="card">
        <div class="card-body">
            <ul class="small text-muted">
                <li><strong>مدير:</strong> كل الصلاحيات، ويعتمد أي قرار.</li>
                <li><strong>محرر محتوى:</strong> يعدّل نصوص الواجهة والمربعات كمسودة، ولا يغيّر وجهات الأزرار ولا ينشر؛ النشر والاسترجاع للمدير.</li>
                <li><strong>دعم / تحليلات:</strong> يطّلع على الطلبات والتقارير والإحصائيات دون أي تعديل.</li>
                <li><strong>مراجع خلافات:</strong> يقرر في الخلافات؛ قراراته المالية (تحرير، استرجاع، تقسيم، تغيير المستقل) تُحفظ كمقترح يعتمده المحاسب أو المدير.</li>
                <li><strong>محاسب:</strong> ينفّذ القرارات المالية ويعتمد المقترحات حتى حد الموافقة؛ ما فوقه يعتمده المدير.</li>
            </ul>
            <form method="POST" action="{{ route('admin.staff.update') }}">
                @csrf
                <table class="table table-sm">
                    <thead><tr><th>الموظف</th><th>البريد</th><th>الدور</th></tr></thead>
                    <tbody>
                        @foreach ($admins as $admin)
                            <tr>
                                <td>{{ $admin->name }}</td>
                                <td dir="ltr" class="text-right">{{ $admin->email }}</td>
                                <td>
                                    <select name="roles[{{ $admin->id }}]" class="form-control form-control-sm">
                                        @foreach (P::ROLES as $key => $label)<option value="{{ $key }}" @selected(P::role($admin) === $key)>{{ $label }}</option>@endforeach
                                    </select>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="form-group">
                    <label>حد الموافقة (ج.م): القرارات المالية فوقه تحتاج موافقة المدير</label>
                    <input type="number" name="threshold" min="0" step="0.01" class="form-control" style="max-width: 240px" value="{{ old('threshold', $threshold) }}">
                    <small class="text-muted">0 = بلا حد (المحاسب يعتمد أي مبلغ).</small>
                </div>
                <button class="btn btn-primary">حفظ</button>
            </form>
        </div>
    </div>
@endsection
