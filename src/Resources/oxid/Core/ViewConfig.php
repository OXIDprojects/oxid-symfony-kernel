<?php

namespace OxidCommunity\SymfonyKernel\Legacy\Core;

use OxidEsales\EshopCommunity\Core\ViewConfig as OxidViewConfig;

class ViewConfig extends ViewConfig_parent
{
    public function getModuleVendorPath($sModule)
    {
        $oModule = oxNew(\OxidEsales\Eshop\Core\Module\Module::class);
        $sModulePath = $oModule->getModulePath($sModule);
        return str_replace('src/Resources/oxid', '', readlink($this->getConfig()->getModulesDir() . $sModulePath));
    }

    public function getModuleAssetsPath($sModule, $sFile = '')
    {
        if (!$sFile || ($sFile[0] != '/')) {
            $sFile = '/' . $sFile;
        }
        $VendorPath = $this->getModuleVendorPath($sModule);
        $Composer = json_decode(file_get_contents($VendorPath . 'composer.json'), true);
        $sFile = rtrim(rtrim($this->getConfig()->getOutDir(), '/') . '/assets/modules/' . str_replace('/', '', $Composer['name']), '/') . $sFile;
        if (file_exists($sFile) || is_dir($sFile)) {
            return $sFile;
        } else {
            /**
             * Do not call oxNew in the exception handling of the module subsystem system, as the same module system will be
             * involved when calling oxNew
             */
            $exception = new \OxidEsales\Eshop\Core\Exception\FileException("Requested file not found for module $sModule ($sFile)");
            if ($this->getConfig()->getConfigParam('iDebug')) {
                throw $exception;
            } else {
                /**
                 * This error should be reported, as it will be the cause of an unexpected behavior of the shop an the
                 * operator should be given a chance to analyse the issue.
                 */
                $exception->debugOut();
                return '';
            }
        }
    }

    public function getModuleAssetsUrl($sModule, $sFile = '')
    {
        $c = $this->getConfig();
        $shopUrl = null;
        if ($this->isAdmin()) {
            if ($c->isSsl()) {
                // From admin and with SSL we try to use sAdminSSLURL config directive
                $shopUrl = $c->getConfigParam('sAdminSSLURL');
                if ($shopUrl) {
                    // but we don't need the admin directory
                    $adminDir = '/'.$c->getConfigParam('sAdminDir');
                    $shopUrl = substr($shopUrl, 0, -strlen($adminDir));
                } else {
                    // if no sAdminSSLURL directive were defined we use sSSLShopURL config directive instead
                    $shopUrl = $c->getConfigParam('sSSLShopURL');
                }
            }
            // From admin and with no config usefull directive, we use the sShopURL directive
            if (!$shopUrl) {
                $shopUrl = $c->getConfigParam('sShopURL');
            }
        }
        // We are either in front, or in admin with no $sShopURL defined
        if (!$shopUrl) {
            $shopUrl = $c->getCurrentShopUrl();
        }
        $shopUrl = rtrim($shopUrl, '/');

        $sUrl = str_replace(
            rtrim($c->getConfigParam('sShopDir'), '/'),
            $shopUrl,
            $this->getModuleAssetsPath($sModule, $sFile)
        );

        return $sUrl;
    }
}
