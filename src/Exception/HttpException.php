<?php

namespace Webguosai\HttpClient\Exception;

class HttpException extends RequestException
{
    public function __construct(int $statusCode = 200, array $requestArgs = [], string $response = '')
    {
        parent::__construct($statusCode, $requestArgs, $response, 'http');
    }
}