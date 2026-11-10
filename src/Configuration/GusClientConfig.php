<?php

declare(strict_types=1);

namespace NipFinder\Configuration;

use NipFinder\Exception\InvalidConfiguration;

final readonly class GusClientConfig
{
    public function __construct(
        public string $baseUrl,
        public string $endpoint = '/api/v1/gus',
        public string $identifierField = 'nip',
        public Authentication $authentication = new Authentication(),
    ) {
        $this->validateBaseUrl();
        $this->validateEndpoint();
        $this->validateIdentifierField();
    }

    public function url(): string
    {
        return rtrim($this->baseUrl, '/') . $this->endpoint;
    }

    private function validateBaseUrl(): void
    {
        $isValidUrl = filter_var($this->baseUrl, FILTER_VALIDATE_URL) !== false;
        $scheme = strtolower((string) parse_url($this->baseUrl, PHP_URL_SCHEME));
        $isHttpUrl = in_array($scheme, ['http', 'https'], true);

        if (!$isValidUrl || !$isHttpUrl) {
            throw InvalidConfiguration::because(
                'baseUrl must be an absolute HTTP(S) URL.',
            );
        }
    }

    private function validateEndpoint(): void
    {
        if (!str_starts_with($this->endpoint, '/')) {
            throw InvalidConfiguration::because(
                'endpoint must start with a slash.',
            );
        }
    }

    private function validateIdentifierField(): void
    {
        if (trim($this->identifierField) === '') {
            throw InvalidConfiguration::because(
                'identifierField cannot be empty.',
            );
        }
    }

}
