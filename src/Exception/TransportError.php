<?php
declare(strict_types=1);
namespace NipFinder\Exception;
use Psr\Http\Client\ClientExceptionInterface;
use RuntimeException;
final class TransportError extends RuntimeException implements NipFinderException
{
    public static function fromClientException(
        ClientExceptionInterface $exception,
    ): self {
        return new self(
            'Could not communicate with the GUS API.',
            previous: $exception,
        );
    }
}
