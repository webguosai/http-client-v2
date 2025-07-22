<?php

namespace Webguosai\HttpClient\Exception;

use RuntimeException;
use Webguosai\HttpClient\Consts\Consts;

/**
 * 所有异常会继承此类，这是一个基类
 */
class RequestException extends RuntimeException
{
    protected $errorType;
    protected $requestArgs;
    protected $response;

    public function __construct(int $code = 0, array $requestArgs = [], string $response = '', string $errorType = '')
    {
        $this->requestArgs = $requestArgs;
        $this->response    = $response;
        $this->errorType   = $errorType;
        parent::__construct($this->getErrorMsg($code), $code);
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
     * 获取错误类型
     * @return string
     */
    public function getErrorType(): string
    {
        return $this->errorType;
    }

    /**
     * 获取错误内容
     * @param int $code
     * @return string
     */
    protected function getErrorMsg(int $code): string
    {
        $errorType = $this->getErrorType();
        $text = '';
        if ($errorType === 'curl' && $code !== 0) {
            $text = 'curl错误：';
        } else if ($errorType === 'http' && $code !== 200) {
            $text = '响应的http状态错误：';
        }

        $message = Consts::CODE[$errorType][$code] ?? '';

        return $text . "{$message} [{$code}]";
    }
}