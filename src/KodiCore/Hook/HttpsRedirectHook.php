<?php
/**
 * Created by PhpStorm.
 * User: nagyatka
 * Date: 2017. 08. 23.
 * Time: 10:58
 */

namespace KodiCore\Hook;


use KodiCore\Core\KodiConf;
use KodiCore\Exception\RedirectException;
use KodiCore\Request\Request;

class HttpsRedirectHook extends  HookInterface
{
    /**
     * @param KodiConf $conf
     * @param Request $request
     * @return Request
     * @throws RedirectException
     */
    public function process(KodiConf $conf, Request $request): Request
    {
        $redirect = "https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        throw new RedirectException($redirect);
    }
}