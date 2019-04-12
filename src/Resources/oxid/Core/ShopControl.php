<?php

namespace Sioweb\Oxid\Kernel\Legacy\Core;

use Sioweb\Oxid\Kernel\HttpKernel\Kernel;
use Symfony\Component\HttpFoundation\Request;

class ShopControl extends ShopControl_parent
{
    public function start($controllerKey = null, $function = null, $parameters = null, $viewsChain = null)
    {
        $kernel = new Kernel('prod', false);
        $kernel->loadClassCache();
        //$kernel = new AppCache($kernel);
        $request = Request::createFromGlobals();
        $response = $kernel->handle($request);
        $responseStatusCode = $response->getStatusCode();
        
        if($responseStatusCode !== 404) {
            $response->send();
            $kernel->terminate($request, $response);
        } else {
            die('Wrong');
            parent::start($controllerKey, $function, $parameters, $viewsChain);
        }
    }
}