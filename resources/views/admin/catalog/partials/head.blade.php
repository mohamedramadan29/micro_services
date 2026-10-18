{{-- $title, $subtitle --}}
<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto">{{ $title }}</h4>
            <span class="text-muted mt-1 tx-13 mr-2 mb-0">/ {{ $subtitle }}</span>
        </div>
    </div>
    <div class="my-auto d-flex" style="gap: 6px">
        <a href="{{ route('admin.content.preview', 'services') }}" target="_blank" class="btn btn-outline-primary"><i class="fas fa-eye"></i> معاينة صفحة الخدمات</a>
    </div>
</div>
@include('admin.catalog.partials.flash')
