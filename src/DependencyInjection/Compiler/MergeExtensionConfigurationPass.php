<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sioweb\Oxid\Kernel\DependencyInjection\Compiler;

use Sioweb\Oxid\Kernel\Bundle\Extension\OxidKernelExtensionInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder AS BaseContainerBuilder;
use Sioweb\Oxid\Kernel\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Merges extension configs into the container builder.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class MergeExtensionConfigurationPass implements CompilerPassInterface
{
    /**
     * @var ContainerBuilder
     */
    private $container = null;

    public function setGlobalContainer($container)
    {
        $this->container = $container;
    }
    /**
     * {@inheritdoc}
     */
    public function process(BaseContainerBuilder $container)
    {
        $parameters = $container->getParameterBag()->all();
        $definitions = $container->getDefinitions();
        $aliases = $container->getAliases();
        $exprLangProviders = $container->getExpressionLanguageProviders();

        foreach ($container->getExtensions() as $extension) {
            if ($extension instanceof PrependExtensionInterface) {
                $extension->prepend($container);
            }
        }

        foreach ($container->getExtensions() as $name => $extension) {
            if (!$config = $container->getExtensionConfig($name)) {
                // this extension was not called
                continue;
            }
            $config = $container->getParameterBag()->resolveValue($config);

            $tmpContainer = new ContainerBuilder($container->getParameterBag());
            $tmpContainer->setResourceTracking($container->isTrackingResources());
            $tmpContainer->addObjectResource($extension);

            foreach ($exprLangProviders as $provider) {
                $tmpContainer->addExpressionLanguageProvider($provider);
            }
            if($extension instanceof OxidKernelExtensionInterface) {
                $extension->getExtenisonConfig((array)$config, $tmpContainer, $this->container);
                $container->merge($this->container);
            } else {
                $extension->load((array)$config, $tmpContainer);
                $container->merge($tmpContainer);
            }

            $container->getParameterBag()->add($parameters);
        }

        $container->addDefinitions($definitions);
        $container->addAliases($aliases);
    }
}
