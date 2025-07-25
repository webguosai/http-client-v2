<?php

namespace Webguosai\HttpClient\Contract;

interface ResponseInterface
{
    public function getBody();

    public function getStatusCode(): int;

    public function getCurlErrorCode(): int;

    public function json();

    public function xml();

    public function getRequestHeaders(): array;

    public function getRequestArgs(): array;

    public function getHeaders(): array;

    public function getInfo(): array;

    public function getResponse(): string;

    public function ok(?callable $callback = null): array;

    public function throw();

    public function isImg(): bool;
}