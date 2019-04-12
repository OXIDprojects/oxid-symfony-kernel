<?php

namespace Sioweb\Oxid\Kernel;

use Sioweb\Oxid\Kernel\Extension\Extension;
use Symfony\Component\HttpKernel\Bundle\Bundle AS BaseBundle;

/**
 * Configures the Contao Glossar bundle.
 *
 * @author Sascha Weidner <https://www.sioweb.de>
 */
class OxidKernelBundle extends BaseBundle
{
    /**
     * {@inheritdoc}
     */
    public function getContainerExtension()
    {
        return new Extension();
    }
}
