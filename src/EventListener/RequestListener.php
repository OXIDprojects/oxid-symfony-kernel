<?php

namespace Sioweb\Oxid\Kernel\EventListener;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Sioweb\Oxid\Kernel\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;

class RequestListener
{

    /**
     * @var request_stack
     */
    private $routeLoader;
    private $service_container;

    public function __construct($service_container, $routeLoader)
    {
        $this->service_container = $service_container;
        $this->routeLoader = $routeLoader;
    }

    public function onKernelRequest(KernelEvent $event)
    {
        $response = new Response();
        $event->setResponse($response);

        $Routes = $this->routeLoader->getRoutes();

        $request = $event->getRequest();
        $request->attributes->add([
            '_controller' => '1',
        ]);
        
        $context = new RequestContext();
        $context->fromRequest(Request::createFromGlobals());
        $matcher = new UrlMatcher($Routes, $context);

        $Match = $matcher->checkRoute($request);

        if(!empty($Match)) {
            $request->attributes->add($Match);
            $response->setStatusCode(200);
            $event->getKernel()->handleOxid($request);
        } else {
            $response->setStatusCode(404);
        }

    }
}
