<?php

namespace Backstage\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Routing\Router;

class RouteServiceProvider extends ServiceProvider
{
    public function map(Router $router)
    {
        $router->group(['middleware' => 'web'], function ($router) {
            require __DIR__ . '/../../routes/web.php';

            $router->group(['namespace' => 'App\Http\Controllers'], function ($router) {
                if (file_exists(base_path('routes/web.php'))) {
                    require base_path('routes/web.php');
                }
            });
        });
    }
}
