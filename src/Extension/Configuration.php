<?php

/**
 * Contao Open Source CMS
 */

declare (strict_types = 1);

namespace OxidCommunity\SymfonyKernel\Extension;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function getConfigTreeBuilder()
	{
		$treeBuilder = new TreeBuilder();
		$rootNode = $treeBuilder->root('oxid-symfony-kernel');

		$rootNode
			->children()
				->arrayNode('bundles')
				->end()
		->end();

    	return $treeBuilder;

	}
}
