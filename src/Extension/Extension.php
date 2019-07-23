<?php

/**
 * Contao Open Source CMS
 */

declare (strict_types = 1);

namespace OxidCommunity\SymfonyKernel\Extension;

use OxidCommunity\SymfonyKernel\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerBuilder AS BaseContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension AS BaseExtension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class Extension extends BaseExtension 
{
	/**
	 * {@inheritdoc}
	 */
	public function getAlias()
	{
		return 'oxid-kernel';
    }
    
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, BaseContainerBuilder $container)
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );
        $loader->load('listener.yml');
        $loader->load('services.yml');
    }
}
