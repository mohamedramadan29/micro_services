{{-- Tabs and the Search Console connection box, shared by both SEO pages. --}}
<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto">إدارة SEO والكلمات المفتاحية</h4>
            <span class="text-muted mt-1 tx-13 mr-2 mb-0">/ الكلمات المتابَعة وما يبحث عنه الناس في Google</span>
        </div>
    </div>
</div>
@include('admin.catalog.partials.flash')

<div class="d-flex flex-wrap mb-3" style="gap: 6px">
    <a href="{{ route('admin.seo.index') }}" class="btn {{ request()->routeIs('admin.seo.index') ? 'btn-primary' : 'btn-light' }}">الكلمات المفتاحية</a>
    <a href="{{ route('admin.seo.queries') }}" class="btn {{ request()->routeIs('admin.seo.queries') ? 'btn-primary' : 'btn-light' }}">ما يبحث عنه الناس في Google</a>
</div>

<div class="card">
    <div class="card-body d-flex justify-content-between align-items-center flex-wrap" style="gap: 8px">
        @if ($connected)
            <div>
                <strong class="text-success"><i class="fas fa-check-circle"></i> مربوط بـ Google Search Console</strong>
                <div class="text-muted small">آخر تحديث: {{ $lastSync ? $lastSync->format('Y-m-d H:i') : 'لم يُحدَّث بعد' }} · البيانات لآخر 28 يومًا وتتحدث يوميًا تلقائيًا.</div>
            </div>
            <form method="POST" action="{{ route('admin.seo.sync') }}">@csrf<button class="btn btn-outline-primary"><i class="fas fa-sync"></i> تحديث الآن</button></form>
        @else
            <div>
                <strong class="text-warning"><i class="fas fa-plug"></i> بيانات Google غير مربوطة بعد</strong>
                <div class="text-muted small">لن تظهر أرقام حتى يكتمل الربط؛ لا نعرض تقديرات على أنها بيانات حقيقية.</div>
                <details class="small mt-1">
                    <summary>خطوات الربط (مجانية)</summary>
                    <ol class="mb-0 mt-1">
                        <li>في Google Cloud Console: أنشئ <strong>Service Account</strong> وفعّل <strong>Google Search Console API</strong>، ثم نزّل مفتاح JSON.</li>
                        <li>في Search Console ← الإعدادات ← المستخدمون: أضف بريد الـ Service Account بصلاحية «مقيّد».</li>
                        <li>ضع ملف المفتاح على السيرفر، وفي <code>.env</code>: <code>GSC_CREDENTIALS=/المسار/key.json</code> و <code>GSC_SITE_URL=sc-domain:nafizha.com</code>.</li>
                    </ol>
                </details>
            </div>
        @endif
    </div>
</div>
