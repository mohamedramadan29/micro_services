@extends('admin.layouts.master')
@section('title')
    مكتبة الصور والخلفيات
@endsection
@section('css')
    <style>
        .md-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 14px; }
        .md-item { border: 1px solid #e6ebf0; border-radius: 10px; overflow: hidden; background: #fff; }
        .md-item img { width: 100%; height: 140px; object-fit: cover; background: #f5f7fa; display: block; }
        .md-body { padding: 10px; }
        .md-muted { color: #6b7785; font-size: 12px; }
    </style>
@endsection
@section('content')
    <div class="breadcrumb-header justify-content-between">
        <div class="my-auto">
            <div class="d-flex">
                <h4 class="content-title mb-0 my-auto">مكتبة الصور والخلفيات</h4>
                <span class="text-muted mt-1 tx-13 mr-2 mb-0">/ تُستخدم في محتوى الواجهة</span>
            </div>
        </div>
        <div class="my-auto">
            <a href="{{ route('admin.content.index') }}" class="btn btn-light"><i class="fas fa-arrow-right"></i> إدارة محتوى الواجهة</a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
    @endif

    <div class="card">
        <div class="card-header"><h6 class="mb-0">رفع صورة</h6></div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.media.store') }}" enctype="multipart/form-data" class="row align-items-end">
                @csrf
                <div class="form-group col-md-4">
                    <label>الصورة (jpg, png, webp — حتى 4 ميجابايت)</label>
                    <input type="file" name="file" accept=".jpg,.jpeg,.png,.webp" class="form-control" required>
                </div>
                <div class="form-group col-md-4">
                    <label>وصف الصورة (يظهر لقارئات الشاشة ومحركات البحث)</label>
                    <input type="text" name="alt" class="form-control" maxlength="255">
                </div>
                <div class="form-group col-md-2">
                    <label>النوع</label>
                    <select name="kind" class="form-control">
                        @foreach (\App\Models\ContentMedia::KINDS as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-2">
                    <button class="btn btn-primary btn-block">رفع</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h6 class="mb-0">الصور ({{ $media->total() }})</h6></div>
        <div class="card-body">
            @if ($media->isEmpty())
                <p class="md-muted mb-0">لا توجد صور بعد.</p>
            @else
                <div class="md-grid">
                    @foreach ($media as $item)
                        <div class="md-item">
                            <img src="{{ $item->url() }}" alt="{{ $item->alt }}" loading="lazy">
                            <div class="md-body">
                                <div class="md-muted">
                                    {{ \App\Models\ContentMedia::KINDS[$item->kind] ?? $item->kind }}
                                    @if ($item->width) · {{ $item->width }}×{{ $item->height }} @endif
                                    @if (in_array($item->id, $used, true)) · <span class="text-success">مستخدمة</span> @endif
                                </div>
                                <form method="POST" action="{{ route('admin.media.update', $item) }}" class="d-flex mt-2" style="gap: 4px">
                                    @csrf
                                    <input type="text" name="alt" value="{{ $item->alt }}" class="form-control form-control-sm" placeholder="وصف الصورة">
                                    <button class="btn btn-sm btn-light">حفظ</button>
                                </form>
                                <form method="POST" action="{{ route('admin.media.destroy', $item) }}" class="mt-2" onsubmit="return confirm('حذف هذه الصورة نهائيًا؟')">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-danger btn-block" @disabled(in_array($item->id, $used, true))>حذف</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-3">{{ $media->links() }}</div>
            @endif
        </div>
    </div>
@endsection
