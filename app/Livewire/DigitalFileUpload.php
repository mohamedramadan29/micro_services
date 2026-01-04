<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Http\Utils\Imagemanager;

class DigitalFileUpload extends Component
{
    use WithFileUploads;

    public $digital_file;
    public $uploadedPath = '';
    public $currentFile = ''; // الملف الحالي من الداتابيز

    protected Imagemanager $imagemanager;

    public function mount($currentFile = null)
    {
        $this->currentFile = $currentFile;
        // لو في ملف حالي → نعرضه كـ uploaded من الأول
        if ($currentFile) {
            $this->uploadedPath = $currentFile;
        }
    }

    public function boot(Imagemanager $imagemanager)
    {
        $this->imagemanager = $imagemanager;
    }

    public function updatedDigitalFile()
    {
        $this->validate([
            'digital_file' => 'required|file|max:2048000|mimes:pdf,zip,rar,exe,mp3,mp4,7z',
        ]);

        // رفع الملف الجديد
        $filename = $this->imagemanager->UploadSingleImage('/', $this->digital_file, 'digital_files');

        // حذف الملف القديم لو موجود (اختياري - لو عايز تحذفه من السيرفر)
        if ($this->currentFile) {
            $oldPath = public_path('assets/uploads/digital_products/' . $this->currentFile);
            if (file_exists($oldPath)) {
                unlink($oldPath);
            }
        }

        $this->uploadedPath = $filename;
        $this->currentFile = $filename; // تحديث الحالي
        $this->digital_file = null; // رست الـ input
    }

    public function removeFile()
    {
        // حذف الملف من السيرفر لو عايز
        if ($this->uploadedPath) {
            $path = public_path('assets/uploads/digital_products/' . $this->uploadedPath);
            if (file_exists($path)) {
                unlink($path);
            }
        }

        $this->uploadedPath = '';
        $this->currentFile = '';
    }

    public function render()
    {
        return view('livewire.digital-file-upload');
    }
}
