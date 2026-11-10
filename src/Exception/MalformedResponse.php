<?php
declare(strict_types=1);
namespace NipFinder\Exception;
use RuntimeException;
use Throwable;
final class MalformedResponse extends RuntimeException implements NipFinderException
{
    public static function because(
        string $reason,
        ?Throwable $previous = null,
    ): self {
        return new self(
            'Malformed GUS API response: ' . $reason,
            previous: $previous,
        );
    }
}
