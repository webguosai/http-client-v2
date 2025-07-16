<?php

namespace Webguosai\HttpClient;

use Webguosai\HttpClient\Consts\Consts;
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

        list($this->headers, $this->body) = explode("\r\n\r\n", $response, 2);
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
     * curl=0、且http状态=200表示成功
     * @return array
     */
    public function ok(): array
    {
        try {
            $this->throw();
        } catch (RequestException $e) {
            return [false, $e];
        }

        return [true, null];
    }

    public function throw()
    {
        /** curl **/
        $errorCode = $this->getCurlErrorCode();
        if ($errorCode !== 0) {
            throw new CurlException($errorCode, $this->getRequestArgs());
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
                throw new UnauthorizedException($statusCode, $this->getRequestArgs());
            }
            if ($statusCode === 404) {
                throw new NotFoundException($statusCode, $this->getRequestArgs());
            }
            if ($statusCode === 429) {
                throw new TooManyRequestsException($statusCode, $this->getRequestArgs());
            }
            throw new ClientException($statusCode, $this->getRequestArgs());
        } elseif ($level === 5) {
            // 5xx
            throw new ServerException($statusCode, $this->getRequestArgs());
        }

        // other
        throw new HttpException($statusCode, $this->getRequestArgs());
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