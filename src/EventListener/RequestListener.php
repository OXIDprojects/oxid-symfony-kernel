<?php

namespace Sioweb\Oxid\Kernel\EventListener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpFoundation\RequestMatcher;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Contao\ManagerPlugin\PluginLoader;
use Contao\ManagerPlugin\Routing\RoutingPluginInterface;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface; 

class RequestListener
{

    /**
     * @var request_stack
     */
    private $request_stack;
    private $matcher;
    private $service_container;


    public function __construct($service_container)
    {
        $this->service_container = $service_container;
    }

    public function onKernelRequest(KernelEvent $event)
    {
        die('3');
        $response = new Response();
        $response->setStatusCode(404);
        $event->setResponse($response);
        $request = $event->getRequest();
    }
}
