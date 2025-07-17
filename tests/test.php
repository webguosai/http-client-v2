<?php

require_once '../vendor/autoload.php';

/** get **/
// $url    = 'https://httpbin.org/get?a=aaa&b=bbb';
// $method = 'GET';
// $data   = ['a' => 111];

/** post **/
$url    = 'https://httpbin.org/post';
$method = 'POST';
$data   = json_encode(['a' => 111]);

/** patch **/
// $url    = 'https://httpbin.org/patch';
// $method = 'PATCH';
// $data   = json_encode(['a' => 111]);

$http = (new \Webguosai\HttpClient\HttpClient([
    'timeout' => 5,
    // 'proxy' => '127.0.0.1:9527'
]));

// $http     = \Webguosai\HttpClient\HttpClient::factory([
//     'timeout' => 1,
//     // 'proxy' => '127.0.0.1:9527'
// ]);

$response = $http->request(
    $url,
    $method,
    $data,
    [
        'User-Agent: Apifox/1.0.0 (https://apifox.com)',
        'Authorization: Bearer 111'
    ]
);

/** @var $throw \Webguosai\HttpClient\Exception\RequestException **/
[$status, $throw] = $response->ok();
if ($status) {
    dump($response->getBody());
    dump($response->json());
    dump($response->getHeaders());
    dump($response->getRequestHeaders());
    dump($response->getInfo());
} else {
    dump($throw->getMessage());
    // dump($throw->getErrorType());
    // dump($throw->getCode());
    // dump($throw->getRequestArgs());
}

// try {
//     $response->throw();
//     dump($response->json());
//     dump($response->getInfo());
// } catch (\Webguosai\HttpClient\Exception\RequestException $e) {
//     dump($e->getMessage());
//     // dump($e->getErrorType());
//     dump($e->getCode());
//     dump($e->getRequestArgs());
// }

// dump($response->ok()); //
// dump($response->getRequestHeaders()); // 请求头
// dump($response->getHeaders()); // 响应头
// dump($response->getBody()); // body
// dump($response->getStatusCode()); // http状态码
// dump($response->getInfo()); // 其它信息
// dump($response->json()); // json
// dump($response->xml()); // xml
// dump($response->ok());// http状态码=200返回真
// dump($response->getCurlErrorCode()); // 错误信息
