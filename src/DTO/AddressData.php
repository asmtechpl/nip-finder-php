<?php
declare(strict_types=1);
namespace NipFinder\DTO;
final readonly class AddressData
{
    public function __construct(
        public ?string $voivodeship,
        public ?string $district,
        public ?string $city,
        public ?string $municipality,
        public ?string $postalCode,
        public ?string $street,
        public ?string $houseNumber,
        public ?string $apartmentNumber,
    ) {}

    public function streetLine(): ?string
    {
        $number = $this->houseNumber;

        if ($number !== null && $this->apartmentNumber !== null) {
            $number .= '/' . $this->apartmentNumber;
        }

        $parts = array_filter(
            [$this->street, $number],
            static fn (?string $part): bool => $part !== null && $part !== '',
        );
        $line = trim(implode(' ', $parts));

        return $line === '' ? null : $line;
    }
}
