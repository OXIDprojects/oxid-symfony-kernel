<?php

namespace Sioweb\Oxid\Kernel\Legacy\Core;

use Sioweb\Oxid\Kernel\HttpKernel\Kernel;
use Symfony\Component\HttpFoundation\Request;

class ShopControl extends ShopControl_parent
{
    public function start($controllerKey = null, $function = null, $parameters = null, $viewsChain = null)
    {

        $loader = require __DIR__.'/../vendor/autoload.php';
        
        $kernel = new Kernel('prod', false);
        $kernel->loadClassCache();
        //$kernel = new AppCache($kernel);
        $request = Request::createFromGlobals();
        $response = $kernel->handle($request);
        $response->send();
        $kernel->terminate($request, $response);
        die();
        return parent::start($controllerKey, $function, $parameters, $viewsChain);
    }
}