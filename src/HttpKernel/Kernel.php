<?php

namespace Sioweb\Oxid\Kernel\HttpKernel;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel AS HttpKernel;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class Kernel extends HttpKernel
{
    public function registerBundles()
    {
        $bundles = [
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \Sioweb\Oxid\Kernel\OxidKernelBundle(),
        ];

        $Config = [];

        $ContainerBuilder = new ContainerBuilder();
        $Extension = new \Sioweb\Oxid\Kernel\Extension\Extension();
        $Extension->getConfiguration($Config, $ContainerBuilder);
        $ContainerBuilder->registerExtension($Extension);

        $loader = new YamlFileLoader(
            $ContainerBuilder,
            new FileLocator(__DIR__.'/../Resources/config')
        );

        $loader->load('bundles.yml');

        foreach($ContainerBuilder->getExtensionConfig('oxid-kernel')[0]['bundles'] as $bundle) {
            array_unshift($bundles, new $bundle());
        }

        return $bundles;
    }

    public function getRootDir()
    {
        return __DIR__;
    }

    public function getCacheDir()
    {
        return dirname(__DIR__).'/../../../../source/tmp/cache/'.$this->getEnvironment();
    }

    public function getLogDir()
    {
        return dirname(__DIR__).'/../../../../source/log/kernel';
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        // die('<pre>' . print_r($this->getRootDir().'/../Resources/config/config_'.$this->getEnvironment().'.yml', true));
        $loader->load($this->getRootDir().'/../Resources/config/config_'.$this->getEnvironment().'.yml');
    }

    /**
     * {@inheritdoc}
     */
    protected function initializeContainer()
    {
        parent::initializeContainer();
        if (null === ($container = $this->getContainer())) {
            return;
        }
        
        // Set the plugin loader again so it is available at runtime (synthetic service)
        $container->set('sioweb.oxid.kernel.bundles', new Class(
            $this->getBundles(), $container->get('routing.loader'), $container->get('kernel')
        ) {
            private $loader;

            private $kernel;

            private $bundles;

            public function __construct($bundles, $loader, $kernel)
            {
                $this->bundles = $bundles;
                $this->loader = $loader;
                $this->kernel = $kernel;
            }
            public function getRoutes()
            {
                $Collection = new RouteCollection();
                foreach($this->bundles as $bundle) {
                    if($bundle instanceof BundleRoutesInterface) {
                        $Collection->addCollection($bundle->getRouteCollection(
                            $this->loader->getResolver(), $this->kernel
                        ));
                    }
                }
                return $Collection;
            }
        });
    }
}
