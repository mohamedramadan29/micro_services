<div>
    <div class="mb-3">
        <label class="form-label fw-bold">رفع ملف رقمي جديد (حتى 2 جيجا)</label>

        @if ($uploadedPath)
        <div class="alert alert-success p-4 shadow-sm rounded">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <i class="bx bx-check-circle fs-3 text-success"></i>
                    <strong class="ms-2">
                        @if ($uploadedPath !== $currentFile)
                        تم رفع ملف جديد بنجاح!
                        @else
                        الملف الحالي جاهز
                        @endif
                    </strong><br>
                    <small class="text-muted">{{ basename($uploadedPath) }}</small>
                </div>
                <button type="button" wire:click="removeFile" class="btn btn-outline-danger btn-sm">
                    إزالة الملف
                </button>
            </div>
        </div>
        <input type="hidden" name="digital_file_path" value="{{ $uploadedPath }}">
        @else
        <div x-data="{ uploading: false, progress: 0 }" x-on:livewire-upload-start="uploading = true"
            x-on:livewire-upload-finish="uploading = false; progress = 0"
            x-on:livewire-upload-error="uploading = false; progress = 0"
            x-on:livewire-upload-progress="progress = $event.detail.progress">

            <input type="file" wire:model.live="digital_file" class="form-control form-control-lg"
                accept=".pdf,.zip,.rar,.exe,.mp3,.mp4,.7z">

            @error('digital_file')
            <div class="text-danger mt-2 fw-bold">{{ $message }}</div>
            @enderror

            <div x-show="uploading" class="mt-4">
                <div class="progress" style="height: 50px; border-radius: 12px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-success fs-4"
                        :style="'width: ' + progress + '%'" role="progressbar">
                        <strong x-text="progress + '%'"></strong>
                    </div>
                </div>
                <p class="text-center mt-3 text-success fw-bold fs-5">
                    جاري الرفع... <span x-text="progress"></span>% مكتمل
                </p>
            </div>
        </div>
        @endif
    </div>
</div>
