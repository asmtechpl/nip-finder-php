<?php
declare(strict_types=1);
namespace NipFinder\Tests\ValueObject;
use NipFinder\Exception\InvalidNip;
use NipFinder\ValueObject\Nip;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
final class NipTest extends TestCase
{
    public function testItNormalizesAValidNip(): void { self::assertSame('1234563218', (string) new Nip('123-456-32-18')); }
    #[DataProvider('invalidValues')]
    public function testItRejectsInvalidValues(string $value): void { $this->expectException(InvalidNip::class); new Nip($value); }
    /** @return iterable<string, array{string}> */
    public static function invalidValues(): iterable { yield 'empty' => ['']; yield 'too short' => ['123']; yield 'bad checksum' => ['1234567890']; yield 'letters' => ['abcdefghij']; }
}
