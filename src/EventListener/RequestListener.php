<?php

namespace Sioweb\Oxid\Kernel\EventListener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class RequestListener
{
    public function onKernelRequest(KernelEvent $event)
    {
        die('Hallo')
        $response = new Response();
        $response->setStatusCode(404);
        $event->setResponse($response);
    }
}