@extends('admin.layouts.master')
@section('title')
    إدارة مربعات الصفحة الرئيسية
@endsection
@section('content')
    <div class="breadcrumb-header justify-content-between">
        <div class="my-auto">
            <div class="d-flex">
                <h4 class="content-title mb-0 my-auto">إدارة مربعات الصفحة الرئيسية</h4>
                <span class="text-muted mt-1 tx-13 mr-2 mb-0">/ المحتوى والترتيب والظهور والوجهات، دون تغيير البرمجة</span>
            </div>
        </div>
        <div class="my-auto d-flex flex-wrap" style="gap: 6px">
            <a href="{{ route('admin.content.index', ['page' => 'home']) }}" class="btn btn-primary"><i class="fas fa-edit"></i> تعديل المربعات</a>
            <a href="{{ route('admin.content.preview', 'home') }}" target="_blank" class="btn btn-outline-primary"><i class="fas fa-eye"></i> معاينة كزائر</a>
            <a href="{{ route('admin.home-cards.index', array_filter(['days' => $days, 'source' => $source, 'export' => 'csv'])) }}" class="btn btn-light"><i class="fas fa-file-csv"></i> تصدير CSV</a>
        </div>
    </div>
    @include('admin.catalog.partials.flash')

    <div class="row row-sm">
        <div class="col-md-4"><div class="card"><div class="card-body"><h3 class="mb-0">{{ $rows->where('group_key', '!=', 'secondary')->where('visible', true)->count() }}</h3><span class="text-muted">مربعات أساسية ظاهرة</span></div></div></div>
        <div class="col-md-4"><div class="card"><div class="card-body"><h3 class="mb-0">{{ $rows->where('group_key', 'secondary')->where('visible', true)->count() }}</h3><span class="text-muted">مربعات إضافية ظاهرة</span></div></div></div>
        <div class="col-md-4"><div class="card"><div class="card-body"><h3 class="mb-0">{{ rtrim(rtrim(number_format($commission, 2), '0'), '.') }}%</h3><span class="text-muted">عمولة المشاريع المعتمدة</span></div></div></div>
    </div>

    @if ($hasDrafts)
        <div class="alert alert-warning d-flex justify-content-between align-items-center">
            <span>توجد تعديلات غير منشورة على المربعات؛ الزوار يرون النسخة المنشورة.</span>
            @if ($access === 'publish')
                <form method="POST" action="{{ route('admin.content.publish', 'home') }}" onsubmit="return confirm('نشر التعديلات للزوار الآن؟')">@csrf<button class="btn btn-success btn-sm">حفظ ونشر التعديلات</button></form>
            @endif
        </div>
    @endif

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap" style="gap: 8px">
            <h6 class="mb-0">المربعات ونتائجها</h6>
            <form method="GET" class="d-flex flex-wrap" style="gap: 4px">
                <select name="days" class="form-control form-control-sm" style="width: auto">
                    @foreach ($periods as $value => $label)<option value="{{ $value }}" @selected($days === $value)>{{ $label }}</option>@endforeach
                </select>
                <select name="source" class="form-control form-control-sm" style="width: auto">
                    <option value="">كل مصادر الزيارات</option>
                    @foreach ($sources as $key => $label)<option value="{{ $key }}" @selected($source === $key)>{{ $label }}</option>@endforeach
                </select>
                <button class="btn btn-sm btn-primary">عرض</button>
            </form>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>المربع</th><th>المجموعة</th><th>الوجهة</th><th>يظهر لـ</th><th>الظهور</th><th>مرات العرض</th><th>النقرات</th><th>نسبة النقر</th><th>مكتمل</th><th></th></tr></thead>
                    <tbody>
                        @foreach ($rows as $row)
                            <tr>
                                <td><strong>{{ $row['title'] }}</strong>@if ($row['draft'])<span class="badge badge-warning mr-1">مسودة</span>@endif<div class="small text-muted">ID: {{ $row['block']->id }}</div></td>
                                <td>{{ $row['group'] }}</td>
                                <td>{{ $row['action'] }}@if ($row['path'])<div class="small text-muted" dir="ltr">{{ parse_url($row['path'], PHP_URL_PATH) }}</div>@endif</td>
                                <td>{{ $row['audience'] }}</td>
                                <td>
                                    @if ($access !== 'view')
                                        <form method="POST" action="{{ route('admin.content.toggle', $row['block']) }}">@csrf
                                            <button class="btn btn-sm {{ $row['visible'] ? 'btn-success' : 'btn-light' }}">{{ $row['visible'] ? 'ظاهر' : 'مخفي' }}</button>
                                        </form>
                                    @else
                                        {{ $row['visible'] ? 'ظاهر' : 'مخفي' }}
                                    @endif
                                </td>
                                <td>{{ number_format($row['views']) }}</td>
                                <td>{{ number_format($row['clicks']) }}</td>
                                <td>{{ $row['ctr'] !== null ? $row['ctr'] . '%' : '—' }}</td>
                                <td>{{ number_format($row['completed']) }}</td>
                                <td><a href="{{ route('admin.content.index', ['page' => 'home']) }}#block-{{ $row['block']->id }}" class="btn btn-sm btn-outline-primary">تعديل</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="small text-muted mt-3 mb-0">
                الأرقام من إحصائيات الزوار الذين وافقوا على التتبع فقط. «مكتمل» = زيارات ضغطت المربع ثم أكملت هدفًا: طلب عرض، نشر مشروع، تشخيص، طلب مساعدة، إكمال ملف مستقل، أو شراء.
                تغيير الظهور يُحفظ كمسودة حتى «حفظ ونشر التعديلات». الوجهات من قائمة معتمدة فقط، ويغيّرها المدير.
            </p>
        </div>
    </div>
    <div class="card">
        <div class="card-header"><h6 class="mb-0">كيف تعدّل المربعات بنفسك</h6></div>
        <div class="card-body small">
            <ol class="mb-0">
                <li>اضغط «تعديل المربعات» (أو «تعديل» بجانب المربع): تفتح «إدارة محتوى الواجهة ← الرئيسية - المربعات».</li>
                <li>غيّر العنوان (60 حرفًا) والوصف (180) ونص الزر (30) والأيقونة أو الصورة، ثم «حفظ كمسودة». لا يتغير شيء للزوار بعد.</li>
                <li><strong>وجهة الزر</strong> تُختار من قائمة الصفحات المعتمدة فقط، و<strong>المجموعة</strong> تحدد الصف (الأساسية 3 أعلى، الإضافية تحتها)، و<strong>يظهر لـ</strong> يحدد من يرى المربع. هذه الثلاثة للمدير فقط.</li>
                <li>رتّب بالأسهم (داخل المجموعة نفسها)، أو أخفِ مربعًا دون حذفه بزر «إخفاء».</li>
                <li>اضغط «معاينة كزائر» لترى المسودة على الموقع (لك وحدك)، ثم «نشر». كل نشر يُسجَّل في «سجل النشر» ويمكن للمدير استرجاع نسخة سابقة.</li>
                <li>عنوان المجموعة الثانية ووصفها في «عنوان قسم البطاقات» بنفس الصفحة.</li>
                <li>محرر المحتوى يحفظ مسودات فقط والمدير ينشر؛ دور «دعم/تحليلات» يرى هذه الصفحة دون تعديل (من «أدوار الموظفين»).</li>
            </ol>
        </div>
    </div>
@endsection
