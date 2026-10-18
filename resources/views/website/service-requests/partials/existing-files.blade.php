{{-- Edit page: files already on the request, each can be removed. Expects $files and $serviceRequest. --}}
@if ($files && $files->isNotEmpty())
    <ul class="rf-existing-files">
        @foreach ($files as $file)
            <li>
                <a href="{{ route('requests.file', [$serviceRequest, $file]) }}"><i class="bi bi-paperclip"></i> {{ $file->original_name }}</a>
                <label><input type="checkbox" name="remove_files[]" value="{{ $file->id }}" @checked(in_array($file->id, array_map('intval', (array) old('remove_files', [])), true))> حذف</label>
            </li>
        @endforeach
    </ul>
@endif
