<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidCommunity\SymfonyKernel\Composer\Installer\Package;

use OxidEsales\ComposerPlugin\Installer\Package\ModulePackageInstaller as OxidModulePackageInstaller;
use OxidCommunity\SymfonyKernel\Composer\Utilities\CopyFileManager\CopyGlobFilteredFileManager;
use OxidEsales\ComposerPlugin\Installer\Package\ShopPackageInstaller;

/**
 * @inheritdoc
 */
class ModulePackageInstaller extends OxidModulePackageInstaller
{

    /**
     * Copies module files to shop directory.
     *
     * @param string $packagePath
     */
    public function install($packagePath)
    {
        $this->symlinkPackage($packagePath);
    }

    /**
     * Update module files.
     *
     * @param string $packagePath
     */
    public function update($packagePath)
    {
        if ($this->askQuestionIfNotInstalled("Update operation will overwrite {$this->getPackageName()} files."
            ." Do you want to continue? (y/N) ")) {
            $this->getIO()->write("Copying module {$this->getPackageName()} files...");
            $this->symlinkPackage($packagePath);
        }
    }

    /**
     * Copy files from package source to defined target path.
     *
     * @param string $packagePath Absolute path to the package.
     */
    protected function symlinkPackage($packagePath)
    {
        $filtersToApply = [
            $this->getBlacklistFilterValue(),
            $this->getVCSFilter(),
        ];

        CopyGlobFilteredFileManager::symlink(
            $this->formSourcePath($packagePath),
            $this->formTargetPath(),
            $this->getCombinedFilters($filtersToApply)
        );

        if(is_dir($publicPath = rtrim($packagePath, '/') . '/src/Resources/public/')) {
            $publicTarget = rtrim($this->getRootDirectory(), '/') . '/' . ShopPackageInstaller::SHOP_SOURCE_DIRECTORY . '/out/assets/modules/';

            if(!is_dir($publicTarget)) {
                mkdir($publicTarget, 0777, true);
            }

            CopyGlobFilteredFileManager::symlink(
                $publicPath,
                rtrim($publicTarget, '/') . '/' . str_replace('/', '', $this->getPackage()->getName()),
                $this->getCombinedFilters($filtersToApply)
            );
        }
    }

    private function getRelativePath(string $path): string
    {
        return str_replace(strtr($this->getRootDirectory(), '\\', '/').'/', '', strtr($path, '\\', '/'));
    }
}
