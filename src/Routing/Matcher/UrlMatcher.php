<?php

namespace OxidCommunity\SymfonyKernel\Routing\Matcher;

use Symfony\Component\HttpFoundation\Request;

class UrlMatcher extends \Symfony\Component\Routing\Matcher\UrlMatcher
{

    /**
     * {@inheritdoc}
     */
    public function checkRoute(Request $request)
    {
        if ($ret = $this->matchCollection(rawurldecode($request->getPathInfo()), $this->routes)) {
            return $ret;
        }

        $this->request = null;

        return null;
    }

}
