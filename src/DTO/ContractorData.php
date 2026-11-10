<?php
declare(strict_types=1);
namespace NipFinder\DTO;
final readonly class ContractorData
{
    public function __construct(
        public string $nip,
        public ?string $regon,
        public string $name,
        public AddressData $address,
    ) {}
}
