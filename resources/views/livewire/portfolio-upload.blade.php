<div class="rf-pf-upload">
    {{-- File names travel with the main form. --}}
    @if ($uploadedMainImage)
        <input type="hidden" name="image" value="{{ $uploadedMainImage }}">
    @endif
    @foreach ($uploadedAdditionalImages as $img)
        <input type="hidden" name="files[]" value="{{ $img }}">
    @endforeach

    {{-- Main image --}}
    <div class="form-group">
        <label>الصورة الرئيسية <span class="text-danger">*</span></label>
        @if ($uploadedMainImage)
            <div class="rf-pf-cover">
                <img src="{{ asset('assets/uploads/portfolios/' . $uploadedMainImage) }}" alt="الصورة الرئيسية">
                <button type="button" wire:click="removeMainImage" class="rf-pf-remove" aria-label="حذف الصورة الرئيسية"><i class="bi bi-x-lg"></i></button>
            </div>
        @else
            <label class="rf-pf-drop" x-data="{ progress: 0 }" x-on:livewire-upload-progress="progress = $event.detail.progress" x-on:livewire-upload-finish="progress = 0">
                <input type="file" wire:model="mainImage" accept="image/jpeg,image/png,image/webp">
                <i class="bi bi-cloud-arrow-up"></i>
                <strong>اختر صورة الغلاف</strong>
                <small>jpg أو png أو webp، حتى 4 ميجابايت</small>
                <span class="rf-pf-progress" x-show="progress > 0 && progress < 100"><span :style="'width:' + progress + '%'"></span></span>
            </label>
        @endif
        @error('mainImage')<div class="rf-field-error">{{ $message }}</div>@enderror
    </div>

    {{-- Additional images --}}
    <div class="form-group mb-0">
        <label>صور إضافية <small class="text-muted">(اختياري)</small></label>
        @if (count($uploadedAdditionalImages) > 0)
            <div class="rf-pf-thumbs">
                @foreach ($uploadedAdditionalImages as $index => $img)
                    <div class="rf-pf-thumb">
                        <img src="{{ asset('assets/uploads/portfolios/' . $img) }}" alt="">
                        <button type="button" wire:click="removeAdditionalImage({{ $index }})" class="rf-pf-remove" aria-label="حذف الصورة"><i class="bi bi-x-lg"></i></button>
                    </div>
                @endforeach
            </div>
        @endif
        <label class="rf-pf-drop rf-pf-drop-sm" x-data="{ progress: 0 }" x-on:livewire-upload-progress="progress = $event.detail.progress" x-on:livewire-upload-finish="progress = 0">
            <input type="file" wire:model="additionalImages" multiple accept="image/jpeg,image/png,image/webp">
            <i class="bi bi-images"></i>
            <strong>أضف صورًا أخرى من العمل</strong>
            <small>يمكنك اختيار أكثر من صورة</small>
            <span class="rf-pf-progress" x-show="progress > 0 && progress < 100"><span :style="'width:' + progress + '%'"></span></span>
        </label>
        @error('additionalImages.*')<div class="rf-field-error">{{ $message }}</div>@enderror
    </div>
</div>
