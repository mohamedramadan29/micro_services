{{-- Downloadable request files. Expects $files, $serviceRequest and optional $fileRoute. --}}
<ul class="rf-files">
    @forelse ($files as $file)
        <li>
            <a href="{{ route($fileRoute ?? 'requests.file', [$serviceRequest, $file]) }}">
                <i class="bi bi-paperclip"></i> {{ $file->original_name }}
            </a>
            <span class="rf-muted">{{ $file->humanSize() }}</span>
        </li>
    @empty
        <li class="rf-muted">لا توجد ملفات</li>
    @endforelse
</ul>
