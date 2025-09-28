<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class UploadForm extends Component
{
    /**
     * Create a new component instance.
     */
    public string $action;
    public string $title;
    public string $inputName;
    public string $acceptedFileTypes;
    public int $maxFileSizeMb;
    public string $fileTypesDescription;

    public function __construct(
        string $action,
        string $title = 'Upload Memorial Photo',
        string $inputName = 'file',
        string $acceptedFileTypes = 'image/*',
        int $maxFileSizeMb = 10,
        string $fileTypesDescription = 'PNG, JPG, GIF up to 10MB'
    ) {
        $this->action = $action;
        $this->title = $title;
        $this->inputName = $inputName;
        $this->acceptedFileTypes = $acceptedFileTypes;
        $this->maxFileSizeMb = $maxFileSizeMb;
        $this->fileTypesDescription = $fileTypesDescription;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.upload-form');
    }
}
