<?php
/**
 * Created by PhpStorm.
 * User: nagyatka
 * Date: 2017. 09. 03.
 * Time: 17:03
 */

namespace KodiCore\Exception;


use Throwable;

class RedirectException extends \Exception
{
    private $redirectUrl;

    public function __construct($redirectUrl, $message = "", $code = 0, Throwable $previous = null)
    {
        $this->redirectUrl = $redirectUrl;
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return string
     */
    public function getRedirectUrl(): string
    {
        return $this->redirectUrl;
    }

}