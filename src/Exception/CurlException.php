<?php

namespace Webguosai\HttpClient\Exception;

class CurlException extends RequestException
{
    protected $errorType = 'curl';

    public function __construct(int $errorCode, array $requestArgs = [], string $response = '')
    {
        parent::__construct($errorCode, $requestArgs, $response);
    }
}