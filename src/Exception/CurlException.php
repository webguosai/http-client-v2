<?php

namespace Webguosai\HttpClient\Exception;

class CurlException extends RequestException
{
    protected $errorType = 'curl';
    public function __construct(int $errorCode, array $requestArgs = [])
    {
        parent::__construct($errorCode, $requestArgs);
    }
}