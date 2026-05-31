<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class GuestLayout extends Component
{
    public function __construct(
        public bool $wide = false,
        public bool $wideForm = false,
        public bool $shakeOnError = false,
        public ?string $pageTitle = null,
        public ?string $metaDescription = null,
    ) {}

    public function render(): View
    {
        return view('layouts.guest', [
            'wide' => $this->wide,
            'wideForm' => $this->wideForm,
            'shakeOnError' => $this->shakeOnError,
            'pageTitle' => $this->pageTitle,
            'metaDescription' => $this->metaDescription,
        ]);
    }
}
