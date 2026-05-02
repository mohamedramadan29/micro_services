@extends('admin.layouts.master')

@section('content')
<div class="main-content app-content mt-0" dir="rtl">
    <div class="side-app">
        <div class="main-container container-fluid">

            <div class="page-header">
                <h1 class="page-title">إنشاء بوست جديد</h1>
                <div>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.social.index') }}">السوشيال ميديا</a></li>
                        <li class="breadcrumb-item active">بوست جديد</li>
                    </ol>
                </div>
            </div>

            @include('admin.layouts.alerts.errors')

            <form action="{{ route('admin.social.post.store') }}" method="POST" enctype="multipart/form-data" id="postForm">
                @csrf
                <div class="row">

                    {{-- Left: Post Editor --}}
                    <div class="col-md-8">

                        {{-- Content --}}
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-pen ml-2"></i> محتوى البوست</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label class="form-label">العنوان (اختياري)</label>
                                    <input type="text" name="title" class="form-control" placeholder="عنوان البوست (لـ YouTube فقط)"
                                           value="{{ old('title') }}">
                                </div>
                                <div class="form-group mb-0">
                                    <label class="form-label">نص البوست <span class="text-danger">*</span></label>
                                    <textarea name="content" id="postContent" class="form-control" rows="6"
                                              placeholder="اكتب محتوى البوست هنا..." required>{{ old('content') }}</textarea>
                                    <div class="d-flex justify-content-between mt-1">
                                        <small class="text-muted">الحد الأقصى: 5000 حرف</small>
                                        <small class="text-muted"><span id="charCount">0</span> / 5000</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Media Type + Upload --}}
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-photo-video ml-2"></i> نوع المحتوى والميديا</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label class="form-label">نوع المحتوى <span class="text-danger">*</span></label>
                                    <div class="row text-center">
                                        @php
                                            $mediaTypes = [
                                                'text'  => ['label' => 'نص فقط',  'icon' => 'fas fa-align-left',  'color' => '#6c757d'],
                                                'image' => ['label' => 'صورة',    'icon' => 'fas fa-image',       'color' => '#0d6efd'],
                                                'video' => ['label' => 'فيديو',   'icon' => 'fas fa-video',       'color' => '#dc3545'],
                                                'reel'  => ['label' => 'ريلز',    'icon' => 'fas fa-film',        'color' => '#E4405F'],
                                                'story' => ['label' => 'ستوري',   'icon' => 'fas fa-circle-notch','color' => '#fd7e14'],
                                            ];
                                        @endphp
                                        @foreach($mediaTypes as $key => $mt)
                                        <div class="col">
                                            <input type="radio" name="media_type" id="mt_{{ $key }}" value="{{ $key }}"
                                                   class="d-none media-type-radio" {{ old('media_type', 'text') === $key ? 'checked' : '' }}>
                                            <label for="mt_{{ $key }}" class="media-type-label btn btn-light btn-block border"
                                                   style="cursor:pointer; transition:all .2s">
                                                <i class="{{ $mt['icon'] }} d-block mb-1" style="font-size:1.5rem; color:{{ $mt['color'] }}"></i>
                                                <small>{{ $mt['label'] }}</small>
                                            </label>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>

                                {{-- File Upload --}}
                                <div id="mediaUploadSection" class="{{ old('media_type', 'text') === 'text' ? 'd-none' : '' }}">
                                    <div class="form-group mb-0">
                                        <label class="form-label">رفع الملفات</label>
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" name="media[]" id="mediaFiles"
                                                   multiple accept="image/*,video/*">
                                            <label class="custom-file-label text-right" for="mediaFiles">اختر الملفات...</label>
                                        </div>
                                        <small class="text-muted mt-1 d-block">
                                            صور: JPG, PNG, GIF | فيديو: MP4, MOV | الحد الأقصى: 500MB لكل ملف
                                        </small>
                                    </div>

                                    {{-- Preview --}}
                                    <div id="mediaPreview" class="row mt-3"></div>
                                </div>
                            </div>
                        </div>

                    </div>

                    {{-- Right: Platforms + Schedule --}}
                    <div class="col-md-4">

                        {{-- Platform Selection --}}
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-share-alt ml-2"></i> اختر المنصات</h3>
                            </div>
                            <div class="card-body">
                                @php
                                    $platformDefs = [
                                        'facebook'  => ['name'=>'Facebook',  'icon'=>'fab fa-facebook-f',  'color'=>'#1877F2'],
                                        'instagram' => ['name'=>'Instagram', 'icon'=>'fab fa-instagram',   'color'=>'#E4405F'],
                                        'tiktok'    => ['name'=>'TikTok',    'icon'=>'fab fa-tiktok',      'color'=>'#010101'],
                                        'youtube'   => ['name'=>'YouTube',   'icon'=>'fab fa-youtube',     'color'=>'#FF0000'],
                                        'linkedin'  => ['name'=>'LinkedIn',  'icon'=>'fab fa-linkedin-in', 'color'=>'#0077B5'],
                                        'twitter'   => ['name'=>'Twitter',   'icon'=>'fab fa-twitter',     'color'=>'#1DA1F2'],
                                    ];
                                @endphp

                                @if($accounts->isEmpty())
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle ml-1"></i>
                                        لا يوجد حسابات مربوطة.
                                        <a href="{{ route('admin.social.accounts') }}">اربط حساباتك الآن</a>
                                    </div>
                                @else
                                    @foreach($platformDefs as $key => $def)
                                    @php $accs = $accounts[$key] ?? collect(); @endphp
                                    <div class="platform-check-item p-2 mb-2 rounded border {{ $accs->isEmpty() ? 'op-5' : '' }}"
                                         style="cursor: {{ $accs->isEmpty() ? 'not-allowed' : 'pointer' }}">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" name="platforms[]"
                                                   id="plt_{{ $key }}" value="{{ $key }}"
                                                   {{ $accs->isEmpty() ? 'disabled' : '' }}
                                                   {{ in_array($key, (array)old('platforms', [])) ? 'checked' : '' }}>
                                            <label class="custom-control-label d-flex align-items-center" for="plt_{{ $key }}">
                                                <span class="d-inline-flex align-items-center justify-content-center rounded-circle text-white ml-2"
                                                      style="background:{{ $def['color'] }}; width:30px; height:30px; min-width:30px">
                                                    <i class="{{ $def['icon'] }}" style="font-size:.8rem"></i>
                                                </span>
                                                <span>
                                                    <strong>{{ $def['name'] }}</strong>
                                                    @if($accs->isEmpty())
                                                        <br><small class="text-danger">غير مربوط</small>
                                                    @else
                                                        <br><small class="text-success"><i class="fas fa-check-circle"></i> متصل</small>
                                                    @endif
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                    @endforeach
                                    @if($errors->has('platforms'))
                                        <div class="text-danger tx-12">{{ $errors->first('platforms') }}</div>
                                    @endif
                                @endif
                            </div>
                        </div>

                        {{-- Scheduling --}}
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-calendar-alt ml-2"></i> وقت النشر</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label class="form-label">اختر إجراء</label>
                                    <div>
                                        <div class="custom-control custom-radio mb-2">
                                            <input type="radio" class="custom-control-input" name="action"
                                                   id="action_publish" value="publish" checked>
                                            <label class="custom-control-label" for="action_publish">
                                                <i class="fas fa-bolt text-warning ml-1"></i>
                                                نشر فوري الآن
                                            </label>
                                        </div>
                                        <div class="custom-control custom-radio mb-2">
                                            <input type="radio" class="custom-control-input" name="action"
                                                   id="action_schedule" value="schedule">
                                            <label class="custom-control-label" for="action_schedule">
                                                <i class="fas fa-calendar-check text-info ml-1"></i>
                                                جدولة في وقت محدد
                                            </label>
                                        </div>
                                        <div class="custom-control custom-radio mb-2">
                                            <input type="radio" class="custom-control-input" name="action"
                                                   id="action_draft" value="draft">
                                            <label class="custom-control-label" for="action_draft">
                                                <i class="fas fa-save text-secondary ml-1"></i>
                                                حفظ كمسودة
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                {{-- Schedule DateTime --}}
                                <div id="scheduleDateSection" class="d-none">
                                    <div class="form-group mb-0">
                                        <label class="form-label">تاريخ ووقت النشر</label>
                                        <input type="datetime-local" name="scheduled_at" class="form-control"
                                               id="scheduledAt" value="{{ old('scheduled_at') }}"
                                               min="{{ now()->addMinutes(5)->format('Y-m-d\TH:i') }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Submit Buttons --}}
                        <div class="card">
                            <div class="card-body">
                                <button type="submit" class="btn btn-primary btn-block btn-lg" id="submitBtn">
                                    <i class="fas fa-paper-plane ml-1"></i>
                                    <span id="submitLabel">نشر الآن</span>
                                </button>
                                <a href="{{ route('admin.social.index') }}" class="btn btn-light btn-block mt-2">
                                    <i class="fas fa-times ml-1"></i> إلغاء
                                </a>
                            </div>
                        </div>

                    </div>
                </div>
            </form>

        </div>
    </div>
</div>
@endsection

@section('js')
<script>
// Character counter
const content = document.getElementById('postContent');
const charCount = document.getElementById('charCount');
content.addEventListener('input', () => {
    charCount.textContent = content.value.length;
    charCount.style.color = content.value.length > 4500 ? '#dc3545' : '#6c757d';
});

// Media type toggle
document.querySelectorAll('.media-type-radio').forEach(radio => {
    radio.addEventListener('change', function () {
        document.querySelectorAll('.media-type-label').forEach(l => {
            l.style.background = '';
            l.style.color = '';
            l.style.borderColor = '';
        });
        const label = document.querySelector(`label[for="${this.id}"]`);
        label.style.background = '#e8f4fd';
        label.style.borderColor = '#0d6efd';

        const mediaSection = document.getElementById('mediaUploadSection');
        if (this.value === 'text') {
            mediaSection.classList.add('d-none');
        } else {
            mediaSection.classList.remove('d-none');
        }
    });
});

// Highlight currently selected media type
const activeRadio = document.querySelector('.media-type-radio:checked');
if (activeRadio) {
    const label = document.querySelector(`label[for="${activeRadio.id}"]`);
    if (label) {
        label.style.background = '#e8f4fd';
        label.style.borderColor = '#0d6efd';
    }
}

// Action radio toggle
document.querySelectorAll('[name="action"]').forEach(r => {
    r.addEventListener('change', function () {
        const scheduleSection = document.getElementById('scheduleDateSection');
        const submitLabel = document.getElementById('submitLabel');
        if (this.value === 'schedule') {
            scheduleSection.classList.remove('d-none');
            submitLabel.textContent = 'جدولة البوست';
        } else if (this.value === 'draft') {
            scheduleSection.classList.add('d-none');
            submitLabel.textContent = 'حفظ مسودة';
        } else {
            scheduleSection.classList.add('d-none');
            submitLabel.textContent = 'نشر الآن';
        }
    });
});

// File preview
document.getElementById('mediaFiles').addEventListener('change', function () {
    const preview = document.getElementById('mediaPreview');
    preview.innerHTML = '';
    Array.from(this.files).forEach(file => {
        const col = document.createElement('div');
        col.className = 'col-4 mb-2';
        const reader = new FileReader();
        reader.onload = e => {
            if (file.type.startsWith('image/')) {
                col.innerHTML = `<img src="${e.target.result}" class="img-thumbnail" style="height:100px; width:100%; object-fit:cover">
                                 <small class="d-block text-center text-truncate">${file.name}</small>`;
            } else {
                col.innerHTML = `<div class="bg-dark text-white rounded text-center py-3">
                                    <i class="fas fa-video fa-2x"></i><br>
                                    <small class="text-truncate d-block">${file.name}</small>
                                 </div>`;
            }
        };
        reader.readAsDataURL(file);
        preview.appendChild(col);
    });

    // Update label
    document.querySelector('.custom-file-label').textContent =
        this.files.length > 1 ? `${this.files.length} ملفات محددة` : this.files[0]?.name;
});

// Platform checkbox visual
document.querySelectorAll('.platform-check-item').forEach(item => {
    const checkbox = item.querySelector('input[type="checkbox"]');
    if (!checkbox || checkbox.disabled) return;
    item.addEventListener('click', function(e) {
        if (e.target !== checkbox) checkbox.checked = !checkbox.checked;
        item.style.background = checkbox.checked ? '#e8f4fd' : '';
        item.style.borderColor = checkbox.checked ? '#0d6efd' : '';
    });
    if (checkbox.checked) {
        item.style.background = '#e8f4fd';
        item.style.borderColor = '#0d6efd';
    }
});
</script>
@endsection
