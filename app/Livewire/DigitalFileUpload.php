<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Str;
use Livewire\WithFileUploads;
use App\Http\Utils\Imagemanager;

class DigitalFileUpload extends Component
{

    use WithFileUploads;

    public $digital_file;
    public $progress = 0;
    public $uploadedPath = '';
    public $isUploading = false;

    protected $listeners = ['startUpload'];


      protected Imagemanager $imagemanager;
    public function boot(Imagemanager $imagemanager)
    {
        $this->imagemanager = $imagemanager;
    }


    public function startUpload()
    {
        $this->validate([
            'digital_file' => 'required|file|max:2048000', // 2 جيجا (بالكيلوبايت)
        ]);

        $this->isUploading = true;
        $this->progress = 0;

        $digital_file = $this->digital_file;

        // $filename = Str::random(40) . '.' . $this->digital_file->getClientOriginalExtension();
        // $path = $this->digital_file->storeAs('digital_uploads', $filename, 'local');

        $path = $this->imagemanager->UploadSingleImage('/', $digital_file, 'digital_files');
        $this->uploadedPath = $path;
        $this->isUploading = false;
        $this->progress = 100;

        $this->dispatch('fileUploaded', $path);
    }

    public function render()
    {
        return view('livewire.digital-file-upload');
    }
}
