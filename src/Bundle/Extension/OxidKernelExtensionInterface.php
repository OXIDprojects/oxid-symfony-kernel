<?php

namespace Sioweb\Oxid\Kernel\Bundle\Extension;

use Sioweb\Oxid\Kernel\DependencyInjection\ContainerBuilder;
// use Symfony\Component\DependencyInjection\ContainerBuilder;

interface OxidKernelExtensionInterface
{
    public function getExtenisonConfig(array $configs, ContainerBuilder $extensionContainer, ContainerBuilder $container);
}