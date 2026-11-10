<?php

declare(strict_types=1);

return [
    'base_url' => env('GUS_API_BASE_URL'),
    'token' => env('GUS_API_TOKEN'),
    'api_key' => env('GUS_API_KEY'),
    'token_type' => env('GUS_API_TOKEN_TYPE', 'Bearer'),
    'endpoint' => env('GUS_API_ENDPOINT', '/api/v1/gus'),
    'timeout' => env('GUS_API_TIMEOUT', 10),
    'identifier_field' => env('GUS_API_IDENTIFIER_FIELD', 'nip'),
];
