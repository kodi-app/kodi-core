<?php
/**
 * Created by PhpStorm.
 * User: nagyatka
 * Date: 2017. 08. 25.
 * Time: 11:27
 */

namespace KodiCore\Response;


class Response
{
    const HTTP_CONTINUE                 = 100;
    const HTTP_OK                       = 200;
    const HTTP_UNAUTHORIZED             = 401;
    const HTTP_FORBIDDEN                = 403;
    const HTTP_NOT_FOUND                = 404;
    const HTTP_INTERNAL_SERVER_ERROR    = 500;
    const HTTP_SERVICE_UNAVAILABLE      = 503;

    public static $statusTexts = array(
        100 => 'Continue',
        200 => 'OK',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        500 => 'Internal Server Error',
        503 => 'Service Unavailable',
    );

    /**
     * @var array
     */
    private $headers;

    /**
     * @var mixed
     */
    private $content;

    /**
     * @var int
     */
    private $statusCode;


    /**
     * Response constructor.
     * @param string $content
     * @param int $status
     * @param array $headers
     */
    public function __construct($content = '', $status = 200, $headers = array())
    {
        $this->headers = $headers;
        $this->setContent($content);
        $this->setStatusCode($status);
    }

    /**
     *
     */
    private function applyHeaders() {
        foreach ($this->headers as $header) {
            header($header);
        }
    }

    /**
     * @param $header
     */
    public function addHeader($header) {
        $this->headers[] = $header;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param array $headers
     */
    public function setHeaders($headers)
    {
        $this->headers = $headers;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param mixed $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * @return mixed
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @param mixed $statusCode
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
    }

    function __toString()
    {
        $this->applyStatus();
        $this->applyHeaders();
        return $this->getContent();
    }

    private function applyStatus()
    {
        http_response_code($this->getStatusCode());
    }
}