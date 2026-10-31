@extends('admin.layouts.master')
@section('title')
    إعدادات قسم الشركات
@endsection
@section('content')
    <div class="breadcrumb-header justify-content-between">
        <div class="my-auto"><div class="d-flex"><h4 class="content-title mb-0 my-auto">نفّذها للأعمال</h4><span class="text-muted mt-1 tx-13 mr-2 mb-0">/ الإعدادات — كل شيء يتغير من هنا دون تعديل الكود</span></div></div>
    </div>
    <div class="row row-sm">
        <div class="col-lg-6"><div class="card"><div class="card-body">
            <h6>الخدمات والأسئلة والخطوات (النموذج الموجّه)</h6>
            <p class="text-muted">النموذج الموجّه نموذج عادي في «نماذج الخدمات»: كل قسم = خطوة. أضف أو احذف أو رتّب الأسئلة، غيّر خيارات «الخدمات المطلوبة»، واضبط الإلزامية وقواعد الظهور (مثل: إذا اختار «تقطيع بودكاست» أظهر مدة الحلقة). بيانات الشركة (الاسم، الحجم، الهاتف، البريد) ثابتة في الصفحة.</p>
            @if ($form)
                <a href="{{ route('admin.request-forms.builder', $form) }}" class="btn btn-primary">فتح منشئ النموذج ({{ $form->questions_count }} سؤال · {{ $form->statusLabel() }})</a>
                <a href="{{ route('admin.request-forms.preview', $form) }}" class="btn btn-light">معاينة</a>
                @unless ($form->isPublished())<p class="text-danger small mt-2 mb-0">النموذج غير منشور، فزر «حدّد طلبك خطوة بخطوة» مخفي من الصفحة.</p>@endunless
            @elseif ($service)
                <a href="{{ route('admin.request-forms.show', $service) }}" class="btn btn-primary">إنشاء النموذج</a>
            @else
                <p class="text-danger">لم يُعثر على خدمة النموذج المخفية. شغّل: <code>php artisan db:seed --class=BusinessFormSeeder</code></p>
            @endif
        </div></div></div>
        <div class="col-lg-6"><div class="card"><div class="card-body">
            <h6>نصوص الصفحة والبطاقات</h6>
            <p class="text-muted">العنوان، سطر الخدمات، بطاقتا المسارين وأزرارهما، مثال الطلب الحر وأقصى طول له، نص الموافقة، رسالة النجاح، بطاقات أحجام الشركات، و«ماذا يحدث بعد الإرسال».</p>
            <a href="{{ route('admin.content.index', ['page' => 'business']) }}" class="btn btn-primary">إدارة محتوى الواجهة ← نفّذها للأعمال</a>
            <hr>
            <h6>رسائل الإشعارات والبريد</h6>
            <p class="text-muted">نص كل إشعار (الاستلام، عرض السعر، النسخة الجاهزة، الاعتماد…) من «نصوص الإشعارات ← نفّذها للأعمال»، وإرساله بالبريد أو لا من الإعدادات العامة.</p>
            <a href="{{ route('admin.notification-texts.index') }}" class="btn btn-light">نصوص الإشعارات</a>
            <hr>
            <h6>الرابط في القائمة الرئيسية</h6>
            <p class="text-muted mb-0">رابط «للشركات» من «إدارة محتوى الواجهة ← Navbar».</p>
        </div></div></div>
    </div>
@endsection
