{{-- Fields every request has, whatever the service. Optional $client prefills contact details. --}}
<fieldset class="rf-section rf-section-shared">
    <legend class="rf-section-title">اشرح المشكلة التي تواجهها أو ما ترغب بتنفيذه</legend>
    <div class="rf-field" data-required="1">
        <label class="rf-field-label" for="rf-problem">اكتب التفاصيل، هدفك، وما تحتاج مساعدة فيه <span class="text-danger">*</span></label>
        <textarea class="form-control" id="rf-problem" name="problem" rows="5" maxlength="5000">{{ old('problem') }}</textarea>
        @error('problem')
            <div class="rf-field-error">{{ $message }}</div>
        @enderror
    </div>
    <div class="rf-field">
        <label class="rf-field-label" for="rf-attachments">مرفقات المشروع (اختياري)</label>
        @if (isset($existingFiles))
            @include('website.service-requests.partials.existing-files', ['files' => $existingFiles->get('attachments')])
        @endif
        <input type="file" class="form-control" id="rf-attachments" name="attachments[]" multiple
            accept="{{ collect(\App\Services\ServiceRequests\RequestSubmission::ATTACHMENT_EXTENSIONS)->map(fn ($ext) => '.' . $ext)->implode(',') }}"
            data-max-files="{{ \App\Services\ServiceRequests\RequestSubmission::ATTACHMENTS_MAX }}"
            data-max-size-mb="{{ \App\Services\ServiceRequests\RequestSubmission::ATTACHMENT_MAX_MB }}">
        <small class="rf-field-help">ملف أو صورة توضح طلبك · حتى {{ \App\Services\ServiceRequests\RequestSubmission::ATTACHMENTS_MAX }} ملفات، {{ \App\Services\ServiceRequests\RequestSubmission::ATTACHMENT_MAX_MB }} ميجابايت لكل ملف</small>
        @error('attachments')
            <div class="rf-field-error">{{ $message }}</div>
        @enderror
        @error('attachments.*')
            <div class="rf-field-error">{{ $message }}</div>
        @enderror
    </div>
</fieldset>

<fieldset class="rf-section rf-section-shared">
    <legend class="rf-section-title">بيانات التواصل</legend>
    <div class="row">
        <div class="col-md-4 rf-field" data-required="1">
            <label class="rf-field-label" for="rf-name">الاسم الكامل <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="rf-name" name="contact_name" maxlength="255" autocomplete="name"
                value="{{ old('contact_name', $client->name ?? '') }}">
            @error('contact_name')
                <div class="rf-field-error">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-4 rf-field" data-required="1">
            <label class="rf-field-label" for="rf-phone">رقم التواصل <span class="text-danger">*</span></label>
            <input type="tel" class="form-control" id="rf-phone" name="contact_phone" maxlength="30" dir="ltr" autocomplete="tel"
                value="{{ old('contact_phone', $client->phone ?? '') }}">
            @error('contact_phone')
                <div class="rf-field-error">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-4 rf-field" data-required="1">
            <label class="rf-field-label" for="rf-email">البريد الإلكتروني <span class="text-danger">*</span></label>
            <input type="email" class="form-control" id="rf-email" name="contact_email" maxlength="255" dir="ltr" autocomplete="email"
                value="{{ old('contact_email', $client->email ?? '') }}">
            @error('contact_email')
                <div class="rf-field-error">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="row">
        <div class="col-md-4 rf-field">
            <label class="rf-field-label" for="rf-contact-method">طريقة التواصل المفضلة (اختياري)</label>
            <select class="form-control" id="rf-contact-method" name="contact_method">
                <option value="">بدون تفضيل</option>
                @foreach (\App\Models\ServiceRequest::CONTACT_METHODS as $key => $label)
                    <option value="{{ $key }}" @selected(old('contact_method') === $key)>{{ $label }}</option>
                @endforeach
            </select>
            @error('contact_method')
                <div class="rf-field-error">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <small class="rf-field-help">بيانات التواصل لا تظهر للمستقلين.</small>
</fieldset>
