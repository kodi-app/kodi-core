<?php
/**
 * Created by PhpStorm.
 * User: nagyatka
 * Date: 2017. 08. 30.
 * Time: 11:36
 */

namespace KodiCore\Response;


use KodiCore\Application;
use KodiCore\Core\KodiConf;
use KodiCore\Request\Request;

class DefaultErrorResponse implements ErrorResponse
{

    public function error_401(Request $request): Response
    {
        return new Response(Response::$statusTexts[401],401);
    }

    public function error_403(Request $request): Response
    {
        return new Response(Response::$statusTexts[403],403);
    }

    public function error_404(Request $request): Response
    {
        return new Response(Response::$statusTexts[404],404);
    }

    public function error_500(Request $request): Response
    {
        return new Response(Response::$statusTexts[500],500);
    }

    public function error_503(Request $request): Response
    {
        return new Response(Response::$statusTexts[503],503);
    }

    public function custom_error(Request $request, \Exception $exception): Response
    {
        if(Application::getEnvMode() == KodiConf::ENV_DEVELOPMENT){
            $result = $exception->getMessage()."<br>".str_replace("#","<br>#",$exception->getTraceAsString());
            return new Response($result,500);
        }
        return $this->error_500($request);
    }
}