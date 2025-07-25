<?php

namespace Webguosai\HttpClient\Exception;

use Webguosai\HttpClient\Consts\Consts;
use Webguosai\HttpClient\Contract\ResponseInterface;

class HttpException extends RequestException
{
    public function __construct(int $statusCode, ResponseInterface $response)
    {
        $message = '响应的http状态错误：'. (Consts::CODE['http'][$statusCode] ?? '') . " [{$statusCode}]";

        parent::__construct($message, $response, $statusCode);
    }
}