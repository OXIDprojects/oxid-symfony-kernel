<?php

namespace OxidCommunity\SymfonyKernel\Bundle;

use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\HttpKernel\KernelInterface;

interface BundleRoutesInterface
{
    public function getRouteCollection(LoaderResolverInterface $resolver, KernelInterface $kernel);
}
