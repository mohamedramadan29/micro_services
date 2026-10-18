{{-- Service 3 «مشروع بمراحل»: title and the «stopped project from outside» part. Optional $page (content area staged.page). --}}
@php $page = $page ?? \App\Support\SiteContent::first('staged.page'); @endphp
<input type="hidden" name="kind" value="{{ \App\Models\ServiceRequest::KIND_STAGED }}">
<fieldset class="rf-section rf-section-shared rf-staged-fields">
    <legend class="rf-section-title">المشروع</legend>
    <div class="rf-field" data-required="1">
        <label class="rf-field-label" for="rf-project-title">عنوان المشروع <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="rf-project-title" name="project_title" maxlength="150" required value="{{ old('project_title') }}" placeholder="مثال: تطوير متجر إلكتروني">
        @error('project_title')<div class="rf-field-error">{{ $message }}</div>@enderror
    </div>
    <div class="rf-field rf-stopped">
        <label class="rf-stopped-toggle">
            <input type="checkbox" name="stopped_project" value="1" id="rf-stopped" @checked(old('stopped_project'))>
            <span>{{ $page['stopped_label'] }}</span>
        </label>
        <div id="rf-stopped-box" @unless (old('stopped_project')) hidden @endunless>
            @if ($page['stopped_help'])<small class="rf-field-help d-block mb-1">{{ $page['stopped_help'] }}</small>@endif
            <textarea class="form-control" name="done_before" rows="4" maxlength="5000" placeholder="ما الذي أُنجز؟ ما الذي تبقّى؟ ما الملفات المتوفرة لديك؟">{{ old('done_before') }}</textarea>
            @error('done_before')<div class="rf-field-error">{{ $message }}</div>@enderror
        </div>
    </div>
</fieldset>
<script>
    document.getElementById('rf-stopped')?.addEventListener('change', function () {
        document.getElementById('rf-stopped-box').hidden = !this.checked;
    });
</script>
