<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace Sioweb\Oxid\Kernel\Legacy\Core;

use Symfony\Component\HttpFoundation\Request;

/**
 * Seo encoder base
 */
class SeoDecoder extends SeoDecoder_parent
{

    /**
     * processSeoCall handles Server information and passes it to decoder
     *
     * @param string $sRequest request
     * @param string $sPath    path
     *
     * @access public
     */
    public function processSeoCall($sRequest = null, $sPath = null)
    {
        $request = Request::createFromGlobals();
        if($request->query->get('_controller') != 1) {
            return;
        }

        return parent::processSeoCall($sRequest, $sPath);
    }
}
