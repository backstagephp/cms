<?php

namespace Backstage\View\Components\Blocks;

use Illuminate\View\Component;

class Heading extends Component
{
    public function __construct(public string $heading, public int $level) {}

    public function render()
    {
        return view('backstage::components.blocks.heading');
    }
}
