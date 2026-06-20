@extends('admin.layouts.master')

@section('title', 'إعدادات Gmail API')
@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>إعدادات Gmail API</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-left">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.email.analytics') }}">البريد الإلكتروني</a></li>
                        <li class="breadcrumb-item active">إعدادات Gmail</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            @if($isConnected)
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> تم ربط Gmail API بنجاح. يمكنك الآن اختيار "Gmail API" كطريقة إرسال في الحملات.
            </div>
            @else
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Gmail API غير متصل. قم بتحميل ملف JSON الخاص بمشروع Google API ثم فوض الوصول.
            </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">بيانات اعتماد Google API</h3>
                </div>
                <div class="card-body">
                    <p>للاستفادة من Gmail API:</p>
                    <ol>
                        <li>اذهب إلى <a href="https://console.cloud.google.com/" target="_blank">Google Cloud Console</a></li>
                        <li>أنشئ مشروعاً جديداً أو اختر مشروعاً موجوداً</li>
                        <li>فعّل <strong>Gmail API</strong></li>
                        <li>اذهب إلى <strong>Credentials</strong> وأنشئ OAuth 2.0 Client ID (Web application)</li>
                        <li>أضف <code>{{ route('admin.email.gmail.callback') }}</code> في <strong>Authorized redirect URIs</strong></li>
                        <li>حمّل ملف JSON للـ credentials وألصق محتواه في الحقل أدناه</li>
                    </ol>

                    <form action="{{ route('admin.email.gmail.update') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="gmail_credentials_json">محتوى ملف JSON <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('gmail_credentials_json') is-invalid @enderror" id="gmail_credentials_json" name="gmail_credentials_json" rows="10" placeholder='{"web":{"client_id":"...","client_secret":"...",...}}'>{{ old('gmail_credentials_json', $setting->gmail_credentials_json ?? '') }}</textarea>
                            @error('gmail_credentials_json')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> حفظ الإعدادات</button>
                    </form>
                </div>
            </div>

            @if($setting && $setting->gmail_credentials_json)
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">تفويض الوصول</h3>
                </div>
                <div class="card-body">
                    @if($isConnected)
                        <p>Gmail API متصل وجاهز للإرسال.</p>
                        <form action="{{ route('admin.email.gmail.disconnect') }}" method="POST" style="display:inline">
                            @csrf
                            <button type="submit" class="btn btn-danger" onclick="return confirm('إلغاء ربط Gmail؟')">
                                <i class="fas fa-unlink"></i> فصل Gmail
                            </button>
                        </form>
                    @else
                        @if($authUrl)
                            <a href="{{ $authUrl }}" class="btn btn-success btn-lg">
                                <i class="fab fa-google"></i> تفويض الوصول إلى Gmail
                            </a>
                        @else
                            <p class="text-muted">يرجى حفظ بيانات JSON أولاً لتفعيل زر التفويض.</p>
                        @endif
                    @endif
                </div>
            </div>
            @endif
        </div>
    </section>
</div>
@endsection
