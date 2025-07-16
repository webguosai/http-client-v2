<?php

namespace Webguosai\HttpClient\Contract;

use Webguosai\HttpClient\Response;

/**
 * Http客户端
 * @method Response get(string $url, array $query, string|array $headers)
 * @method Response post(string $url, string|array $data, string|array $headers)
 * @method Response put(string $url, string|array $data, string|array $headers)
 * @method Response patch(string $url, string|array $data, string|array $headers)
 * @method Response delete(string $url, string|array $data, string|array $headers)
 * @method Response head(string $url, string|array $data, string|array $headers)
 * @method Response options(string $url, string|array $data, string|array $headers)
 */
interface HttpClientInterface
{
    public function request(string $url, string $method = 'GET', $data = [], $headers = []): Response;
}