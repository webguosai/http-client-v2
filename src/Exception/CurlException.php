<?php

namespace Webguosai\HttpClient\Exception;

use Webguosai\HttpClient\Consts\Consts;

class CurlException extends RequestException
{
    public function __construct(int $errorCode = 0, array $requestArgs = [], string $response = '')
    {
        $message = 'curl错误：'. (Consts::CODE['curl'][$errorCode] ?? '') . " [{$errorCode}]";

        parent::__construct($message, $requestArgs, $response, 0, $errorCode);
    }
}