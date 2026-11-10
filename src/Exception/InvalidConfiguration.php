<?php
declare(strict_types=1);
namespace NipFinder\Exception;
use InvalidArgumentException;
final class InvalidConfiguration extends InvalidArgumentException implements NipFinderException
{
    public static function because(string $reason): self
    {
        return new self('Invalid GUS client configuration: ' . $reason);
    }
}
