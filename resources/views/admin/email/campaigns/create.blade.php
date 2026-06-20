@extends('admin.layouts.master')

@section('title', 'حملة جديدة')
@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
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
                    <h1>حملة جديدة</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-left">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.email.campaigns.index') }}">الحملات</a></li>
                        <li class="breadcrumb-item active">إضافة</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">إنشاء حملة جديدة</h3>
                    <a href="{{ route('admin.email.campaigns.index') }}" class="btn btn-secondary btn-sm float-left">
                        <i class="fas fa-arrow-left"></i> رجوع
                    </a>
                </div>
                <form action="{{ route('admin.email.campaigns.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">اسم الحملة <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email_list_id">القائمة البريدية <span class="text-danger">*</span></label>
                                    <select class="form-control @error('email_list_id') is-invalid @enderror" id="email_list_id" name="email_list_id" required>
                                        <option value="">اختر القائمة...</option>
                                        @foreach($lists as $list)
                                            <option value="{{ $list->id }}" {{ old('email_list_id') == $list->id ? 'selected' : '' }}>
                                                {{ $list->name }} ({{ $list->contacts_count ?? 0 }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('email_list_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="template_id">قالب الإيميل (اختياري)</label>
                                    <select class="form-control @error('template_id') is-invalid @enderror" id="template_id" name="template_id">
                                        <option value="">بدون قالب</option>
                                        @foreach($templates as $template)
                                            <option value="{{ $template->id }}" {{ old('template_id') == $template->id ? 'selected' : '' }}>
                                                {{ $template->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('template_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="scheduled_at">جدولة الإرسال (اختياري)</label>
                                    <input type="text" class="form-control flatpickr @error('scheduled_at') is-invalid @enderror" id="scheduled_at" name="scheduled_at" value="{{ old('scheduled_at') }}" placeholder="اترك فارغاً للإرسال الفوري">
                                    @error('scheduled_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="subject">عنوان الإيميل (Subject) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="text" class="form-control @error('subject') is-invalid @enderror" id="subject" name="subject" value="{{ old('subject') }}" required>
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
                                    <textarea class="form-control tinymce @error('body') is-invalid @enderror" id="body" name="body" rows="15">{{ old('body') }}</textarea>
                                    @error('body')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>

                        <hr>
                        <h5>إعدادات إضافية</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="sender_name">اسم المرسل</label>
                                    <input type="text" class="form-control" id="sender_name" name="sender_name" value="{{ old('sender_name', config('mail.from.name')) }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="sender_email">بريد المرسل</label>
                                    <input type="email" class="form-control" id="sender_email" name="sender_email" value="{{ old('sender_email', config('mail.from.address')) }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="reply_to">الرد على (Reply-To)</label>
                                    <input type="email" class="form-control" id="reply_to" name="reply_to" value="{{ old('reply_to') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="cc_email">CC (نسخة)</label>
                                    <input type="email" class="form-control" id="cc_email" name="cc_email" value="{{ old('cc_email') }}" placeholder="cc@example.com">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="bcc_email">BCC (نسخة مخفية)</label>
                                    <input type="email" class="form-control" id="bcc_email" name="bcc_email" value="{{ old('bcc_email') }}" placeholder="bcc@example.com">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="custom-control custom-switch" style="margin-top: 10px;">
                                        <input type="checkbox" class="custom-control-input" id="tracking_enabled" name="tracking_enabled" value="1" {{ old('tracking_enabled', '1') ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="tracking_enabled">تفعيل التتبع (فتح/نقر)</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="mailer">طريقة الإرسال</label>
                                    <select class="form-control" id="mailer" name="mailer">
                                        <option value="smtp" {{ old('mailer', 'smtp') == 'smtp' ? 'selected' : '' }}>SMTP (البريد العادي)</option>
                                        <option value="gmail" {{ old('mailer') == 'gmail' ? 'selected' : '' }}>Gmail API</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="attachment">المرفقات (ملف واحد، حد أقصى 10MB)</label>
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="attachment" name="attachment">
                                        <label class="custom-file-label" for="attachment">اختر ملف...</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr>
                        <h5>التحكم في الإرسال (Throttling & Drip)</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="send_interval_seconds">الوقت بين كل إيميل (ثواني)</label>
                                    <input type="number" class="form-control" id="send_interval_seconds" name="send_interval_seconds" value="{{ old('send_interval_seconds', 10) }}" min="1" max="3600">
                                    <small class="text-muted">تأخير بين إرسال كل إيميل والآخر لتجنب السبام</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="throttle_per_hour">الحد الأقصى في الساعة</label>
                                    <input type="number" class="form-control" id="throttle_per_hour" name="throttle_per_hour" value="{{ old('throttle_per_hour', 100) }}" min="1" max="10000">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="daily_limit">الحد الأقصى اليومي</label>
                                    <input type="number" class="form-control" id="daily_limit" name="daily_limit" value="{{ old('daily_limit', 500) }}" min="1" max="100000">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="custom-control custom-switch" style="margin-top: 10px;">
                                        <input type="checkbox" class="custom-control-input" id="drip_enabled" name="drip_enabled" value="1" {{ old('drip_enabled') ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="drip_enabled">تفعيل الإرسال المتقطع (Drip)</label>
                                    </div>
                                    <small class="text-muted">تقسيم الحملة على عدة دفعات متباعدة زمنياً</small>
                                </div>
                            </div>
                        </div>
                        <div id="drip-fields" style="display:none;">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="drip_total_parts">عدد الدفعات</label>
                                        <input type="number" class="form-control" id="drip_total_parts" name="drip_total_parts" value="{{ old('drip_total_parts', 3) }}" min="2" max="100">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="drip_interval_hours">المدة بين كل دفعة (ساعات)</label>
                                        <input type="number" class="form-control" id="drip_interval_hours" name="drip_interval_hours" value="{{ old('drip_interval_hours', 24) }}" min="1" max="720">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr>
                        <h5>A/B Testing (اختياري)</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="custom-control custom-switch" style="margin-top: 10px;">
                                        <input type="checkbox" class="custom-control-input" id="ab_testing_enabled" name="ab_testing_enabled" value="1" {{ old('ab_testing_enabled') ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="ab_testing_enabled">تفعيل A/B Testing</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="ab-fields" style="display:none;">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="ab_subject_b">العنوان البديل (B) <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="ab_subject_b" name="ab_subject_b" value="{{ old('ab_subject_b') }}">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="ab_split_percent">نسبة المجموعة A (%)</label>
                                        <input type="number" class="form-control" id="ab_split_percent" name="ab_split_percent" value="{{ old('ab_split_percent', 50) }}" min="10" max="90">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>توزيع المجموعات</label>
                                        <p class="form-control-static" id="ab-preview">A: 50% / B: 50%</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr>
                        <h5>تصفية جهات الاتصال (اختياري)</h5>
                        <p class="text-muted">أضف شروطاً لفلترة جهات الاتصال التي ستستلم الإيميل. اترك فارغاً للإرسال للكل.</p>
                        <div id="filters-container">
                            <div class="row filter-row mb-2">
                                <div class="col-md-4">
                                    <input type="text" class="form-control filter-field" placeholder="اسم الحقل (مثلاً: city)">
                                </div>
                                <div class="col-md-3">
                                    <select class="form-control filter-operator">
                                        <option value="=">يساوي</option>
                                        <option value="!=">لا يساوي</option>
                                        <option value=">">أكبر من</option>
                                        <option value="<">أصغر من</option>
                                        <option value=">=">أكبر أو يساوي</option>
                                        <option value="<=">أصغر أو يساوي</option>
                                        <option value="contains">يحتوي على</option>
                                        <option value="not_contains">لا يحتوي على</option>
                                        <option value="starts_with">يبدأ بـ</option>
                                        <option value="ends_with">ينتهي بـ</option>
                                        <option value="is_empty">فارغ</option>
                                        <option value="is_not_empty">غير فارغ</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <input type="text" class="form-control filter-value" placeholder="القيمة">
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-danger remove-filter"><i class="fas fa-times"></i></button>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-success" id="add-filter"><i class="fas fa-plus"></i> إضافة شرط</button>
                        <input type="hidden" name="filters" id="filters-input" value="">
                    </div>
                    <div class="card-footer">
                        <button type="submit" name="action" value="save" class="btn btn-primary">
                            <i class="fas fa-save"></i> حفظ كمسودة
                        </button>
                        <button type="submit" name="action" value="save_and_send" class="btn btn-success">
                            <i class="fas fa-paper-plane"></i> حفظ وإرسال
                        </button>
                        <a href="{{ route('admin.email.campaigns.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> إلغاء
                        </a>
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
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    $(document).ready(function() {
        $('.flatpickr').flatpickr({
            enableTime: true,
            dateFormat: 'Y-m-d H:i',
            locale: 'ar'
        });

        $('#ab_testing_enabled').on('change', function() {
            $('#ab-fields').toggle(this.checked);
        }).trigger('change');

        $('#drip_enabled').on('change', function() {
            $('#drip-fields').toggle(this.checked);
        }).trigger('change');

        $('#ab_split_percent').on('input', function() {
            var a = parseInt($(this).val()) || 50;
            var b = 100 - a;
            $('#ab-preview').text('A: ' + a + '% / B: ' + b + '%');
        });

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

        function updateFiltersInput() {
            var filters = [];
            $('.filter-row').each(function() {
                var field = $(this).find('.filter-field').val();
                var operator = $(this).find('.filter-operator').val();
                var value = $(this).find('.filter-value').val();
                if (field) {
                    filters.push({field: field, operator: operator, value: value});
                }
            });
            $('#filters-input').val(JSON.stringify(filters));
        }

        $('#add-filter').on('click', function() {
            var row = $('.filter-row:first').clone();
            row.find('input').val('');
            row.find('select').val('=');
            row.find('.remove-filter').show();
            $('#filters-container').append(row);
            updateFiltersInput();
        });

        $(document).on('click', '.remove-filter', function() {
            if ($('.filter-row').length > 1) {
                $(this).closest('.filter-row').remove();
                updateFiltersInput();
            }
        });

        $(document).on('change keyup', '.filter-field, .filter-operator, .filter-value', function() {
            updateFiltersInput();
        });

        $('form').on('submit', function() {
            updateFiltersInput();
        });

        $('#attachment').on('change', function() {
            var fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').html(fileName);
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

    function generateSubject() {
        var context = prompt('أدخل وصفاً للحملة أو الإيميل (مثلاً: عرض خاص على منتجات العناية بالبشرة بخصم 30%):');
        if (!context) return;
        var btn = $('.ai-subject-btn');
        btn.addClass('ai-loading');
        $.post('{{ route('admin.email.ai.generate') }}', { action: 'subject', context: context, tone: 'professional' }, function(res) {
            if (res.success) $('#subject').val(res.data);
        }).fail(function(xhr) { alert(xhr.responseJSON?.message || 'حدث خطأ'); }).always(function() { btn.removeClass('ai-loading'); });
    }

    function generateBody() {
        var context = prompt('أدخل تفاصيل الإيميل المطلوب (مثلاً: إيميل ترحيبي للعملاء الجدد مع خصم 20% على أول طلب):');
        if (!context) return;
        var subject = $('#subject').val();
        if (!subject) { alert('يرجى كتابة عنوان الإيميل أولاً'); return; }
        var btn = $('.ai-body-btn');
        btn.addClass('ai-loading');
        $.post('{{ route('admin.email.ai.generate') }}', { action: 'body', context: context, subject: subject, tone: 'professional' }, function(res) {
            if (res.success) { if (tinymce.get('body')) tinymce.get('body').setContent(res.data); else $('#body').val(res.data); }
        }).fail(function(xhr) { alert(xhr.responseJSON?.message || 'حدث خطأ'); }).always(function() { btn.removeClass('ai-loading'); });
    }

    function improveText() {
        var editor = tinymce.get('body');
        var text = editor ? editor.getContent() : $('#body').val();
        if (!text) { alert('يرجى كتابة محتوى أولاً'); return; }
        var instruction = prompt('ماذا تريد أن تفعل؟ (مثلاً: اجعل النص أكثر احترافية / قصّر النص / أضف دعوة لاتخاذ إجراء):');
        if (!instruction) return;
        $.post('{{ route('admin.email.ai.generate') }}', { action: 'improve', text: text, instruction: instruction }, function(res) {
            if (res.success) { if (editor) editor.setContent(res.data); else $('#body').val(res.data); }
        }).fail(function(xhr) { alert(xhr.responseJSON?.message || 'حدث خطأ'); });
    }

    function translateText() {
        var editor = tinymce.get('body');
        var text = editor ? editor.getContent() : $('#body').val();
        if (!text) { alert('يرجى كتابة محتوى أولاً'); return; }
        var lang = prompt('أدخل اللغة المستهدفة (مثلاً: English, French, Arabic):');
        if (!lang) return;
        $.post('{{ route('admin.email.ai.generate') }}', { action: 'translate', text: text, language: lang }, function(res) {
            if (res.success) { if (editor) editor.setContent(res.data); else $('#body').val(res.data); }
        }).fail(function(xhr) { alert(xhr.responseJSON?.message || 'حدث خطأ'); });
    }

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
