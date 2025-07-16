<?php

namespace Webguosai\HttpClient\Exception;

class HttpException extends RequestException
{
    protected $errorType = 'http';
    public function __construct(int $statusCode = 0, array $requestArgs = [])
    {
        parent::__construct($statusCode, $requestArgs);
    }
}