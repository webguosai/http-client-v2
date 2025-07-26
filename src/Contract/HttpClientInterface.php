<?php

namespace Webguosai\HttpClient\Contract;

use Webguosai\HttpClient\Response;

/**
 * Http客户端
 * @method ResponseInterface get(string $url, array $query = [], string|array $headers = [])
 * @method ResponseInterface post(string $url, string|array $data = [], string|array $headers = [])
 * @method ResponseInterface put(string $url, string|array $data = [], string|array $headers = [])
 * @method ResponseInterface patch(string $url, string|array $data = [], string|array $headers = [])
 * @method ResponseInterface delete(string $url, string|array $data = [], string|array $headers = [])
 * @method ResponseInterface head(string $url, string|array $data = [], string|array $headers = [])
 * @method ResponseInterface options(string $url, string|array $data = [], string|array $headers = [])
 * @method self timeout(int|float $timeout)
 * @method self redirect(int $redirect)
 * @method self proxy(string $proxy)
 */
interface HttpClientInterface
{
    public function request(string $url, string $method = 'GET', $data = [], $headers = []): Response;
}