<?php

namespace Webguosai\HttpClient\Exception;

use RuntimeException;
use Webguosai\HttpClient\Consts\Consts;

/**
 * 所有异常会继承此类，这是一个基类
 */
class RequestException extends RuntimeException
{
    protected $curlErrorCode;
    protected $httpStatusCode;
    protected $requestArgs;
    protected $response;

    public function __construct(string $message, array $requestArgs = [], string $response = '', int $httpStatusCode = 200, int $curlErrorCode = 0)
    {
        $this->requestArgs    = $requestArgs;
        $this->response       = $response;
        $this->curlErrorCode  = $curlErrorCode;
        $this->httpStatusCode = $httpStatusCode;

        parent::__construct($message);
    }

    /**
     * 获取请求时的传参列表
     * @return array
     */
    public function getRequestArgs(): array
    {
        return $this->requestArgs;
    }

    /**
     * 获取上下文 信息
     * @return array
     */
    public function getContext(): array
    {
        return [
            // 传参
            'args'          => $this->getRequestArgs(),
            // 信息(错误信息)
            'error_message' => $this->getMessage(),
            // 响应内容(包含header头和body)
            'response'      => $this->response,
        ];
    }

    /**
     * 获取curl错误码
     * @return int
     */
    public function getCurlErrorCode(): int
    {
        return $this->curlErrorCode;
    }

    /**
     * 获取http状态码
     * @return int
     */
    public function getHttpStatusCode(): int
    {
        return $this->httpStatusCode;
    }

    /**
     * 错误类型
     * @return string
     */
    public function getErrorType(): string
    {
        $type = '';
        if ($this->curlErrorCode !== 0) {
            $type = 'curl';
        } else if ($this->httpStatusCode !== 200) {
            $type = 'http';
        }

        return $type;
    }

}