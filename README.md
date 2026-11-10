# NIP Finder

Niezależny od frameworka klient PHP do pośredniego HTTP API, które zwraca znormalizowane dane GUS. Biblioteka **nie łączy się z SOAP BIR** i nie zarządza sesją oficjalnego API GUS.

## Wymagania i instalacja

PHP 8.2+. Biblioteka dostarcza i konfiguruje transport HTTP:

```bash
composer require nip-finder/nip-finder
```

## Użycie

```php
use NipFinder\GusLookupClientFactory;
use NipFinder\ValueObject\Nip;

$client = GusLookupClientFactory::fromEnvironment();
$contractor = $client->findByNip(new Nip('123-456-32-18'));

echo $contractor->name;
echo $contractor->address->streetLine();
```

Fabryka automatycznie odczytuje `GUS_API_*` z `$_ENV`, `$_SERVER` lub `getenv()`. Laravel i Symfony ładują swoje pliki `.env` przed utworzeniem klienta. W czystym PHP zmienne muszą być ustawione w środowisku procesu albo załadowane np. przez `vlucas/phpdotenv`.

`Nip` usuwa spacje i myślniki oraz sprawdza długość, cyfry i sumę kontrolną. Wynik to niemutowalne DTO `ContractorData` z zagnieżdżonym `AddressData`.

## Autoryzacja

`GUS_API_TOKEN` generuje nagłówek `Authorization: {tokenType} {token}`, a `GUS_API_KEY` generuje `X-API-KEY`. Jeśli ustawiono oba, wysłane zostaną oba.

## Format odpowiedzi

Akceptowany jest zarówno obiekt danych, jak i obiekt opakowany w `data`:

```json
{
  "data": {
    "nip": "1234563218",
    "regon": "123456789",
    "name": "Przykładowa Firma Sp. z o.o.",
    "voivodeship": "mazowieckie",
    "district": "Warszawa",
    "city": "Warszawa",
    "municipality": "Warszawa",
    "postalCode": "00-001",
    "street": "Marszałkowska",
    "houseNumber": "10",
    "apartmentNumber": "5"
  }
}
```

`nip` i `name` są wymagane. Pozostałe pola mogą być `null`.

## Obsługa błędów

Wszystkie wyjątki biblioteki implementują `NipFinderException`:

- `InvalidNip` — niepoprawny format lub suma kontrolna NIP;
- `InvalidConfiguration` — błędna konfiguracja klienta;
- `TransportError` — timeout, DNS lub inny błąd transportu PSR-18;
- `ApiError` — odpowiedź spoza zakresu 2xx; udostępnia `statusCode` i `responseBody`;
- `MalformedResponse` — niepoprawny JSON lub niezgodny kontrakt odpowiedzi.

Biblioteka nie loguje samodzielnie i nie tłumaczy wyjątków na odpowiedzi HTTP. Te decyzje należą do warstwy aplikacji.

## Laravel

Laravel automatycznie wykrywa provider pakietu. Nie dodawaj własnego providera, fabryki ani wpisu do `config/services.php`. Wystarczy konfiguracja `.env`.

W kontrolerze wstrzyknij kontrakt:

```php
use NipFinder\Contract\ContractorLookup;
use NipFinder\ValueObject\Nip;

final class ContractorController
{
    public function __construct(
        private readonly ContractorLookup $gus,
    ) {}

    public function lookup(string $nip): array
    {
        $contractor = $this->gus->findByNip(new Nip($nip));

        return [
            'nip' => $contractor->nip,
            'regon' => $contractor->regon,
            'name' => $contractor->name,
            'city' => $contractor->address->city,
            'postalCode' => $contractor->address->postalCode,
            'street' => $contractor->address->streetLine(),
        ];
    }
}
```

Provider automatycznie rejestruje `ContractorLookup` jako singleton i korzysta z konfiguracji zgodnej z `config:cache`. Możesz też wstrzyknąć konkretny `GusLookupClient`, ale zależność od kontraktu jest preferowana.

## Symfony

W Symfony i czystym PHP możesz wywołać `GusLookupClientFactory::fromEnvironment()` bez dodatkowej konfiguracji transportu. Dostępne jest również `GusLookupClientFactory::fromArray()` oraz publiczne konstruktory konfiguracji i klienta, jeśli chcesz przekazać własne wartości albo transport PSR-18.

## Rozwój

```bash
composer install
composer test
```
