@extends('admin.layouts.master')

@section('title', 'تعديل متابعة')
@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endsection

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>تعديل متابعة: {{ $emailFollowUp->name }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-left">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.email.campaigns.index') }}">الحملات</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.email.campaigns.show', $emailCampaign) }}">{{ $emailCampaign->name }}</a></li>
                        <li class="breadcrumb-item active">تعديل متابعة</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">تعديل: {{ $emailFollowUp->name }}</h3>
                    <a href="{{ route('admin.email.campaigns.show', $emailCampaign) }}" class="btn btn-secondary btn-sm float-left">
                        <i class="fas fa-arrow-left"></i> رجوع
                    </a>
                </div>
                <form action="{{ route('admin.email.follow-ups.update', [$emailCampaign, $emailFollowUp]) }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">اسم المتابعة <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $emailFollowUp->name) }}" required>
                                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="trigger_type">نوع المحفز <span class="text-danger">*</span></label>
                                    <select class="form-control @error('trigger_type') is-invalid @enderror" id="trigger_type" name="trigger_type" required>
                                        <option value="no_open" {{ old('trigger_type', $emailFollowUp->trigger_type) == 'no_open' ? 'selected' : '' }}>لم يفتح الإيميل</option>
                                        <option value="no_click" {{ old('trigger_type', $emailFollowUp->trigger_type) == 'no_click' ? 'selected' : '' }}>لم ينقر على الرابط</option>
                                    </select>
                                    @error('trigger_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="delay_days">بعد كم يوم؟ <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('delay_days') is-invalid @enderror" id="delay_days" name="delay_days" value="{{ old('delay_days', $emailFollowUp->delay_days) }}" min="1" required>
                                    @error('delay_days')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="template_id">قالب الإيميل (اختياري)</label>
                                    <select class="form-control" id="template_id" name="template_id">
                                        <option value="">بدون قالب</option>
                                        @foreach($templates as $template)
                                            <option value="{{ $template->id }}" {{ old('template_id', $emailFollowUp->template_id) == $template->id ? 'selected' : '' }}>{{ $template->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="sort_order">الترتيب في التسلسل</label>
                                    <input type="number" class="form-control" id="sort_order" name="sort_order" value="{{ old('sort_order', $emailFollowUp->sort_order ?? 0) }}" min="0" max="999">
                                    <small class="text-muted">يحدد ترتيب هذه المتابعة في السلسلة (0 = الأولى)</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="is_active">الحالة</label>
                                    <div class="custom-control custom-switch" style="margin-top: 10px;">
                                        <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" {{ old('is_active', $emailFollowUp->is_active) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="is_active">مفعلة</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="subject">عنوان الإيميل (Subject) <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('subject') is-invalid @enderror" id="subject" name="subject" value="{{ old('subject', $emailFollowUp->subject) }}" required>
                                    @error('subject')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="body">محتوى الإيميل <span class="text-danger">*</span></label>
                                    <div>
                                        <button type="button" class="btn btn-dark btn-sm mb-2" data-toggle="modal" data-target="#videoEmbedModal"><i class="fas fa-video"></i> إدراج فيديو</button>
                                    </div>
                                    <textarea class="form-control tinymce @error('body') is-invalid @enderror" id="body" name="body" rows="15">{{ old('body', $emailFollowUp->body) }}</textarea>
                                    @error('body')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> حفظ التغييرات</button>
                        <a href="{{ route('admin.email.campaigns.show', $emailCampaign) }}" class="btn btn-secondary"><i class="fas fa-times"></i> إلغاء</a>
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
    $(document).ready(function() {
        $('#template_id').on('change', function() {
            var templateId = $(this).val();
            if (templateId) {
                $.get('{{ url('admin/email/templates') }}/' + templateId, function(template) {
                    $('#subject').val(template.subject);
                    if (tinymce.get('body').getContent() === '') {
                        tinymce.get('body').setContent(template.body);
                    }
                });
            }
        });

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
