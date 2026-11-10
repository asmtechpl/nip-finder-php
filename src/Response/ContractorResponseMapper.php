<?php
declare(strict_types=1);
namespace NipFinder\Response;
use NipFinder\DTO\AddressData;
use NipFinder\DTO\ContractorData;
use NipFinder\Exception\MalformedResponse;
final class ContractorResponseMapper
{
    /** @param array<string, mixed> $payload */
    public function map(array $payload): ContractorData
    {
        $data = $this->unwrapData($payload);

        return new ContractorData(
            nip: $this->requiredString($data, 'nip'),
            regon: $this->nullableString($data, 'regon'),
            name: $this->requiredString($data, 'name'),
            address: new AddressData(
                voivodeship: $this->nullableString($data, 'voivodeship'),
                district: $this->nullableString($data, 'district'),
                city: $this->nullableString($data, 'city'),
                municipality: $this->nullableString($data, 'municipality'),
                postalCode: $this->nullableString($data, 'postalCode'),
                street: $this->nullableString($data, 'street'),
                houseNumber: $this->nullableString($data, 'houseNumber'),
                apartmentNumber: $this->nullableString($data, 'apartmentNumber'),
            ),
        );
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function unwrapData(array $payload): array
    {
        if (!array_key_exists('data', $payload)) {
            return $payload;
        }

        if (!is_array($payload['data']) || array_is_list($payload['data'])) {
            throw MalformedResponse::because(
                'field "data" must be an object.',
            );
        }

        return $payload['data'];
    }

    /** @param array<string, mixed> $payload */
    private function requiredString(array $payload, string $field): string
    {
        $value = $this->nullableString($payload, $field);

        if ($value === null || $value === '') {
            throw MalformedResponse::because(
                sprintf('field "%s" must be a non-empty string.', $field),
            );
        }

        return $value;
    }

    /** @param array<string, mixed> $payload */
    private function nullableString(array $payload, string $field): ?string
    {
        $value = $payload[$field] ?? null;

        if ($value === null) {
            return null;
        }

        if (!is_string($value) && !is_int($value)) {
            throw MalformedResponse::because(
                sprintf('field "%s" must be a string, integer or null.', $field),
            );
        }

        return (string) $value;
    }
}
