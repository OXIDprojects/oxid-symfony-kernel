<?php

namespace OxidCommunity\SymfonyKernel\Legacy\Core;

use OxidEsales\EshopCommunity\Core\ViewConfig as OxidViewConfig;

class ViewConfig extends ViewConfig_parent
{
    public function getModuleVendorPath($sModule)
    {
        $oModule = oxNew(\OxidEsales\Eshop\Core\Module\Module::class);
        $sModulePath = $oModule->getModulePath($sModule);
        $sModulePath = $this->getConfig()->getModulesDir() . $sModulePath;
        $sModuleLink = str_replace('src/Resources/oxid/', '', readlink($sModulePath));
        $sModuleLink = preg_replace('|\.\./|s', '', $sModuleLink);
        return rtrim(INSTALLATION_ROOT_PATH, '/') . '/' . $sModuleLink;
    }

    public function getModuleAssetsPath($sModule, $sFile = '', $relative = false)
    {
        if (!$sFile || ($sFile[0] != '/')) {
            $sFile = '/' . $sFile;
        }
        $VendorPath = $this->getModuleVendorPath($sModule);

        $OutDir = $this->getConfig()->getOutDir();
        $Composer = json_decode(file_get_contents(rtrim($VendorPath, '/') . '/composer.json'), true);
        $sFileAbsolute = rtrim(rtrim($OutDir, '/') . '/assets/modules/' . str_replace('/', '', $Composer['name']), '/') . $sFile;
        $sFileRelative = $sFileAbsolute;
        if($relative) {
            $OutDir = '/' . str_replace(OX_BASE_PATH, '', $OutDir);
            $sFileRelative = rtrim(rtrim($OutDir, '/') . '/assets/modules/' . str_replace('/', '', $Composer['name']), '/') . $sFile;
        }

        // die('<pre>' . __METHOD__ . ":\n" . print_r($sFileRelative, true) . "\n#################################\n\n" . '</pre>');
        if (file_exists($sFileAbsolute) || is_dir($sFileAbsolute)) {
            return $sFileRelative;
        } else {
            /**
             * Do not call oxNew in the exception handling of the module subsystem system, as the same module system will be
             * involved when calling oxNew
             */
            $exception = new \OxidEsales\Eshop\Core\Exception\FileException("Requested file not found for module $sModule ($sFileAbsolute)");
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
