<?php
/**
 * Created by PhpStorm.
 * User: nagyatka
 * Date: 2017. 08. 22.
 * Time: 12:46
 */

namespace KodiCore\Hook;


use KodiCore\Core\KodiConf;
use KodiCore\Request\Request;

abstract class HookInterface
{
    /**
     * @var array
     */
    private $parameters;

    /**
     * HookInterface constructor.
     * @param array $parameters
     */
    public function __construct(array $parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * @param KodiConf $kodiConf
     * @param Request $request
     * @return Request
     */
    abstract public function process(KodiConf $kodiConf, Request $request):Request;

    /**
     * @param string $key
     * @return mixed
     */
    public function getParameterByKey(string $key) {
        if(!isset($this->parameters[$key])) return null;
        return $this->parameters[$key];
    }
}