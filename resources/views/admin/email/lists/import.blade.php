@extends('admin.layouts.master')

@section('title', 'استيراد CSV')
@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>استيراد CSV</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-left">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.email.lists.index') }}">القوائم البريدية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.email.lists.show', $emailList) }}">{{ $emailList->name }}</a></li>
                        <li class="breadcrumb-item active">استيراد</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">استيراد جهات اتصال إلى: {{ $emailList->name }}</h3>
                </div>
                <form action="{{ route('admin.email.lists.import.store', $emailList) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        @if($errors->any())
                            <div class="alert alert-danger">{{ $errors->first() }}</div>
                        @endif
                        <div class="alert alert-info">
                            <strong>ملحوظة:</strong> ملف CSV يجب أن يحتوي على عمود <code>email</code> على الأقل.
                            يمكن إضافة أعمدة <code>name</code> و <code>phone</code> وأي حقول إضافية أخرى.
                        </div>
                        <div class="form-group">
                            <label for="csv_file">ملف CSV <span class="text-danger">*</span></label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="csv_file" name="csv_file" accept=".csv,.txt" required>
                                <label class="custom-file-label" for="csv_file">اختر ملف</label>
                            </div>
                            @error('csv_file')<div class="text-danger">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-success"><i class="fas fa-upload"></i> استيراد</button>
                        <a href="{{ route('admin.email.lists.show', $emailList) }}" class="btn btn-secondary">إلغاء</a>
                    </div>
                </form>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h3 class="card-title">استيراد من Google Sheets</h3>
                </div>
                <div class="card-body">
                    @php
                        $sheetsService = app(\App\Services\GoogleSheetsService::class);
                        $sheetsReady = $sheetsService->isReady();
                    @endphp
                    <div class="alert alert-info">
                        يمكنك استيراد جهات الاتصال من Google Sheets. يجب أن يحتوي الجدول على عمود <code>email</code> على الأقل.
                    </div>
                    @if(!$sheetsReady)
                    <div class="alert alert-warning">
                        لم يتم ربط Google Sheets بعد.
                        <a href="{{ route('admin.email.sheets.auth') }}" class="btn btn-sm btn-primary mr-2">
                            <i class="fab fa-google"></i> ربط Google Sheets
                        </a>
                    </div>
                    @else
                    <div class="alert alert-success">
                        ✅ Google Sheets متصل وجاهز للاستيراد.
                        <a href="{{ route('admin.email.gmail.settings') }}" class="btn btn-sm btn-link">إدارة الاتصال</a>
                    </div>
                    @endif
                    <form action="{{ route('admin.email.lists.import.sheets', $emailList) }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="spreadsheet_id">رابط أو معرّف الجدول (Spreadsheet ID) <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="spreadsheet_id" name="spreadsheet_id"
                                        placeholder="https://docs.google.com/spreadsheets/d/... أو 1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs74OgVE2TzM">
                                    <small class="text-muted">انسخ الرابط بالكامل أو معرّف الجدول (المعرف هو الجزء الطويل بعد /d/)</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="range">نطاق الخلايا (اختياري)</label>
                                    <input type="text" class="form-control" id="range" name="range" placeholder="A:Z" value="A:Z">
                                    <small class="text-muted">مثال: A:Z أو Sheet1!A:Z</small>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="is_public" name="is_public" value="1" checked>
                                        <label class="custom-control-label" for="is_public">الجدول عام (Anyone with link)</label>
                                    </div>
                                    <small class="text-muted">إذا كان الجدول عاماً، لا نحتاج إلى OAuth</small>
                                </div>
                            </div>
                            <div class="col-md-6" id="gid-field" style="display:none;">
                                <div class="form-group">
                                    <label for="gid">GID (رقم الورقة)</label>
                                    <input type="text" class="form-control" id="gid" name="gid" value="0">
                                    <small class="text-muted">موجود في رابط الجدول بعد #gid=</small>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success"><i class="fab fa-google"></i> استيراد من Sheets</button>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@section('js')
<script>
    $('.custom-file-input').on('change', function() {
        var fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').html(fileName);
    });

    $('#is_public').on('change', function() {
        $('#gid-field').toggle(this.checked);
    }).trigger('change');

    $('#spreadsheet_id').on('input', function() {
        var val = $(this).val();
        var match = val.match(/\/d\/([a-zA-Z0-9_-]+)/);
        if (match) {
            $(this).val(match[1]);
        }
    });
</script>
@endsection
