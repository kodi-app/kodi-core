<?php
/**
 * Created by PhpStorm.
 * User: nagyatka
 * Date: 2016. 07. 23.
 * Time: 20:35
 */

namespace KodiCore;


use KodiCore\Core\Core;
use KodiCore\Core\KodiConf;
use KodiCore\Exception\ConfigurationException;
use KodiCore\Exception\Http\HttpAccessDeniedException;
use KodiCore\Exception\Http\HttpAuthRequiredException;
use KodiCore\Exception\Http\HttpInternalServerErrorException;
use KodiCore\Exception\Http\HttpNotFoundException;
use KodiCore\Exception\Http\HttpServiceTemporarilyUnavailableException;
use KodiCore\Exception\RedirectException;
use KodiCore\Request\Request;
use Pimple\Container;

class Application implements \ArrayAccess
{
    /**
     * Singleton minta
     *
     * @var Application
     */
    private static $instance = null;

    /**
     * @var Core
     */
    private $core;

    /**
     * @var KodiConf
     */
    private $kodiConfiguration;

    /**
     * @var Container
     */
    private $pimpleContainer;

    /**
     * @return Application
     */
    public static function getInstance() {
        if(Application::$instance == null) {
            Application::$instance = new Application();
        }
        return Application::$instance;
    }

    /**
     * @return string
     */
    public static function getEnvMode(): string {
        return self::getInstance()["environment"]["mode"];
    }

    /**
     * @param string $key
     * @return mixed
     */
    public static function getEnv(string $key) {
        return self::getInstance()["environment"][$key];
    }

    /**
     * Application constructor.
     */
    protected function __construct()
    {
        $this->pimpleContainer = new Container();
    }

    /**
     * @param array $conf
     */
    public function run(array $conf): void {
        ob_start();
        $kodiConf = new KodiConf($conf);
        $request = Request::get();
        try {

            // Init application (init services and environment) using KodiConf
            $this->initializeConfiguration($kodiConf);

            // Process request
            $this->core = new Core($this);
            $module = $this->core->processRequest($kodiConf,$request);

            // Run the appropriate module and print result
            print $module->run();

        }
        catch (\Exception $exception) {
            $errorHandler = $kodiConf->getErrorResponseHandler();
            if ($exception instanceof HttpAuthRequiredException) {
                print $errorHandler->error_401($request);
            }
            elseif ($exception instanceof HttpAccessDeniedException) {
                print $errorHandler->error_403($request);
            }
            elseif($exception instanceof HttpNotFoundException) {
                print $errorHandler->error_404($request);
            }
            elseif ($exception instanceof HttpInternalServerErrorException) {
                print $errorHandler->error_500($request);
            }
            elseif ($exception instanceof HttpServiceTemporarilyUnavailableException) {
                print $errorHandler->error_503($request);
            }
            elseif ($exception instanceof RedirectException) {
                $redirect = $exception->getRedirectUrl();
                header("Location:$redirect");
            }
            else {
                print $errorHandler->custom_error($request, $exception);
            }
        }
        ob_end_flush();
    }

    /**
     * @param KodiConf $kodiConf
     * @throws ConfigurationException
     */
    private function initializeConfiguration(KodiConf $kodiConf): void {
        $this->kodiConfiguration                = $kodiConf;
        $this->pimpleContainer["environment"]   = $kodiConf->getEnvironmentSettings();
        foreach ($kodiConf->getServicesConfiguration() as $service) {
            if (is_string($service)) {
                $this->pimpleContainer->register(new $service());
            }
            elseif (is_array($service)) {
                $serviceClassName  = $service["class_name"];
                $serviceParameters = $service["parameters"];
                $this->pimpleContainer->register(new $serviceClassName($serviceParameters));
            }
            else {
                throw new ConfigurationException("Unknown in configuration.");
            }
        }
    }

    /**
     * @return KodiConf
     */
    public function getKodiConf(): KodiConf {
        return $this->kodiConfiguration;
    }

    public function offsetExists($offset)
    {
        return isset($this->pimpleContainer[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->pimpleContainer[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->pimpleContainer[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->pimpleContainer[$offset]);
    }

    /**
     * @return Core
     */
    public function getCore(): Core
    {
        return $this->core;
    }
}