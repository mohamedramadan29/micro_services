<div>
    <div class="mb-3">
        <label class="form-label">الملف الرقمي (حتى 2 جيجا)</label>

        @if ($uploadedPath)
            <div class="alert alert-success">
                تم الرفع بنجاح: {{ basename($uploadedPath) }}
                <input type="hidden" name="digital_file_path" value="{{ $uploadedPath }}">
            </div>
        @else
            <input type="file" wire:model="digital_file" class="form-control" accept=".pdf,.zip,.rar,.exe,.mp3,.mp4">

            @if ($isUploading)
                <div class="mt-3">
                    <div class="progress" style="height: 40px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-success"
                            style="width: {{ $progress }}%">
                            {{ $progress }}%
                        </div>
                    </div>
                </div>
            @endif

            <button type="button" wire:click="startUpload" class="btn btn-primary mt-2" wire:loading.attr="disabled">
                <span wire:loading.remove>رفع الملف</span>
                <span wire:loading>جاري الرفع...</span>
            </button>
        @endif
    </div>

    <!-- تحديث التقدم تلقائيًا -->
    <script>
        document.addEventListener('livewire:load', () => {
            Livewire.on('upload:progress', (event) => {
                @this.set('progress', event.progress);
            });
        });
    </script>
</div>
