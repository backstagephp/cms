<?php

namespace Backstage\View\Components;

use Illuminate\Support\Facades\View;
use Illuminate\View\Component;

class DefaultBlock extends Component
{
    public function __construct(public string $_type) {}

    public function render()
    {
        return View::first([
            'components.blocks.' . $this->_type,
            'components.blocks.default',
            'backstage::components.default',
        ]);
    }
}
