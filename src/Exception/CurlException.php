<?php

namespace Webguosai\HttpClient\Exception;

class CurlException extends RequestException
{
    public function __construct(int $errorCode = 0, array $requestArgs = [], string $response = '')
    {
        parent::__construct($errorCode, $requestArgs, $response, 'curl');
    }
}