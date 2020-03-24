<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidCommunity\SymfonyKernel\Composer\Installer\Package;

use Webmozart\PathUtil\Path;
use OxidEsales\ComposerPlugin\Installer\Package\ThemePackageInstaller as OxidThemePackageInstaller;
use OxidCommunity\SymfonyKernel\Composer\Utilities\CopyFileManager\CopyGlobFilteredFileManager;
use OxidEsales\ComposerPlugin\Installer\Package\ShopPackageInstaller;

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
            $this->formThemeTargetPath(),
            $this->getCombinedFilters($filtersToApply)
        );

        if(is_dir($publicPath = rtrim($packagePath, '/') . '/src/Resources/public/')) {
            $publicTarget = rtrim($this->getRootDirectory(), '/') . '/' . ShopPackageInstaller::SHOP_SOURCE_DIRECTORY . '/out/assets/themes/';

            if(!is_dir($publicTarget)) {
                mkdir($publicTarget, 0777, true);
            }

            // echo '<pre>' . __METHOD__ . ":\n" . print_r([
            //     $publicPath,
            //     rtrim($publicTarget, '/') . '/' . str_replace('/', '', $this->formAssetsDirectoryName())
            // ], true) . "\n#################################\n\n" . '</pre>';

            // Array
            // (
            //     [0] => /app/vendor/cihaeuser/theme/src/Resources/public/
            //     [1] => /app/source/out/assets/themes/ci
            // )


            CopyGlobFilteredFileManager::symlink(
                $publicPath,
                rtrim($publicTarget, '/') . '/' . str_replace('/', '', $this->formAssetsDirectoryName()),
                $this->getCombinedFilters($filtersToApply)
            );
        }
    }
    /**
     * If module source directory option provided add it's relative path.
     * Otherwise return plain package path.
     *
     * @param string $packagePath
     *
     * @return string
     */
    protected function formSourcePath($packagePath)
    {
        $sourceDirectory = $this->getExtraParameterValueByKey(static::EXTRA_PARAMETER_KEY_SOURCE);

        return !empty($sourceDirectory)?
            Path::join($packagePath, $sourceDirectory):
            $packagePath;
    }

    private function getRelativePath(string $path): string
    {
        return str_replace(strtr($this->getRootDirectory(), '\\', '/').'/', '', strtr($path, '\\', '/'));
    }
}
