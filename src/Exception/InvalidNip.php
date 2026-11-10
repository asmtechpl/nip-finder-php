<?php
declare(strict_types=1);
namespace NipFinder\Exception;
use InvalidArgumentException;
final class InvalidNip extends InvalidArgumentException implements NipFinderException
{
    public static function forValue(string $value): self
    {
        return new self(sprintf('"%s" is not a valid Polish NIP.', $value));
    }
}
