<?php

namespace Sioweb\Oxid\Kernel\Legacy\Core;

use Symfony\Component\HttpFoundation\Request;

class SeoDecoder extends SeoDecoder_parent
{

    /**
     * processSeoCall check if symfony route is active, otherwise go for default function
     *
     * @param string $sRequest request
     * @param string $sPath    path
     *
     * @access public
     */
    public function processSeoCall($sRequest = null, $sPath = null)
    {
        $_controller = Request::createFromGlobals()->query->get('_controller');
        if(!empty($_controller) && $_controller != 1) {
            return;
        }

        return parent::processSeoCall($sRequest, $sPath);
    }
}
