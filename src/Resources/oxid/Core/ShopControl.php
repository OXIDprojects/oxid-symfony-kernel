<?php

namespace Sioweb\Oxid\Kernel\Legacy\Core;

use Sioweb\Oxid\Kernel\HttpKernel\Kernel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Debug\Debug;
use OxidEsales\Eshop\Core\Registry;


class ShopControl extends ShopControl_parent
{
    public function start($controllerKey = null, $function = null, $parameters = null, $viewsChain = null)
    {
        if(!empty($_GET['cl'])) {
            return parent::start($controllerKey, $function, $parameters, $viewsChain);
        }
        
        $_GET['_controller'] = 1;

        // Debug::enable(E_ERROR);
        Request::enableHttpMethodParameterOverride();
        $kernel = new Kernel('prod', false);


    
        $kernel->setProjectRoot('/' . trim(__DIR__, 'source/modules/sioweb/Kernel/Core'));
        $request = Request::createFromGlobals();
        $response = $kernel->handle($request);
        $responseStatusCode = $response->getStatusCode();
        
        if($responseStatusCode !== 404) {
            $response->send();
            $kernel->terminate($request, $response);
        } else {
            $response->setCache(array('max_age' => 0));
            parent::start($controllerKey, $function, $parameters, $viewsChain);
        }
    }
}
