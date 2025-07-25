<?php

namespace Webguosai\HttpClient\Exception;

use Webguosai\HttpClient\Consts\Consts;
use Webguosai\HttpClient\Contract\ResponseInterface;

class CurlException extends RequestException
{
    public function __construct(int $errorCode, ResponseInterface $response)
    {
        $message = 'curl错误：'. (Consts::CODE['curl'][$errorCode] ?? '') . " [{$errorCode}]";

        parent::__construct($message, $response, 0, $errorCode);
    }
}