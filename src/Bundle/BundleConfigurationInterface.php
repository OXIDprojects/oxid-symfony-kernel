<?php

namespace OxidCommunity\SymfonyKernel\Bundle;

use OxidCommunity\SymfonyKernel\DependencyInjection\ContainerBuilder;

interface BundleConfigurationInterface
{
    public function getBundleConfiguration($name, ContainerBuilder $containerBuilder) : array;
}