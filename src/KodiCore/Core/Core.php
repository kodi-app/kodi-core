<?php
/**
 * Created by PhpStorm.
 * User: nagyatka
 * Date: 2017. 08. 13.
 * Time: 23:45
 */

namespace KodiCore\Core;


use KodiCore\Application;
use KodiCore\Core\Module\Module;
use KodiCore\Core\Module\ModuleParams;
use KodiCore\Core\Router\RouterInterface;
use KodiCore\Exception\CoreException;
use KodiCore\Hook\HookInterface;
use KodiCore\Request\Request;

class Core
{
    /**
     * Registered hooks in the core
     * @var HookInterface[]
     */
    private $registeredHooks;

    /**
     * Core constructor.
     * @param Application $application
     * @throws CoreException
     */
    public function __construct(Application $application)
    {
        // Get configuration
        $hookConfiguration = $application->getKodiConf()->getHooksConfiguration();

        // Hook registration
        $this->registeredHooks = [];
        foreach ($hookConfiguration as $hook) {
            if (is_string($hook)) {
                $this->registeredHooks[] = new $hook();
            }
            elseif (is_array($hook)) {
                $hookClassName  = $hook["class_name"];
                $hookParameters = $hook["parameters"];
                if(!$hookParameters) $hookParameters = [];
                $this->registeredHooks[] = new $hookClassName($hookParameters);
            }
            else {
                throw new CoreException("Unknown hook in configuration.");
            }
        }
    }

    /**
     * @param KodiConf $kodiConf
     * @param Request $request
     * @return Module
     */
    public function processRequest(KodiConf $kodiConf, Request $request): Module {

        // Run hooks
        foreach ($this->registeredHooks as $registeredHook) {
            /** @var HookInterface $registeredHook */
            $request = $registeredHook->process($kodiConf, $request);
        }

        // Run router
        $routerConfiguration = $kodiConf->getRouterConfiguration();
        $routerClassName = $routerConfiguration["class_name"];
        /** @var RouterInterface $router */
        $router = new $routerClassName($routerConfiguration["parameters"]);
        $router->setRoutes($kodiConf->getRoutesConfiguration());
        $routerResult = $router->findRoute($request->getHttpMethod(),$request->getUri());
        $parts = $controllerParts = explode("::", $routerResult["handler"]);


        // Initialize module
        $moduleClassName = $parts[0];
        /** @var Module $module */
        $module = new $moduleClassName();
        $module->setControllerName($parts[1]);
        $module->setMethod($parts[2]);
        $module->setUrlParams($routerResult["params"]);
        ModuleParams::setParams($kodiConf->getModulesConfiguration()[$moduleClassName]);
        $module->before();

        return $module;
    }
}