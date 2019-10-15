<?php

namespace OxidCommunity\SymfonyKernel\Legacy\Core;

use OxidCommunity\SymfonyKernel\HttpKernel\Kernel;
use Symfony\Component\HttpFoundation\Request;

class ShopControl extends ShopControl_parent
{
    protected function kernel($state, ...$params) {}

    public function start($controllerKey = null, $function = null, $parameters = null, $viewsChain = null)
    {
        if(!empty($_GET['cl'])) {
            return parent::start($controllerKey, $function, $parameters, $viewsChain);
        }

        $this->kernel('init');
        
        $_GET['_controller'] = 1;

        // Debug::enable(E_ERROR);
        Request::enableHttpMethodParameterOverride();
        $kernel = new Kernel('prod', false);

        $kernel->setProjectRoot(trim(preg_replace('|\\\|is', '/', __DIR__), 'source/modules/oxid-community/symfony-kernel/Core'));
        $request = Request::createFromGlobals();
        $response = $kernel->handle($request);
        $responseStatusCode = $response->getStatusCode();
        
        if($responseStatusCode !== 404) {
            $this->kernel('before', $kernel, $response);
            $response->send();
            $kernel->terminate($request, $response);
            $this->kernel('after', $kernel, $response);
        } else {
            $this->kernel('oxid', $kernel, $response, $controllerKey, $function, $parameters, $viewsChain);
            $response->setCache(array('max_age' => 0));
            parent::start($controllerKey, $function, $parameters, $viewsChain);
        }
    }
}
