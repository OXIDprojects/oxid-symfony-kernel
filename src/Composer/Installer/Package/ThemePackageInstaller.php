<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidCommunity\SymfonyKernel\Composer\Installer\Package;

use OxidEsales\ComposerPlugin\Installer\Package\ThemePackageInstaller as OxidThemePackageInstaller;
use OxidCommunity\SymfonyKernel\Composer\Utilities\CopyFileManager\CopyGlobFilteredFileManager;

/**
 * @inheritdoc
 */
class ThemePackageInstaller extends OxidThemePackageInstaller
{
    /**
     * Copies theme files to shop directory.
     *
     * @param string $packagePath
     */
    public function install($packagePath)
    {
        $this->getIO()->write("Installing {$this->getPackage()->getName()} package");
        $this->symlinkPackage($packagePath);
    }

    /**
     * Overwrites theme files.
     *
     * @param string $packagePath
     */
    public function update($packagePath)
    {
        $packageName = $this->getPackage()->getName();
        $question = "Update operation will overwrite $packageName files. Do you want to continue? (y/N) ";

        if ($this->askQuestionIfNotInstalled($question)) {
            $this->getIO()->write("Copying theme {$this->getPackage()->getName()} files...");
            $this->symlinkPackage($packagePath);
        }
    }

    /**
     * @param string $packagePath
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
            $publicTarget = rtrim($this->getRootDirectory(), '/') . '/out/assets/themes/';

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
}
