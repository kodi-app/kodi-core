<?php

namespace KodiCore\Core;

use KodiCore\Core\Module\Module;
use KodiCore\Core\Module\ProjectModule;
use KodiCore\Exception\ConfigurationException;
use KodiCore\Response\DefaultErrorResponse;
use KodiCore\Response\ErrorResponse;

class KodiConf
{
    const ENVIRONMENT       = "environment";
    const HOOKS             = "hooks";
    const SERVICES          = "services";
    const MODULES           = "modules";
    const ROUTER            = "router";
    const ROUTES            = "routes";

    const ENV_DEVELOPMENT   = "development";
    const ENV_PRODUCTION    = "production";


    /**
     * @var array
     */
    private $monolithicConfiguration;


    /**
     * KodiConf constructor.
     * @param array $monolithicConfiguration
     */
    public function __construct(array $monolithicConfiguration)
    {
        $this->monolithicConfiguration = $monolithicConfiguration;
    }

    /**
     * @return array
     * @throws ConfigurationException
     */
    public function getEnvironmentSettings(): array {
        if(!isset($this->monolithicConfiguration[self::ENVIRONMENT]))
            throw new ConfigurationException("Missing environment configuration");
        return $this->monolithicConfiguration["environment"];
    }

    /**
     * @return array
     * @throws ConfigurationException
     */
    public function getHooksConfiguration(): array {
        if(!isset($this->monolithicConfiguration[self::HOOKS]))
            throw new ConfigurationException("Missing hooks configuration");
        return $this->monolithicConfiguration["hooks"];
    }

    /**
     * @return array
     * @throws ConfigurationException
     */
    public function getServicesConfiguration(): array {
        if(!isset($this->monolithicConfiguration[self::SERVICES]))
            throw new ConfigurationException("Missing services configuration");
        return $this->monolithicConfiguration["services"];
    }

    /**
     * @return array
     * @throws ConfigurationException
     */
    public function getModulesConfiguration(): array {
        if(!isset($this->monolithicConfiguration[self::MODULES]))
            return [];
        return $this->monolithicConfiguration["modules"];
    }

    /**
     * @return array
     * @throws ConfigurationException
     */
    public function getRoutesConfiguration(): array {
        $routes = [];

        // Load project level routes
        if(!isset($this->monolithicConfiguration[self::ROUTES]))
            throw new ConfigurationException("Missing routes configuration");
        $projectRoutes = $this->monolithicConfiguration[self::ROUTES];
        foreach ($projectRoutes as &$projectRoute) {
            $projectRoute["handler"] = ProjectModule::class."::".$projectRoute["handler"];
        }
        $routes = array_merge($routes,$projectRoutes);

        // Load module level routes
        $modulesConfiguration = $this->getModulesConfiguration();
        foreach ($modulesConfiguration as $moduleClassName => $moduleParams) {
            // TODO: Enable to override module routes from KodiConf if necessary
            /** @var Module $module */
            $module = new $moduleClassName();
            $moduleRoutes = $module->getRoutes();
            foreach ($moduleRoutes as &$moduleRoute) {
                $moduleRoute["handler"] = $module."::".$moduleRoute["handler"];
            }
            $routes = array_merge($routes,$moduleRoutes);
        }

        return $routes;
    }

    /**
     * @return array
     */
    public function getRouterConfiguration(): array {
        if(isset($this->monolithicConfiguration[self::ROUTER])) {
            return [];
        }
        return $this->monolithicConfiguration[self::ROUTER];
    }

    public function getErrorResponseHandler(): ErrorResponse {
        if(isset($this->monolithicConfiguration["error_handler"])) {
            $errorHandlerClassName = $this->monolithicConfiguration["error_handler"];
            return new $errorHandlerClassName();
        }
        return new DefaultErrorResponse();
    }
}