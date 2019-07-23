<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace OxidCommunity\SymfonyKernel\DependencyInjection;

use OxidCommunity\SymfonyKernel\Bundle\BundleConfigurationInterface;
// use OxidCommunity\SymfonyKernel\DependencyInjection\Compiler\Compiler;
use Symfony\Component\DependencyInjection\ContainerBuilder as BaseContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\BadMethodCallException;

class ContainerBuilder extends BaseContainerBuilder
{
    private $autoloadedBundles = [];

    public function getBundle($bundle) {
        return $this->autoloadedBundles[$bundle];
    }

    public function setBundles($bundles)
    {
        $this->autoloadedBundles = $bundles;
    }

    public function getBundles()
    {
        return $this->autoloadedBundles;
    }

    public function getExtensionConfigs()
    {
        return $this->extensionConfigs;
    }


    /**
     * {@inheritdoc}
     */
    public function getExtensionConfig($name): array
    {
        if(!empty($this->autoloadedBundles)) {
            foreach($this->autoloadedBundles as $bundle) {
                $ExtensionName = $bundle->getContainerExtension()->getAlias();
                if(empty($this->extensionConfigs[$ExtensionName])) {
                    $this->extensionConfigs[$ExtensionName] = parent::getExtensionConfig($ExtensionName)[0];
                    if($bundle instanceof BundleConfigurationInterface) {
                        $this->extensionConfigs = $bundle->getBundleConfiguration($name, $this);
                    }
                }
            }

            // if($name === 'security') {
            //     die('<pre>1: ' . print_r($this->extensionConfigs, true));
            // }

            if($this->autoloadedBundles[$name] instanceof BundleConfigurationInterface) {
                return $this->extensionConfigs;
            }

            return [$this->extensionConfigs[$name]];
        }
        return parent::getExtensionConfig($name);
    }

    /**
     * Compiles the container.
     *
     * This method passes the container to compiler
     * passes whose job is to manipulate and optimize
     * the container.
     *
     * The main compiler passes roughly do four things:
     *
     *  * The extension configurations are merged;
     *  * Parameter values are resolved;
     *  * The parameter bag is frozen;
     *  * Extension loading is disabled.
     */
    public function compile()
    {
        $compiler = $this->getCompiler();

        if ($this->trackResources) {
            foreach ($compiler->getPassConfig()->getPasses() as $pass) {
                $this->addObjectResource($pass);
            }
        }

        $compiler->compile($this);

        if ($this->trackResources) {
            foreach ($this->definitions as $definition) {
                if ($definition->isLazy() && ($class = $definition->getClass()) && class_exists($class)) {
                    $this->addClassResource(new \ReflectionClass($class));
                }
            }
        }

        $this->extensionConfigs = array();

        parent::compile();
    }
}
