<?php
/**
 * Copyright (C) 2014 Arthur Halet
 *
 * This software is distributed under the terms and conditions of the 'MIT'
 * license which can be found in the file 'LICENSE' in this package distribution
 * or at 'http://opensource.org/licenses/MIT'.
 *
 * Author: Arthur Halet
 * Date: 14/03/2015
 */

namespace Arhframe\Util;


class Proxy
{

    /**
     * @param null $context
     * @return resource
     */
    public static function createStreamContext($context = null)
    {
        $proxy = self::getProxyHttp();
        if (empty($proxy)) {
            return empty($context) ? null : stream_context_create($context);
        }
        $proxy = preg_replace("#^http(s)?#i", "tcp", $proxy);
        $proxyContext = array(
            'proxy' => $proxy,
            'request_fulluri' => true
        );
        if (empty($context)) {
            $context['http'] = $proxyContext;
        } else {
            $context['http'] = array_merge($proxyContext, $context['http']);
        }
        return stream_context_create($context);
    }

    /**
     * @return null|string
     */
    public static function getProxyHttp()
    {
        $proxyKeys = array("HTTP_PROXY", "HTTPS_PROXY", "http_proxy", "https_proxy");
        $proxyUri = null;
        foreach ($proxyKeys as $proxyKey) {
            $proxy = getenv($proxyKey);
            if (!empty($proxy)) {
                $proxyUri = $proxy;
                break;
            }
            $proxy = $_SERVER[$proxyKey];
            if (!empty($proxy)) {
                $proxyUri = $proxy;
                break;
            }
        }
        return $proxyUri;
    }
}
