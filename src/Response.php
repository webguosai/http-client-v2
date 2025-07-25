<?php

namespace Webguosai\HttpClient;

use Webguosai\HttpClient\Exception\ClientException;
use Webguosai\HttpClient\Exception\CurlException;
use Webguosai\HttpClient\Exception\HttpException;
use Webguosai\HttpClient\Exception\NotFoundException;
use Webguosai\HttpClient\Exception\RequestException;
use Webguosai\HttpClient\Exception\ServerException;
use Webguosai\HttpClient\Exception\TooManyRequestsException;
use Webguosai\HttpClient\Exception\UnauthorizedException;
use function floor;

class Response
{
    protected $response;
    protected $info;
    protected $errorCode;
    protected $requestHeaders;
    protected $requestArgs;
    protected $headers;
    protected $body;
    protected $contentType;
    protected $statusCode;

    public function __construct(
        string $response,
        array  $info,
        int    $errorCode,
        array  $requestHeaders,
        array  $requestArgs)
    {
        $this->response       = $response;
        $this->info           = $info;
        $this->errorCode      = $errorCode;
        $this->contentType    = $info['content_type'] ?? '';
        $this->statusCode     = $info['http_code'] ?? 0;
        $this->requestHeaders = $requestHeaders;
        $this->requestArgs    = $requestArgs;

        $headerSize    = $info['header_size'] ?? 0;
        $this->headers = substr($response, 0, $headerSize);
        $this->body    = substr($response, $headerSize, strlen($response));
    }

    /**
     * body
     * @return mixed
     */
    public function getBody()
    {
        return $this->clearBom($this->body);
    }

    /**
     * http状态码
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * 获取curl错误码
     * @return int
     */
    public function getCurlErrorCode(): int
    {
        return $this->errorCode;
    }

    /**
     * 解析json返回数组
     * @return mixed
     */
    public function json()
    {
        return @json_decode($this->body, true);
    }

    /**
     * 解析xml返回数组
     * @return mixed
     */
    public function xml()
    {
        libxml_disable_entity_loader(true);
        $xml = simplexml_load_string($this->body, 'SimpleXMLElement', LIBXML_NOCDATA);
        return json_decode(json_encode($xml), TRUE);
    }

    /**
     * 请求头
     * @return array
     */
    public function getRequestHeaders(): array
    {
        return $this->requestHeaders;
    }

    /**
     * 获取请求前参数
     * @return array
     */
    public function getRequestArgs(): array
    {
        return $this->requestArgs;
    }

    /**
     * 响应头
     * @return array
     */
    public function getHeaders(): array
    {
        return array_filter(explode("\r\n", $this->headers));
    }

    /**
     * 获取curl信息
     * @return array
     */
    public function getInfo(): array
    {
        return $this->info;
    }

    /**
     * 获取响应内容
     * @return string
     */
    public function getResponse(): string
    {
        return $this->response;
    }

    /**
     * curl=0、且http状态=200表示成功
     * @param callable|null $callback 自定义异常处理
     * @return array
     */
    public function ok(?callable $callback = null): array
    {
        try {
            $this->throw();

            if (null !== $callback) {
                call_user_func($callback, $this);
            }

        } catch (RequestException $e) {
            return [false, $e];
        }

        return [true, null];
    }

    /**
     * 抛出异常
     * @return void
     */
    public function throw()
    {
        /** curl **/
        $errorCode = $this->getCurlErrorCode();
        if ($errorCode !== 0) {
            throw new CurlException($errorCode, $this->getRequestArgs(), $this->getResponse());
        }

        /** http **/
        $statusCode = $this->getStatusCode();
        if ($statusCode === 200) {
            return;
        }

        $level = (int)floor($statusCode / 100);
        if ($level === 4) {
            // 4xx
            if ($statusCode === 401) {
                throw new UnauthorizedException($statusCode, $this->getRequestArgs(), $this->getResponse());
            }
            if ($statusCode === 404) {
                throw new NotFoundException($statusCode, $this->getRequestArgs(), $this->getResponse());
            }
            if ($statusCode === 429) {
                throw new TooManyRequestsException($statusCode, $this->getRequestArgs(), $this->getResponse());
            }
            throw new ClientException($statusCode, $this->getRequestArgs(), $this->getResponse());
        } elseif ($level === 5) {
            // 5xx
            throw new ServerException($statusCode, $this->getRequestArgs(), $this->getResponse());
        }

        // other
        throw new HttpException($statusCode, $this->getRequestArgs(), $this->getResponse());
    }

    /**
     * 判断是否为图片
     * @return bool
     */
    public function isImg(): bool
    {
        // 从文档中判断
        if (stripos($this->contentType, 'image/') !== false) {
            return true;
        }

        //从内容的前两个字节判断
        $strInfo  = @unpack("C2chars", substr($this->body, 0, 2));
        $typeCode = intval($strInfo['chars1'] . $strInfo['chars2']);
        if ($typeCode == 255216 /*jpg*/ || $typeCode == 7173 /*gif*/ || $typeCode == 13780 /*png*/) {
            return true;
        }
        return false;
    }

    /**
     * 错误信息
     * @return string
     */
    // public function getErrorMsg(): string
    // {
    //     if ($this->errorCode !== 0) {
    //         $curlMsg = Consts::CODE['curl'][$this->errorCode];
    //         return "curl错误: {$curlMsg} [{$this->errorCode}]";
    //     }
    //
    //     if ($this->statusCode !== 200) {
    //         $httpStatusMsg = Consts::CODE['http'][$this->statusCode];
    //         return "响应的http状态错误: {$httpStatusMsg} [{$this->statusCode}]";
    //     }
    //
    //     return '';
    // }

    /**
     * 清除BOM头 (清除body头部中的utf-8签名)
     * @param mixed $html
     * @return mixed
     */
    protected function clearBom($html)
    {
        $bom = array(
            ord(substr($html, 0, 1)),
            ord(substr($html, 1, 1)),
            ord(substr($html, 2, 1))
        );

        if ($bom[0] == 239 && $bom[1] == 187 && $bom[2] == 191) {
            $html = substr($html, 3);
        }

        return $html;
    }
}