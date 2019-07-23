<?php

/**
 * Contao Open Source CMS
 */

declare (strict_types = 1);

namespace Sioweb\Oxid\Kernel\Extension;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @file Configuration.php
 * @class Configuration
 * @author Sascha Weidner
 * @package sioweb.contao.extensions.glossar
 * @copyright Sascha Weidner, Sioweb
 */

class Configuration implements ConfigurationInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function getConfigTreeBuilder()
	{
		$treeBuilder = new TreeBuilder();
		$rootNode = $treeBuilder->root('oxid-kernel');

		$rootNode
			->children()
				->arrayNode('bundles')
				->end()
		->end();

    	return $treeBuilder;

	}
}
