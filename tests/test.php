<?php

require_once '../vendor/autoload.php';

/** get **/
$url    = 'https://httpbin.org/get?a=aaa&b=bbb';
$method = 'GET';
$data   = ['a' => 111];

/** post **/
// $url = 'https://httpbin.org/post';
// // $url    = 'https://google.com';
// $method = 'POST';
// $data   = json_encode(['a' => 111]);

/** patch **/
// $url    = 'https://httpbin.org/patch';
// $method = 'PATCH';
// $data   = json_encode(['a' => 111]);

// $http = (new \Webguosai\HttpClient\HttpClient([
//     'timeout' => 5.0,
//     // 'proxy' => '127.0.0.1:9527'
// ]));

$http = \Webguosai\HttpClient\HttpClient::factory([
    'timeout' => 1.0,
    // 'proxy' => '127.0.0.1:9527'
])->timeout(2.5);

$response = $http->request(
    $url,
    $method,
    $data,
    [
        'User-Agent: Apifox/1.0.0 (https://apifox.com)',
        'Authorization: Bearer 111'
    ]
);
// $response = $http->get($url, [], [
//     'User-Agent: Apifox/1.0.0 (https://apifox.com)',
//     'Authorization: Bearer 111'
// ]);

/** @var $throw \Webguosai\HttpClient\Exception\RequestException * */
[$status, $throw] = $response->ok(function (\Webguosai\HttpClient\Contract\ResponseInterface $response) {
    if ($response->getBody() !== 'hello world') {
        // throw new \Webguosai\HttpClient\Exception\RequestException('自定义异常错误', $response);
    }
});
if ($status) {
    // dump($response->getBody());
    // dump($response->json());
    // dump($response->getHeaders());
    // dump($response->getRequestHeaders());
    // dump($response->getInfo());
    dump($response->getResponse());
} else {
    dump($throw->getMessage());
    // dump($throw->getRequestArgs());
    dump($throw->getContext());
    // dump($throw->getErrorType());
    // dump($throw->getHttpStatusCode());
    // dump($throw->getCurlErrorCode());
}

// try {
//     $response->throw();
//     // dump($response->json());
//     // dump($response->getInfo());
//
//     // 自定义错误
//     if ($response->getBody() !== 'hello world') {
//         throw new \Webguosai\HttpClient\Exception\RequestException('自定义错误', $response->getRequestArgs(), $response->getResponse());
//     }
// } catch (\Webguosai\HttpClient\Exception\RequestException $e) {
//     dump($e->getMessage());
//     dump($e->getRequestArgs());
//     dump($e->getContext());
//     dump($e->getErrorType());
//     dump($e->getHttpStatusCode());
//     dump($e->getCurlErrorCode());
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
