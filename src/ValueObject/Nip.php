<?php
declare(strict_types=1);
namespace NipFinder\ValueObject;
use NipFinder\Exception\InvalidNip;
use Stringable;
final readonly class Nip implements Stringable
{
    private const LENGTH = 10;

    private const CHECKSUM_WEIGHTS = [6, 5, 7, 2, 3, 4, 5, 6, 7];

    public string $value;

    public function __construct(string $value)
    {
        $normalized = self::normalize($value);

        if (!self::hasValidFormat($normalized)) {
            throw InvalidNip::forValue($value);
        }

        if (!self::hasValidChecksum($normalized)) {
            throw InvalidNip::forValue($value);
        }

        $this->value = $normalized;
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function __toString(): string
    {
        return $this->value;
    }

    private static function normalize(string $value): string
    {
        return str_replace([' ', '-'], '', $value);
    }

    private static function hasValidFormat(string $value): bool
    {
        return strlen($value) === self::LENGTH && ctype_digit($value);
    }

    private static function hasValidChecksum(string $value): bool
    {
        $checksum = 0;

        foreach (self::CHECKSUM_WEIGHTS as $position => $weight) {
            $checksum += (int) $value[$position] * $weight;
        }

        return $checksum % 11 === (int) $value[self::LENGTH - 1];
    }
}
