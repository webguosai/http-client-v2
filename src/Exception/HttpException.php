<?php

namespace Webguosai\HttpClient\Exception;

use Webguosai\HttpClient\Consts\Consts;

class HttpException extends RequestException
{
    public function __construct(int $statusCode = 200, array $requestArgs = [], string $response = '')
    {
        $message = '响应的http状态错误：'. (Consts::CODE['http'][$statusCode] ?? '') . " [{$statusCode}]";

        parent::__construct($message, $requestArgs, $response, $statusCode);
    }
}