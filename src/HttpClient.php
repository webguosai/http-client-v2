<?php

namespace Webguosai\HttpClient;

use Exception;
use Webguosai\HttpClient\Contract\HttpClientInterface;
use Webguosai\HttpClient\Contract\ResponseInterface;

class HttpClient implements HttpClientInterface
{
    /** 请求 **/
    protected $requestHeaders = [];

    /** 配置 **/
    public $options = [
        // 超时
        'timeout'       => 3,

        // 代理
        'proxySocks5'   => false, // 是否使用 socks5
        'proxy'         => '', // 代理ip，如：0.0.0.0:8888

        // 允许重定向及重定向次数
        'redirects'     => false,
        'maxRedirect'   => 5,

        // 保存cookie的文件路径
        'cookieJarFile' => '',
    ];

    public function __construct(array $options = [])
    {
        $this->options = array_merge($this->options, $options);
    }

    /**
     * 魔术方法
     * @throws Exception
     */
    public function __call($name, $args): ResponseInterface
    {
        $name = strtoupper($name);
        if (in_array($name, ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'])) {
            $url     = $args[0];
            $method  = $name;
            $data    = empty($args[1]) ? [] : $args[1];
            $headers = empty($args[2]) ? [] : $args[2];

            return $this->request($url, $method, $data, $headers);
        }

        throw new Exception('找不到方法：' . $name);
    }

    /**
     * 工厂方法
     * @param array $options
     * @return self
     */
    public static function factory(array $options = []): self
    {
        return new self($options);
    }

    /**
     * 设置超时
     * @param int|float $timeout 超时时间(秒,-1表示不超时)
     * @return $this
     */
    public function timeout($timeout): self
    {
        $this->options['timeout'] = $timeout;
        return $this;
    }

    /**
     * 请求
     * @param string $url
     * @param string $method
     * @param array|string $data
     * @param array|string $headers
     * @return Response
     */
    public function request(string $url, string $method = 'GET', $data = [], $headers = []): Response
    {
        // 请求方式(大写)
        $method = strtoupper($method);

        // 清空请求头
        $this->requestHeaders = [];

        // 根据 data 来设置对应的content-type请求头
        $this->setContentTypeByData($data);

        // 处理 url 和 data
        [$url, $data] = $this->handleUrlData($url, $method, $data);

        $ch = curl_init();

        // url
        curl_setopt($ch, CURLOPT_URL, $url);
        // 超时 (最终都会转换为毫秒)
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, $this->options['timeout'] * 1000);

        // 代理
        if (!empty($this->options['proxy'])) {
            if ($this->options['proxySocks5']) {
                // SOCKS5
                curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
            }
            curl_setopt($ch, CURLOPT_PROXY, $this->options['proxy']);
        }

        // 重定向
        if ($this->options['redirects']) {
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

            // 最多重定向次数
            curl_setopt($ch, CURLOPT_MAXREDIRS, $this->options['maxRedirect']);
        }

        // cookie
        if ($this->options['cookieJarFile']) {
            curl_setopt($ch, CURLOPT_COOKIEJAR, $this->options['cookieJarFile']); // 存放Cookie信息的文件名称
            curl_setopt($ch, CURLOPT_COOKIEFILE, $this->options['cookieJarFile']); // 读取上面所储存的Cookie信息
        }

        // headers
        $this->mergeRequestHeaders($headers);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->requestHeaders);

        // 设置请求方式
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        if ($method == 'HEAD') {
            curl_setopt($ch, CURLOPT_NOBODY, true);
        }

        // post
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }

        // https
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // 不直接输出响应数据
        // curl_setopt($ch, CURLOPT_ENCODING, ''); // 编码
        curl_setopt($ch, CURLOPT_HEADER, true); // 获取header

        // 发送请求
        $response = curl_exec($ch);

        // 响应的curl错误代码、http状态、文档类型、info
        $errorCode = curl_errno($ch);
        $info      = curl_getinfo($ch);

        curl_close($ch);

        return new Response($response, $info, $errorCode, $this->requestHeaders, func_get_args());
    }

    /**
     * 根据 data 判断使用哪种 content-type
     * @param mixed $data
     * @return void
     */
    protected function setContentTypeByData($data): void
    {
        // 根据data类型来设置请求的content-type
        if (is_string($data)) {
            if (!is_null(json_decode($data))) {
                $this->setContentType('application/json');
            } else {
                $this->setContentType('application/x-www-form-urlencoded');
            }
        } elseif (is_array($data)) {
            $this->setContentType('multipart/form-data');
        } else {
            $this->setContentType('text/plain');
        }
    }

    /**
     * 处理 url 和 data
     * @param string $url
     * @param string $method
     * @param $data
     * @return array
     */
    protected function handleUrlData(string $url, string $method, $data): array
    {
        if ($method === 'GET') {
            /** 拼接 GET 请求中的 query 参数到url中 **/
            if (!empty($data)) {
                if (is_string($data)) {
                    parse_str($data, $data);
                }

                if (strpos($url, '?') === false) {
                    $url .= '?' . http_build_query($data);
                } else {
                    $url .= '&' . http_build_query($data);
                }
            }

            $data = [];
        }

        return [$url, $data];
    }

    /**
     * 设置content-type
     * @param string $contentType application/json
     * @return $this
     */
    protected function setContentType(string $contentType): self
    {
        return $this->setRequestHeaders([
            'Content-type: ' . $contentType,
        ]);
    }

    /**
     * 合并 请求头
     * @param array|string $headers
     * @return void
     */
    protected function mergeRequestHeaders($headers)
    {
        $parseHeaders = $this->parseHeaders($headers);

        $this->setRequestHeaders($parseHeaders);
    }

    /**
     * 设置请求头
     * @param $headers ['cookie: xxx', 'Authorization: Bearer xxx']
     * @return $this
     */
    protected function setRequestHeaders($headers): self
    {
        foreach ($headers as $header) {
            $this->requestHeaders[] = $header;
        }

        return $this;
    }

    /**
     * 解析请求头
     * @param array|string $headers
     * @return array
     */
    protected function parseHeaders($headers): array
    {
        $parseHeaders = [];

        // 字符串先转换为数组(一般由浏览器复制而来)
        if (is_string($headers)) {
            $headers = array_map(function ($data) {
                return trim($data);
            }, explode("\n", $headers));
        }

        // 数组，这里分两种情况
        // 将所有header转换为curl能识别的格式
        foreach ($headers as $key => $value) {
            if (is_string($key)) {
                $parseHeaders[] = $key . ':' . $value;
            } else {
                $parseHeaders[] = $value;
            }
        }

        return $parseHeaders;
    }
}
