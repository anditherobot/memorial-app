<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class AdminPageHeader extends Component
{
    public string $title;
    public array $breadcrumbs;

    /**
     * Create a new component instance.
     */
    public function __construct(string $title, array $breadcrumbs = [])
    {
        $this->title = $title;
        $this->breadcrumbs = $breadcrumbs;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.admin-page-header');
    }
}
