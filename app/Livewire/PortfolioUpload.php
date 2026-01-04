<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Http\Utils\Imagemanager;

class PortfolioUpload extends Component
{

    use WithFileUploads;

    public $portfolio = null; // هيتبعت من الـ view

    public $mainImage;
    public $additionalImages = [];

    public $uploadedMainImage = null;
    public $uploadedAdditionalImages = [];

    protected $imagemanager;

    public function mount($portfolio = null)
    {
        $this->portfolio = $portfolio;

        if ($portfolio) {
            $this->uploadedMainImage = $portfolio->image;

            // more_images محفوظ كـ JSON string → نحوله لـ array
            $more = $portfolio->more_images ? $portfolio->more_images : [];
            $this->uploadedAdditionalImages = is_array($more) ? $more : [];
        }
    }

    public function boot()
    {
        $this->imagemanager = new Imagemanager();
    }

    public function updatedMainImage()
    {
        $this->validate([
            'mainImage' => 'image|mimes:jpeg,png,jpg,webp|max:4096',
        ]);

        $fileName = $this->imagemanager->UploadSingleImage('/', $this->mainImage, 'portfolio_files');
        $this->uploadedMainImage = $fileName;
        $this->reset('mainImage');
    }

    public function updatedAdditionalImages()
    {
        $this->validate([
            'additionalImages.*' => 'image|mimes:jpeg,png,jpg,webp|max:4096',
        ]);

        $paths = [];
        foreach ($this->additionalImages as $image) {
            $fileName = $this->imagemanager->UploadSingleImage('/', $image, 'portfolio_files');
            $paths[] = $fileName;
        }

        $this->uploadedAdditionalImages = array_merge($this->uploadedAdditionalImages, $paths);
        $this->reset('additionalImages');
    }

    public function removeMainImage()
    {
        // اختياري: حذف الملف من السيرفر
        // if ($this->uploadedMainImage && file_exists(public_path('portfolio_files/' . $this->uploadedMainImage))) {
        //     unlink(public_path('portfolio_files/' . $this->uploadedMainImage));
        // }

        $this->uploadedMainImage = null;
    }

    public function removeAdditionalImage($index)
    {
        if (isset($this->uploadedAdditionalImages[$index])) {
            // اختياري: حذف الملف
            // $path = public_path('portfolio_files/' . $this->uploadedAdditionalImages[$index]);
            // if (file_exists($path)) unlink($path);

            unset($this->uploadedAdditionalImages[$index]);
            $this->uploadedAdditionalImages = array_values($this->uploadedAdditionalImages);
        }
    }
    public function render()
    {
        return view('livewire.portfolio-upload');
    }
}
