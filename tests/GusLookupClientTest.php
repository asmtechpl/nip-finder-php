<?php
declare(strict_types=1);
namespace NipFinder\Tests;
use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\Response;
use NipFinder\Configuration\Authentication;
use NipFinder\Configuration\GusClientConfig;
use NipFinder\Exception\ApiError;
use NipFinder\GusLookupClient;
use NipFinder\Http\GusRequestFactory;
use NipFinder\Tests\Support\FakeHttpClient;
use NipFinder\ValueObject\Nip;
use PHPUnit\Framework\TestCase;
final class GusLookupClientTest extends TestCase
{
    public function testItBuildsAuthenticatedRequestAndMapsResponse(): void
    {
        $body = json_encode(['data' => ['nip' => '1234563218', 'regon' => 123456789, 'name' => 'Acme', 'city' => 'Warszawa', 'street' => 'Marszałkowska', 'houseNumber' => '10', 'apartmentNumber' => '5']], JSON_THROW_ON_ERROR);
        $http = new FakeHttpClient(new Response(200, ['Content-Type' => 'application/json'], $body));
        $config = new GusClientConfig(
            baseUrl: 'https://gus.example.test/',
            authentication: new Authentication(token: 'secret', apiKey: 'key'),
        );
        $client = new GusLookupClient(
            $http,
            new GusRequestFactory(new HttpFactory(), $config),
        );
        $result = $client->findByNip(new Nip('1234563218'));
        self::assertSame('Acme', $result->name);
        self::assertSame('Marszałkowska 10/5', $result->address->streetLine());
        self::assertSame('https://gus.example.test/api/v1/gus?nip=1234563218', (string) $http->lastRequest?->getUri());
        self::assertSame('Bearer secret', $http->lastRequest?->getHeaderLine('Authorization'));
        self::assertSame('key', $http->lastRequest?->getHeaderLine('X-API-KEY'));
    }
    public function testItExposesUpstreamStatusAndBody(): void
    {
        $http = new FakeHttpClient(new Response(429, [], '{"message":"limit"}'));
        $config = new GusClientConfig('https://gus.example.test');
        $client = new GusLookupClient(
            $http,
            new GusRequestFactory(new HttpFactory(), $config),
        );
        try { $client->findByNip(new Nip('1234563218')); self::fail('Exception was not thrown.'); }
        catch (ApiError $error) { self::assertSame(429, $error->statusCode); self::assertSame('{"message":"limit"}', $error->responseBody); }
    }
}
