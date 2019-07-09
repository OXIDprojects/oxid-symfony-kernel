<?php

namespace Sioweb\Oxid\Kernel\HttpKernel;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Filesystem\Filesystem;
use Sioweb\Oxid\Kernel\DependencyInjection\ContainerBuilder;
// use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel AS BaseKernel;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Sioweb\Oxid\Kernel\Bundle\BundleRoutesInterface;
use Sioweb\Oxid\Kernel\Bundle\BundleConfigurationInterface;
use Symfony\Bridge\ProxyManager\LazyProxy\Instantiator\RuntimeInstantiator;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Sioweb\Oxid\Kernel\DependencyInjection\Compiler\MergeExtensionConfigurationPass;


class Kernel extends BaseKernel
{
    private $autoloadetBundles = [];

    public function registerBundles()
    {
        $autoloadetBundles = [
            new \Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new \Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new \Symfony\Bundle\MonologBundle\MonologBundle(),
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \Sioweb\Oxid\Kernel\OxidKernelBundle(),
        ];

        foreach($autoloadetBundles as $bundle) {
            $this->autoloadetBundles[$bundle->getContainerExtension()->getAlias()] = $bundle;
        }

        $Config = [];

        $ContainerBuilder = new ContainerBuilder();
        $Extension = new \Sioweb\Oxid\Kernel\Extension\Extension();
        $Extension->getConfiguration($Config, $ContainerBuilder);
        $ContainerBuilder->registerExtension($Extension);

        $FileLocator = new FileLocator(__DIR__.'/../Resources/config');
        try {
            $FileLocator->locate('bundles.yml');
        } catch(\Exception $e) {
            (new Filesystem())->dumpFile(__DIR__.'/../Resources/config/bundles.yml', Yaml::dump(['oxid-kernel' => ['bundles' => []]]));
        }

        $loader = new YamlFileLoader(
            $ContainerBuilder,
            $FileLocator
        );

        $loader->load('bundles.yml');

        $class2Alias = [];
        $loadBefore = [];

        $arrBundles = $ContainerBuilder->getExtensionConfig('oxid-kernel')[0]['bundles'];
        foreach($arrBundles as &$bundle) {
            $bundle = new $bundle();
            if(empty($loadBefore[get_class($bundle)])) {
                $loadBefore[get_class($bundle)] = 0;
            }
            $loadBefore[get_class($bundle)]++;

            if(method_exists($bundle, 'loadBefore')) {
                foreach ($bundle->loadBefore() as $className) {
                    if(empty($loadBefore[$className])) {
                        $loadBefore[$className] = 0;
                    }
                    $loadBefore[$className]++;
                }
            }
        }

        
        asort($loadBefore);

        foreach($arrBundles as $bundleClass) {
            $loadBefore[get_class($bundleClass)] = $bundleClass;
        }
        unset($bundle);
        unset($arrBundles);

        foreach($loadBefore as $bundle) {
            if(!is_object($bundle)) {
                continue;
            }
            $this->autoloadetBundles[$bundle->getContainerExtension()->getAlias()] = $bundle;
        }

        unset($loadBefore);

        return $this->autoloadetBundles;
    }

    public function setProjectRoot($dir)
    {
        $this->projectDir = rtrim($dir, '/');
    }

    public function getProjectDir()
    {
        return $this->projectDir;
    }

    public function getCacheDir()
    {
        return $this->getProjectDir().'/kernel/var/cache/'.$this->getEnvironment();
    }

    public function getLogDir()
    {
        return $this->getProjectDir().'/kernel/var/log/'.$this->getEnvironment();
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->getRootDir().'/../Resources/config/config_'.$this->getEnvironment().'.yml');

        $configDir = $this->getProjectDir() . '/kernel/config';
        
        if (file_exists($configDir.'/parameters.yml')) {
            $loader->load($configDir.'/parameters.yml');
        }

        if (file_exists($configDir.'/config_'.$this->getEnvironment().'.yml')) {
            $loader->load($configDir.'/config_'.$this->getEnvironment().'.yml');
        } elseif (file_exists($configDir.'/config.yml')) {
            $loader->load($configDir.'/config.yml');
        }
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

            private function getRouteCollection($bundle) : RouteCollection
            {
                return $bundle->getRouteCollection(
                    $this->loader->getResolver(),
                    $this->kernel
                );
            }

            public function getRoutes()
            {
                $Collection = new RouteCollection();
                foreach($this->bundles as $bundle) {
                    if($bundle instanceof BundleRoutesInterface) {
                        $Collection->addCollection($this->getRouteCollection($bundle));
                    }
                }
                return $Collection;
            }
        });
    }

    /**
     * Prepares the ContainerBuilder before it is compiled.
     *
     * @param ContainerBuilder $container A ContainerBuilder instance
     */
    protected function prepareContainer(ContainerBuilder $container)
    {
        $extensions = array();
        foreach ($this->bundles as $bundle) {
            if ($extension = $bundle->getContainerExtension()) {
                $container->registerExtension($extension);
                $extensions[] = $extension->getAlias();
            }

            if ($this->debug) {
                $container->addObjectResource($bundle);
            }
        }
        foreach ($this->bundles as $bundle) {
            $bundle->build($container);
        }

        // ensure these extensions are implicitly loaded
        $MergePass = new MergeExtensionConfigurationPass($extensions);
        $MergePass->setGlobalContainer($container);
        $container->getCompilerPassConfig()->setMergePass($MergePass);
    }

    /**
     * Gets a HTTP kernel from the container.
     *
     * @return HttpKernel
     */
    protected function getHttpKernel()
    {
        return $this->container->get('oxid_http_kernel');
    }

    /**
     * Gets a new ContainerBuilder instance used to build the service container.
     *
     * @return ContainerBuilder
     */
    protected function getContainerBuilder()
    {
        $container = new ContainerBuilder(new ParameterBag($this->getKernelParameters()));
        $container->setBundles($this->autoloadetBundles);

        if (class_exists('ProxyManager\Configuration') && class_exists('Symfony\Bridge\ProxyManager\LazyProxy\Instantiator\RuntimeInstantiator')) {
            $container->setProxyInstantiator(new RuntimeInstantiator());
        }

        return $container;
    }
}
