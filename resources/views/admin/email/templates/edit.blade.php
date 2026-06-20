@extends('admin.layouts.master')

@section('title', 'تعديل القالب')
@section('css')
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
.ai-btn { margin-right: 5px; }
.ai-loading { opacity: 0.6; pointer-events: none; }
.ai-spinner { display: none; }
.ai-loading .ai-spinner { display: inline-block; }
.ai-loading .ai-text { display: none; }
</style>
@endsection
@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>تعديل القالب</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-left">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.email.templates.index') }}">القوالب</a></li>
                        <li class="breadcrumb-item active">تعديل</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">تعديل: {{ $emailTemplate->name }}</h3>
                    <a href="{{ route('admin.email.templates.index') }}" class="btn btn-secondary btn-sm float-left">
                        <i class="fas fa-arrow-left"></i> رجوع
                    </a>
                </div>
                <form action="{{ route('admin.email.templates.update', $emailTemplate) }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">اسم القالب <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $emailTemplate->name) }}" required>
                                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="custom-control custom-switch" style="margin-top: 38px;">
                                        <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" {{ old('is_active', $emailTemplate->is_active) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="is_active">نشط</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="subject">عنوان الإيميل (Subject) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="text" class="form-control @error('subject') is-invalid @enderror" id="subject" name="subject" value="{{ old('subject', $emailTemplate->subject) }}" required>
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-info ai-btn ai-subject-btn" onclick="generateSubject()">
                                                <span class="ai-spinner"><i class="fas fa-spinner fa-spin"></i></span>
                                                <span class="ai-text"><i class="fas fa-magic"></i> AI</span>
                                            </button>
                                        </div>
                                    </div>
                                    @error('subject')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="body">محتوى الإيميل <span class="text-danger">*</span></label>
                                    <div>
                                        <button type="button" class="btn btn-info btn-sm mb-2 ai-btn ai-body-btn" onclick="generateBody()">
                                            <span class="ai-spinner"><i class="fas fa-spinner fa-spin"></i></span>
                                            <span class="ai-text"><i class="fas fa-magic"></i> كتابة المحتوى بالذكاء الاصطناعي</span>
                                        </button>
                                        <button type="button" class="btn btn-success btn-sm mb-2" onclick="improveText()"><i class="fas fa-pen-fancy"></i> تحسين النص</button>
                                        <button type="button" class="btn btn-warning btn-sm mb-2" onclick="translateText()"><i class="fas fa-language"></i> ترجمة</button>
                                        <button type="button" class="btn btn-dark btn-sm mb-2" data-toggle="modal" data-target="#videoEmbedModal"><i class="fas fa-video"></i> إدراج فيديو</button>
                                    </div>
                                    <textarea class="form-control tinymce @error('body') is-invalid @enderror" id="body" name="body" rows="15">{{ old('body', $emailTemplate->body) }}</textarea>
                                    @error('body')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> حفظ</button>
                        <a href="{{ route('admin.email.templates.index') }}" class="btn btn-secondary"><i class="fas fa-times"></i> إلغاء</a>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>
<div class="modal fade" id="videoEmbedModal" tabindex="-1" role="dialog" aria-labelledby="videoEmbedModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="videoEmbedModalLabel"><i class="fas fa-video"></i> إدراج فيديو</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="video_url">رابط الفيديو (YouTube أو Vimeo)</label>
                    <input type="url" class="form-control" id="video_url" placeholder="https://www.youtube.com/watch?v=...">
                    <small class="form-text text-muted">سيتم عرض الفيديو كصورة مصغرة داخل الإيميل، وعند الضغط يفتح في نافذة جديدة.</small>
                    <div id="video-preview" class="mt-3" style="display:none;"></div>
                    <div id="video-error" class="mt-2 text-danger" style="display:none;"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-primary" id="insertVideoBtn" onclick="insertVideoEmbed()"><i class="fas fa-plus"></i> إدراج</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    function generateSubject() {
        var context = prompt('أدخل وصفاً للحملة أو الإيميل (مثلاً: عرض خاص على منتجات العناية بالبشرة بخصم 30%):');
        if (!context) return;

        var btn = $('.ai-subject-btn');
        btn.addClass('ai-loading');

        $.post('{{ route('admin.email.ai.generate') }}', {
            action: 'subject',
            context: context,
            tone: 'professional'
        }, function(res) {
            if (res.success) {
                $('#subject').val(res.data);
            }
        }).fail(function(xhr) {
            var msg = xhr.responseJSON?.message || 'حدث خطأ';
            alert(msg);
        }).always(function() {
            btn.removeClass('ai-loading');
        });
    }

    function generateBody() {
        var context = prompt('أدخل تفاصيل الإيميل المطلوب (مثلاً: إيميل ترحيبي للعملاء الجدد مع خصم 20% على أول طلب):');
        if (!context) return;

        var subject = $('#subject').val();
        if (!subject) {
            alert('يرجى كتابة عنوان الإيميل أولاً');
            return;
        }

        var btn = $('.ai-body-btn');
        btn.addClass('ai-loading');

        $.post('{{ route('admin.email.ai.generate') }}', {
            action: 'body',
            context: context,
            subject: subject,
            tone: 'professional'
        }, function(res) {
            if (res.success) {
                if (tinymce.get('body')) {
                    tinymce.get('body').setContent(res.data);
                } else {
                    $('#body').val(res.data);
                }
            }
        }).fail(function(xhr) {
            var msg = xhr.responseJSON?.message || 'حدث خطأ';
            alert(msg);
        }).always(function() {
            btn.removeClass('ai-loading');
        });
    }

    function improveText() {
        var editor = tinymce.get('body');
        var text = editor ? editor.getContent() : $('#body').val();
        if (!text) {
            alert('يرجى كتابة محتوى أولاً');
            return;
        }

        var instruction = prompt('ماذا تريد أن تفعل؟ (مثلاً: اجعل النص أكثر احترافية / قصّر النص / أضف دعوة لاتخاذ إجراء):');
        if (!instruction) return;

        $.post('{{ route('admin.email.ai.generate') }}', {
            action: 'improve',
            text: text,
            instruction: instruction
        }, function(res) {
            if (res.success) {
                if (editor) {
                    editor.setContent(res.data);
                } else {
                    $('#body').val(res.data);
                }
            }
        }).fail(function(xhr) {
            var msg = xhr.responseJSON?.message || 'حدث خطأ';
            alert(msg);
        });
    }

    function translateText() {
        var editor = tinymce.get('body');
        var text = editor ? editor.getContent() : $('#body').val();
        if (!text) {
            alert('يرجى كتابة محتوى أولاً');
            return;
        }

        var lang = prompt('أدخل اللغة المستهدفة (مثلاً: English, French, Arabic):');
        if (!lang) return;

        $.post('{{ route('admin.email.ai.generate') }}', {
            action: 'translate',
            text: text,
            language: lang
        }, function(res) {
            if (res.success) {
                if (editor) {
                    editor.setContent(res.data);
                } else {
                    $('#body').val(res.data);
                }
            }
        }).fail(function(xhr) {
            var msg = xhr.responseJSON?.message || 'حدث خطأ';
            alert(msg);
        });
    }

    $(function() {
        var previewTimer;
        $('#video_url').on('input', function() {
            clearTimeout(previewTimer);
            var url = $(this).val();
            if (!url) { $('#video-preview').hide(); return; }
            previewTimer = setTimeout(function() {
                $.post('{{ route('admin.email.ai.embed-video') }}', { url: url }, function(res) {
                    if (res.success) {
                        $('#video-preview').html(res.html).show();
                        $('#video-error').hide();
                    }
                }).fail(function(xhr) {
                    $('#video-preview').hide();
                });
            }, 800);
        });
    });

    function insertVideoEmbed() {
        var url = $('#video_url').val();
        if (!url) { alert('يرجى إدخال رابط الفيديو'); return; }
        var btn = $('#insertVideoBtn');
        btn.html('<i class="fas fa-spinner fa-spin"></i> جاري التحميل...').prop('disabled', true);
        $('#video-error').hide();
        $.post('{{ route('admin.email.ai.embed-video') }}', { url: url }, function(res) {
            if (res.success) {
                var editor = tinymce.get('body');
                if (editor) editor.insertContent(res.html);
                else $('#body').val($('#body').val() + res.html);
                $('#videoEmbedModal').modal('hide');
                $('#video_url').val('');
                $('#video-preview').hide();
            }
        }).fail(function(xhr) {
            var msg = xhr.responseJSON?.message || 'حدث خطأ في تحميل الفيديو';
            $('#video-error').text(msg).show();
        }).always(function() {
            btn.html('<i class="fas fa-plus"></i> إدراج').prop('disabled', false);
        });
    }
</script>
@endsection
