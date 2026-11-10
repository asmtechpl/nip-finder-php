<?php

declare(strict_types=1);

namespace NipFinder\Configuration;

use NipFinder\Exception\InvalidConfiguration;

final readonly class Authentication
{
    /** @param array<string, string> $additionalHeaders */
    public function __construct(
        public ?string $token = null,
        public ?string $apiKey = null,
        public string $tokenType = 'Bearer',
        public array $additionalHeaders = [],
    ) {
        $this->validateTokenType();
        $this->validateAdditionalHeaders();
    }

    /** @return array<string, string> */
    public function headers(): array
    {
        $headers = $this->additionalHeaders;

        if ($this->hasToken()) {
            $headers['Authorization'] = sprintf(
                '%s %s',
                $this->tokenType,
                $this->token,
            );
        }

        if ($this->hasApiKey()) {
            $headers['X-API-KEY'] = $this->apiKey;
        }

        return $headers;
    }

    private function hasToken(): bool
    {
        return $this->token !== null && $this->token !== '';
    }

    private function hasApiKey(): bool
    {
        return $this->apiKey !== null && $this->apiKey !== '';
    }

    private function validateTokenType(): void
    {
        if ($this->hasToken() && trim($this->tokenType) === '') {
            throw InvalidConfiguration::because(
                'tokenType cannot be empty when token is configured.',
            );
        }
    }

    private function validateAdditionalHeaders(): void
    {
        foreach ($this->additionalHeaders as $name => $value) {
            if (!is_string($name) || trim($name) === '' || !is_string($value)) {
                throw InvalidConfiguration::because(
                    'additionalHeaders must contain string names and values.',
                );
            }
        }
    }
}
