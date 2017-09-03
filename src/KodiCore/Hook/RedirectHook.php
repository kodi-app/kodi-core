<?php
/**
 * Created by PhpStorm.
 * User: nagyatka
 * Date: 2017. 09. 03.
 * Time: 16:48
 */

namespace KodiCore\Hook;


use KodiCore\Core\KodiConf;
use KodiCore\Exception\RedirectException;
use KodiCore\Request\Request;

class RedirectHook extends HookInterface
{
    /**
     * Used parameter name: redirect_url
     *
     * @param KodiConf $kodiConf
     * @param Request $request
     * @return Request
     * @throws RedirectException
     */
    public function process(KodiConf $kodiConf, Request $request): Request
    {
        throw new RedirectException($this->getParameterByKey("redirect_url"));
    }
}