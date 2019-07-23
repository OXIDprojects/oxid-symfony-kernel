<?php

namespace OxidCommunity\SymfonyKernel\Bundle\Extension;

use OxidCommunity\SymfonyKernel\DependencyInjection\ContainerBuilder;
// use Symfony\Component\DependencyInjection\ContainerBuilder;

interface OxidKernelExtensionInterface
{
    public function getExtenisonConfig(array $configs, ContainerBuilder $extensionContainer, ContainerBuilder $container);
}