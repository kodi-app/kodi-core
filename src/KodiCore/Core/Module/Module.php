<?php
/**
 * Created by PhpStorm.
 * User: nagyatka
 * Date: 2017. 08. 25.
 * Time: 11:24
 */

namespace KodiCore\Core\Module;

use KodiCore\Response\Response;


/**
 * Class Module
 * @package KodiCore\Module
 */
abstract class Module
{
    /**
     * @var string
     */
    private $controllerName;

    /**
     * @var string
     */
    private $method;

    /**
     * @var array
     */
    private $urlParams;

    /**
     * @return Response
     */
    public function run(): Response {
        $controllerFullName = $this->getControllerNamespace().$this->controllerName;
        $controller = new $controllerFullName();
        $result = $controller->{$this->method}($this->urlParams);
        if(is_string($result)) {
            $result = new Response($result);
        }
        return $result;
    }

    /**
     *
     */
    abstract public function before(): void;

    /**
     * @return string
     */
    abstract public function getControllerNamespace(): string;

    /**
     * @return array
     */
    abstract public function getRoutes(): array;

    /**
     * @param string $controllerName
     */
    public function setControllerName(string $controllerName)
    {
        $this->controllerName = $controllerName;
    }

    /**
     * @param string $method
     */
    public function setMethod(string $method)
    {
        $this->method = $method;
    }

    /**
     * @param array $urlParams
     */
    public function setUrlParams(array $urlParams)
    {
        $this->urlParams = $urlParams;
    }


}