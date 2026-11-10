<?php

declare(strict_types=1);

namespace NipFinder;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use NipFinder\Configuration\Authentication;
use NipFinder\Configuration\GusClientConfig;
use NipFinder\Environment\Environment;
use NipFinder\Exception\InvalidConfiguration;
use NipFinder\Http\GusRequestFactory;

final class GusLookupClientFactory
{
    private const DEFAULT_ENDPOINT = '/api/v1/gus';

    private const DEFAULT_IDENTIFIER_FIELD = 'nip';

    private const DEFAULT_TIMEOUT = 10.0;

    private const DEFAULT_TOKEN_TYPE = 'Bearer';

    public static function fromEnvironment(
        ?Environment $environment = null,
    ): GusLookupClient {
        $environment ??= new Environment();

        return self::fromArray([
            'base_url' => self::required($environment, 'GUS_API_BASE_URL'),
            'endpoint' => $environment->getOrDefault(
                'GUS_API_ENDPOINT',
                self::DEFAULT_ENDPOINT,
            ),
            'identifier_field' => $environment->getOrDefault(
                'GUS_API_IDENTIFIER_FIELD',
                self::DEFAULT_IDENTIFIER_FIELD,
            ),
            'token' => $environment->get('GUS_API_TOKEN'),
            'api_key' => $environment->get('GUS_API_KEY'),
            'token_type' => $environment->getOrDefault(
                'GUS_API_TOKEN_TYPE',
                self::DEFAULT_TOKEN_TYPE,
            ),
            'timeout' => $environment->getOrDefault(
                'GUS_API_TIMEOUT',
                (string) self::DEFAULT_TIMEOUT,
            ),
        ]);
    }

    /** @param array<string, mixed> $values */
    public static function fromArray(array $values): GusLookupClient
    {
        $config = new GusClientConfig(
            baseUrl: self::requiredString($values, 'base_url'),
            endpoint: self::stringOrDefault(
                $values,
                'endpoint',
                self::DEFAULT_ENDPOINT,
            ),
            identifierField: self::stringOrDefault(
                $values,
                'identifier_field',
                self::DEFAULT_IDENTIFIER_FIELD,
            ),
            authentication: new Authentication(
                token: self::nullableString($values, 'token'),
                apiKey: self::nullableString($values, 'api_key'),
                tokenType: self::stringOrDefault(
                    $values,
                    'token_type',
                    self::DEFAULT_TOKEN_TYPE,
                ),
            ),
        );

        $timeout = self::timeout($values['timeout'] ?? self::DEFAULT_TIMEOUT);
        $requestFactory = new GusRequestFactory(new HttpFactory(), $config);

        return new GusLookupClient(
            httpClient: new Client(['timeout' => $timeout]),
            requestFactory: $requestFactory,
        );
    }

    private static function required(
        Environment $environment,
        string $name,
    ): string {
        $value = $environment->get($name);

        if ($value === null) {
            throw InvalidConfiguration::because(
                sprintf('environment variable %s is required.', $name),
            );
        }

        return $value;
    }

    private static function timeout(mixed $value): float
    {
        if (!is_numeric($value) || (float) $value <= 0) {
            throw InvalidConfiguration::because(
                'GUS_API_TIMEOUT must be a positive number.',
            );
        }

        return (float) $value;
    }

    /** @param array<string, mixed> $values */
    private static function requiredString(array $values, string $key): string
    {
        $value = self::nullableString($values, $key);

        if ($value === null) {
            throw InvalidConfiguration::because(
                sprintf('configuration value %s is required.', $key),
            );
        }

        return $value;
    }

    /** @param array<string, mixed> $values */
    private static function nullableString(array $values, string $key): ?string
    {
        $value = $values[$key] ?? null;

        if ($value === null || $value === '') {
            return null;
        }

        if (!is_string($value)) {
            throw InvalidConfiguration::because(
                sprintf('configuration value %s must be a string.', $key),
            );
        }

        return $value;
    }

    /** @param array<string, mixed> $values */
    private static function stringOrDefault(
        array $values,
        string $key,
        string $default,
    ): string {
        return self::nullableString($values, $key) ?? $default;
    }
}
