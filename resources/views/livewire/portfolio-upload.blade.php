<div>
    <!-- Hidden inputs لإرسال الأسماء مع الفورم الرئيسي -->
    @if ($uploadedMainImage)
    <input type="hidden" name="image" value="{{ $uploadedMainImage }}">
    @endif

    @foreach ($uploadedAdditionalImages as $img)
    <input type="hidden" name="files[]" value="{{ $img }}">
    @endforeach

    <!-- الصورة الرئيسية -->
    <div class="form-group mb-4">
        <label>الصورة الرئيسية <span class="text-danger">*</span></label>

        @if ($uploadedMainImage)
        <div class="mt-3 position-relative d-inline-block">
            <img src="{{ asset('assets/uploads/portfolios/' . $uploadedMainImage) }}" class="img-thumbnail"
                style="max-height: 200px;">
            <button type="button" wire:click="removeMainImage"
                class="btn btn-danger btn-sm position-absolute top-0 end-0">
                ×
            </button>
        </div>
        @else
        <input type="file" wire:model="mainImage" accept="image/*" class="form-control">
        @error('mainImage')
        <span class="text-danger">{{ $message }}</span>
        @enderror
        @endif

        <!-- Progress Bar -->
        <div x-data="{ progress: 0 }" x-on:livewire-upload-progress="progress = $event.detail.progress"
            x-on:livewire-upload-finish="$wire.reset('mainImage')">
            <div x-show="progress > 0 && progress < 100" class="progress mt-3" style="height: 28px;">
                <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                    :style="'width: ' + progress + '%'" x-text="progress + '%'">
                </div>
            </div>
        </div>

        <small class="text-muted d-block mt-2">الامتدادات: jpg, png, jpeg, webp | الحجم الأقصى: 4MB</small>
    </div>

    <!-- الصور الإضافية -->
    <div class="form-group mb-4">
        <label>صور إضافية للعمل (اختياري)</label>

        <input type="file" wire:model="additionalImages" multiple accept="image/*" class="form-control">

        <!-- Progress Bar للمتعدد -->
        <div x-data="{ progress: 0 }" x-on:livewire-upload-progress="progress = $event.detail.progress"
            x-on:livewire-upload-finish="$wire.reset('additionalImages')">
            <div x-show="progress > 0 && progress < 100" class="progress mt-3" style="height: 28px;">
                <div class="progress-bar progress-bar-striped progress-bar-animated bg-success"
                    :style="'width: ' + progress + '%'" x-text="progress + '%'">
                </div>
            </div>
        </div>

        <small class="text-muted d-block mt-2">الامتدادات: jpg, png, jpeg, webp | الحجم الأقصى لكل ملف: 4MB</small>

        @if (count($uploadedAdditionalImages) > 0)
        <div class="row mt-4">
            @foreach ($uploadedAdditionalImages as $index => $img)
            <div class="col-md-3 position-relative mb-3">
                <img src="{{ asset('assets/uploads/portfolios/' . $img) }}" class="img-thumbnail"
                    style="max-height: 150px;">
                <button type="button" wire:click="removeAdditionalImage({{ $index }})"
                    class="btn btn-danger btn-sm position-absolute top-0 end-0">×</button>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>
