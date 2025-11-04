<?php

namespace Backstage\View\Components;

use Illuminate\Support\Facades\View;
use Illuminate\View\Component;

class Page extends Component
{
    public function __construct() {}

    public function render()
    {
        return View::first(['components.page', 'backstage::components.page']);
    }
}
