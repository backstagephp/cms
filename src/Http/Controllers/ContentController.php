<?php

namespace Backstage\Http\Controllers;

use Illuminate\Http\Request;

class ContentController
{
    public function __invoke(Request $request)
    {
        return $request->content->response();
    }
}
