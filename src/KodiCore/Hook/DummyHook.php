<?php
/**
 * Created by PhpStorm.
 * User: nagyatka
 * Date: 2017. 08. 22.
 * Time: 12:55
 */

namespace KodiCore\Hook;


use KodiCore\Core\KodiConf;
use KodiCore\Request\Request;

class DummyHook extends HookInterface
{
    /**
     * @param KodiConf $conf
     * @param Request $request
     * @return Request
     */
    public function process(KodiConf $conf, Request $request): Request
    {
        return $request;
    }
}