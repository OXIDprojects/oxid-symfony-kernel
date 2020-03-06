<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidCommunity\SymfonyKernel\Composer\Installer;

use Composer\Package\PackageInterface;
use OxidEsales\ComposerPlugin\Installer\PackageInstallerTrigger as OxidPackageInstallerTrigger;
use OxidEsales\ComposerPlugin\Installer\Package\ShopPackageInstaller;
use OxidCommunity\SymfonyKernel\Composer\Installer\Package\ModulePackageInstaller;
use OxidCommunity\SymfonyKernel\Composer\Installer\Package\ThemePackageInstaller;

/**
 * Class responsible for triggering installation process.
 */
class PackageInstallerTrigger extends OxidPackageInstallerTrigger
{
    /** @var array Available installers for packages. */
    private $installers = [
        self::TYPE_ESHOP => ShopPackageInstaller::class,
        self::TYPE_MODULE => ModulePackageInstaller::class,
        self::TYPE_THEME => ThemePackageInstaller::class,
    ];

    /**
     * @param PackageInterface $package
     */
    public function installPackage(PackageInterface $package)
    {
        $installer = $this->createInstaller($package);
        if (!$installer->isInstalled()) {
            $installer->install($this->getInstallPath($package));
        }
    }

    /**
     * @param PackageInterface $package
     */
    public function updatePackage(PackageInterface $package)
    {
        $installer = $this->createInstaller($package);
        $installer->update($this->getInstallPath($package));
    }
}
