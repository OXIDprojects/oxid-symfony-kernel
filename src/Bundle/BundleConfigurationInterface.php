<?php

namespace Sioweb\Oxid\Kernel\Bundle;

use Sioweb\Oxid\Kernel\DependencyInjection\ContainerBuilder;

interface BundleConfigurationInterface
{
    public function getBundleConfiguration($name, ContainerBuilder $containerBuilder) : array;
}