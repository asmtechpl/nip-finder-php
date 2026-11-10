<?php
declare(strict_types=1);
namespace NipFinder\Exception;
use RuntimeException;
final class ApiError extends RuntimeException implements NipFinderException
{
    public function __construct(
        public readonly int $statusCode,
        public readonly string $responseBody,
    ) {
        parent::__construct(
            sprintf('GUS API returned HTTP %d.', $statusCode),
            $statusCode,
        );
    }
}
