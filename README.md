<h1 align="center">http client v2</h1>

<p align="center">
<a href="https://packagist.org/packages/webguosai/http-client-v2"><img src="https://poser.pugx.org/webguosai/http-client-v2/v/stable" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/webguosai/http-client-v2"><img src="https://poser.pugx.org/webguosai/http-client-v2/downloads" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/webguosai/http-client-v2"><img src="https://poser.pugx.org/webguosai/http-client-v2/v/unstable" alt="Latest Unstable Version"></a>
<a href="https://packagist.org/packages/webguosai/http-client-v2"><img src="https://poser.pugx.org/webguosai/http-client-v2/license" alt="License"></a>
</p>

## 运行环境

- php >= 7.2
- composer

## 安装

```Shell
composer require webguosai/http-client-v2 -vvv
```

## 使用
### 初始化
```php
$options = [
    //超时(单位秒)
    'timeout'     => 3,

    //代理ip
    'proxySocks5'   => true, // 使用 socks5 代理
    'proxy'         => '0.0.0.0:8888', // 代理ip，如：0.0.0.0:8888

    //重定向、及最多重定向跳转次数
    'redirects'   => false,
    'maxRedirect' => 5,
    
    //cookie自动保存路径
    'cookieJarFile' => 'cookie.txt',
];

// 实例化
$http = new \Webguosai\HttpClient\HttpClient($options);

// 工厂模式
$http = \Webguosai\HttpClient\HttpClient::factory([
    'timeout' => 3,
])
```

### 请求
```php
$url = 'https://httpbin.org';
$data = ['data' => '111', 'data2' => '222'];
$headers = [
    'User-Agent' => 'http-client browser',
    'cookie' => 'login=true'
];

//所有方法
$response = $http->request($url, $method, $data, $headers);

$response = $http->get($url, $query, $headers);
$response = $http->post($url, $data, $headers);
$response = $http->put($url, $data, $headers);
$response = $http->patch($url, $data, $headers);
$response = $http->delete($url, $data, $headers);
$response = $http->head($url, $data, $headers);
$response = $http->options($url, $data, $headers);
```

### 响应
```php
[$status, $throw] = $response->ok(); //
$response->getRequestHeaders(); // 请求头
$response->getHeaders(); // 响应头
$response->getBody(); // body
$response->getStatusCode(); // http状态码
$response->getInfo(); // 其它信息
$response->json(); // json
$response->xml(); // xml
$response->ok();// curl_code = 0 && http_code = 200 返回真
$response->getCurlErrorCode(); // 错误信息
```

### data 传值方式
```php
// multipart/form-data
$data = ['data' => '111', 'data2' => '222'];

// application/x-www-form-urlencoded
$data = http_build_query($data); 

// application/json
$data = json_encode($data); 

// 文件上传 $_FILES['file'] 接收
$data = [
    'file' => new \CURLFile('1.jpg'),
];

$response = $http->post($url, $data);
```

### headers 传值方式
```php
// 数组传递 
$headers = [
    'User-Agent: chrome',
    'User-Agent' => 'chrome',
];

// 纯字符串 (一般为从浏览器复制)
$headers = 'User-Agent: chrome
Referer: https://www.x.com
Cookie: cookie=6666666';

$response = $http->post($url, $data, $headers);
```


## 实操
```php
$http = \Webguosai\HttpClient\HttpClient::factory([
    'timeout' => 3,
])
$response = $http->get('http://www.baidu.com');

/** @var $throw \Webguosai\HttpClient\Exception\RequestException **/
[$status, $throw] = $response->ok();
if ($status) {
    var_dump($response->getBody());
    var_dump($response->json());
} else {
    var_dump($throw->getMessage());
    var_dump($throw->getErrorType());
    var_dump($throw->getCode());
}
```

## 异常

```php
try {
    $response->throw();
    var_dump($response->json());
} catch (\Webguosai\HttpClient\Exception\RequestException $e) {
    var_dump($e->getMessage());
    // var_dump($e->getErrorType());
    var_dump($e->getCode());
    var_dump($e->getRequestArgs());
}
```

## License

MIT
