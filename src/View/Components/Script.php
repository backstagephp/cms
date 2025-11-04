<?php

namespace Backstage\View\Components;

use Illuminate\View\Component;

class Script extends Component
{
    public function __construct(
        public string $src,
        public bool $defer = true
    ) {}

    public function render()
    {
        return view('backstage::components.page');
    }
}
