<?php

declare(strict_types=1);

namespace NipFinder\Http;

use NipFinder\Configuration\GusClientConfig;
use NipFinder\ValueObject\Nip;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;

final readonly class GusRequestFactory
{
    public function __construct(
        private RequestFactoryInterface $requestFactory,
        private GusClientConfig $config,
    ) {}

    public function createForNip(Nip $nip): RequestInterface
    {
        $request = $this->requestFactory
            ->createRequest('GET', $this->buildUrl($nip))
            ->withHeader('Accept', 'application/json');

        foreach ($this->config->authentication->headers() as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        return $request;
    }

    private function buildUrl(Nip $nip): string
    {
        $query = http_build_query(
            [$this->config->identifierField => $nip->value],
            encoding_type: PHP_QUERY_RFC3986,
        );

        $separator = str_contains($this->config->url(), '?') ? '&' : '?';

        return $this->config->url() . $separator . $query;
    }
}
