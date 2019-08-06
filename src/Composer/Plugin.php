<?php

declare (strict_types = 1);

namespace OxidCommunity\SymfonyKernel\Composer;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Package\CompletePackage;
use Composer\Package\RootPackage;
use Composer\Plugin\PluginInterface;
use Composer\Repository\RepositoryInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;
use OxidEsales\ComposerPlugin\Installer\PackageInstallerTrigger;
use OxidEsales\ComposerPlugin\Installer\Package\AbstractPackageInstaller;
use OxidEsales\ComposerPlugin\Installer\Package\ModulePackageInstaller;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;
use Webmozart\PathUtil\Path;

class Plugin implements PluginInterface, EventSubscriberInterface
{
    /**
     * @var Filesystem
     */
    private $filesystem;
    private $resetBundleYml = false;

    private static $instance = null;

    /** @var PackageInstallerTrigger */
    private $packageInstallerTrigger;

    public function __construct(Filesystem $filesystem = null)
    {
        $this->filesystem = $filesystem ?: new Filesystem();
    }

    public static function registrateRootPlugin(Event $event)
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }

        self::$instance->composer = $event->getComposer();
        self::$instance->io = $event->getIO();
        if (class_exists(PackageInstallerTrigger::class)) {
            self::$instance->packageInstallerTrigger = new PackageInstallerTrigger(self::$instance->io, self::$instance->composer);
        }

        self::$instance->registrateBundle($event->getComposer()->getPackage(), $event->getIO());
    }

    /**
     * {@inheritdoc}
     */
    public function activate(Composer $composer, IOInterface $io): void
    {
        $this->composer = $composer;
        $this->io = $io;

        if (!class_exists(PackageInstallerTrigger::class)) {
            return;
        }

        $this->packageInstallerTrigger = new PackageInstallerTrigger($io, $composer);

        $extraSettings = $this->composer->getPackage()->getExtra();
        if (isset($extraSettings[AbstractPackageInstaller::EXTRA_PARAMETER_KEY_ROOT])) {
            $this->packageInstallerTrigger->setSettings($extraSettings[AbstractPackageInstaller::EXTRA_PARAMETER_KEY_ROOT]);
        }

        $RootPath = $this->packageInstallerTrigger->getShopSourcePath();
        $repo = $this->composer->getRepositoryManager()->getLocalRepository();

        foreach ($repo->getPackages() as $Package) {
            if ($Package->getName() === 'oxid-community/symfony-kernel') {
                $packageInstaller = new ModulePackageInstaller($this->io, $RootPath, $Package);
                $SourceDir = $this->formSourcePath($Package);
                $TargetDir = $this->formTargetPath();
                if (is_dir($SourceDir)) {
                    if (!is_dir($TargetDir)) {
                        $io->write('<info>oxid-community/symfony-kernel:</info> Oxid kernel will be installed into oxid modules directory.');
                        $packageInstaller->install($this->packageInstallerTrigger->getInstallPath($Package));
                    } else {
                        $io->write('<info>oxid-community/symfony-kernel:</info> Oxid kernel will be reintegrated into oxid modules directory.');
                        $packageInstaller->install($this->packageInstallerTrigger->getInstallPath($Package));
                    }
                }
                break;
            }
        }

        // $this->registrateBundles($repo->getPackages(), $io, true);
    }

    /**
     * If module source directory option provided add it's relative path.
     * Otherwise return plain package path.
     *
     * @param string $packagePath
     *
     * @return string
     */
    protected function formSourcePath($Package)
    {
        $RootPath = $this->packageInstallerTrigger->getShopSourcePath();
        // "source-directory": "src/Resources/oxid",
        // "target-directory": "oxid-community/symfony-kernel"
        $sourceDirectory = 'src/Resources/oxid';
        $packagePath = $this->packageInstallerTrigger->getInstallPath($Package);

        return $RootPath . !empty($sourceDirectory) ?
        Path::join($packagePath, $sourceDirectory) :
        $packagePath;
    }

    /**
     * @return string
     */
    protected function formTargetPath()
    {
        $RootPath = $this->packageInstallerTrigger->getShopSourcePath();
        return Path::join($RootPath, 'modules', 'oxid-community/symfony-kernel');
    }

    public function resetBundleYml(Event $event): void
    {
        $this->resetBundleYml = true;
    }

    public function addBundles(Event $event): void
    {
        $this->registrateBundlesFromExtra($event->getComposer()->getRepositoryManager()->getLocalRepository(), $event->getIO());
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ScriptEvents::PRE_INSTALL_CMD => 'resetBundleYml',
            ScriptEvents::PRE_UPDATE_CMD => 'resetBundleYml',
            ScriptEvents::POST_INSTALL_CMD => 'addBundles',
            ScriptEvents::POST_UPDATE_CMD => 'addBundles',
        ];
    }

    protected function registrateBundle(RootPackage $Package, $io)
    {
        $this->addPluginsToBundleYml($this->loadPluginClasses($Package, $io), $io);
    }

    protected function registrateBundlesFromExtra(RepositoryInterface $Packages, $io, bool $static = false)
    {
        $plugins = [];
        $Packages = $this->composer->getRepositoryManager()->getLocalRepository();
        foreach ($Packages->getPackages() as $package) {
            $plugins = array_merge($plugins, $this->loadPluginClasses($package, $io));
        }

        $this->addPluginsToBundleYml($plugins, $io, $static);
    }

    private function loadPluginClasses($Package, $io)
    {
        $plugins = [];
        foreach ($this->getPluginClasses($Package) as $name => $classes) {
            if (!is_array($classes)) {
                $classes = [$classes];
            }
            
            foreach ($classes as $customname => $class) {
                if (!class_exists($class)) {
                    $io->write(' - Class not found for ' . $name, true, IOInterface::VERY_VERBOSE);
                    //     throw new \RuntimeException(sprintf('The plugin class "%s" was not found.', $class));
                }

                $io->write(' - Added plugin for ' . $name, true, IOInterface::VERY_VERBOSE);

                $plugins[$name] = $class;
            }
        }

        return $plugins;
    }

    private function addPluginsToBundleYml($plugins, $io, bool $static = false)
    {
        if (!$static && !empty($plugins)) {
            $Config = [];
            $ContainerBuilder = new ContainerBuilder();
            $Extension = new \OxidCommunity\SymfonyKernel\Extension\Extension();
            $Extension->getConfiguration($Config, $ContainerBuilder);
            $ContainerBuilder->registerExtension($Extension);

            $FileLocator = new FileLocator(__DIR__ . '/../Resources/config');
            try {
                $FileLocator->locate('bundles.yml');
            } catch (\Exception $e) {
                $this->filesystem->dumpFile(__DIR__ . '/../Resources/config/bundles.yml', Yaml::dump(['oxid-symfony-kernel' => ['bundles' => []]]));
            }

            $loader = new YamlFileLoader(
                $ContainerBuilder,
                $FileLocator
            );

            $loader->load('bundles.yml');

            $Bundles = [
                'oxid-symfony-kernel' => [
                    'bundles' => (array) $ContainerBuilder->getExtensionConfig('oxid-symfony-kernel')[0]['bundles'],
                ],
            ];

            if($this->resetBundleYml) {
                $Bundles['oxid-symfony-kernel']['bundles'] = $plugins;
                $this->resetBundleYml = false;
            } else {
                $Bundles['oxid-symfony-kernel']['bundles'] = array_merge($Bundles['oxid-symfony-kernel']['bundles'], $plugins);
            }
            
            $this->filesystem->dumpFile(__DIR__ . '/../Resources/config/bundles.yml', Yaml::dump($Bundles));

            if(defined('INSTALLATION_ROOT_PATH')) {
                return;
            }
            define('INSTALLATION_ROOT_PATH', str_replace('/vendor', '', $this->composer->getConfig()->get('vendor-dir')));
            define('OX_BASE_PATH', INSTALLATION_ROOT_PATH . DIRECTORY_SEPARATOR . 'source' . DIRECTORY_SEPARATOR);
            define('OX_LOG_FILE', OX_BASE_PATH . 'log' . DIRECTORY_SEPARATOR . 'oxideshop.log');
            define('OX_OFFLINE_FILE', OX_BASE_PATH . 'offline.html');
            define('VENDOR_PATH', INSTALLATION_ROOT_PATH . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR);

            if (is_file(OX_BASE_PATH . '/../source/overridablefunctions.php')) {
                require_once OX_BASE_PATH . '/../source/overridablefunctions.php';

                if (file_exists(OX_BASE_PATH . 'config.inc.php')) {
                    $this->mapOxidConfig(OX_BASE_PATH . 'config.inc.php', 'parameters');
                }
                // if(file_exists(OX_BASE_PATH . 'config.development.inc.php')) {
                //     $this->mapOxidConfig(OX_BASE_PATH . 'config.development.inc.php', 'parameters_dev');
                // }
            }
        }
    }

    private function mapOxidConfig($ConfigFile, $ParameterFile)
    {
        // require_once OX_BASE_PATH . '/../source/oxfunctions.php';
        $OxidConfig = new \OxidEsales\Eshop\Core\ConfigFile($ConfigFile);

        if (is_array($OxidConfig = $OxidConfig->getVars())) {

            $OxidConfig = array_change_key_case((array) $OxidConfig, CASE_LOWER);
            $Mapping = [
                'database_host' => 'dbHost',
                'database_port' => 'dbPort',
                'database_user' => 'dbUser',
                'database_password' => 'dbPwd',
                'database_name' => 'dbName',
            ];

            foreach ($Mapping as $sParam => $oxParam) {
                if (empty($OxidConfig[$sParam])) {
                    $OxidConfig[$sParam] = $OxidConfig[strtolower($oxParam)];
                    unset($OxidConfig[strtolower($oxParam)]);
                }
            }

            if (!file_exists(INSTALLATION_ROOT_PATH . '/kernel/config/' . $ParameterFile . '.yml')) {
                $this->filesystem->dumpFile(INSTALLATION_ROOT_PATH . '/kernel/config/' . $ParameterFile . '.yml', Yaml::dump([
                    'parameters' => $OxidConfig,
                ]));
            } else {
                $ContainerBuilder = new ContainerBuilder();
                $Extension = new \OxidCommunity\SymfonyKernel\Extension\Extension();
                $Extension->getConfiguration([], $ContainerBuilder);
                $ContainerBuilder->registerExtension($Extension);

                $loader = new YamlFileLoader(
                    $ContainerBuilder,
                    new FileLocator(INSTALLATION_ROOT_PATH . '/kernel/config')
                );

                $loader->load($ParameterFile . '.yml');

                $Parameters = [
                    'parameters' => array_merge(
                        $OxidConfig,
                        $ContainerBuilder->getParameterBag()->all()
                    ),
                ];
                $this->filesystem->dumpFile(INSTALLATION_ROOT_PATH . '/kernel/config/' . $ParameterFile . '.yml', Yaml::dump($Parameters));
            }
        }
    }

    /**
     * @return array<string,string>
     */
    private function getPluginClasses(CompletePackage $package): array
    {
        $extra = $package->getExtra();

        if (!isset($extra['oxid-kernel-plugin'])) {
            return [];
        }

        if (is_string($extra['oxid-kernel-plugin'])) {
            return [$package->getName() => $extra['oxid-kernel-plugin']];
        }

        if (is_array($extra['oxid-kernel-plugin'])) {
            $Plugins = [];
            foreach ($extra['oxid-kernel-plugin'] as $customName => $class) {
                if (!is_numeric($customName)) {
                    $Plugins[$customName] = $class;
                } elseif (empty($Plugins[$package->getName()])) {
                    $Plugins[$package->getName()] = $class;
                } else {
                    $Plugins[$package->getName() . '/' . $customName] = $class;
                }
            }

            return $Plugins;
        }

        throw new \RuntimeException('Invalid value for "extra.oxid-kernel-plugin".');
    }
}
