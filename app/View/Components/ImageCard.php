<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ImageCard extends Component
{
    public string $imageUrl;
    public ?string $thumbnailUrl;
    public string $altText;
    public string $filename;
    public string $dimensions;
    public string $deleteUrl;

    /**
     * Create a new component instance.
     */
    public function __construct(
        string $imageUrl,
        ?string $thumbnailUrl,
        string $altText,
        string $filename,
        string $dimensions,
        string $deleteUrl
    ) {
        $this->imageUrl = $imageUrl;
        $this->thumbnailUrl = $thumbnailUrl;
        $this->altText = $altText;
        $this->filename = $filename;
        $this->dimensions = $dimensions;
        $this->deleteUrl = $deleteUrl;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.image-card');
    }
}
