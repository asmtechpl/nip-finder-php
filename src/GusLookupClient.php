<?php

declare(strict_types=1);

namespace NipFinder;

use JsonException;
use NipFinder\Contract\ContractorLookup;
use NipFinder\DTO\ContractorData;
use NipFinder\Exception\ApiError;
use NipFinder\Exception\MalformedResponse;
use NipFinder\Exception\TransportError;
use NipFinder\Http\GusRequestFactory;
use NipFinder\Response\ContractorResponseMapper;
use NipFinder\ValueObject\Nip;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final readonly class GusLookupClient implements ContractorLookup
{
    public function __construct(
        private ClientInterface $httpClient,
        private GusRequestFactory $requestFactory,
        private ContractorResponseMapper $mapper = new ContractorResponseMapper(),
    ) {}

    public function findByNip(Nip $nip): ContractorData
    {
        $request = $this->requestFactory->createForNip($nip);
        $response = $this->sendRequest($request);
        $payload = $this->decodeResponse($response);

        return $this->mapper->map($payload);
    }

    private function sendRequest(RequestInterface $request): ResponseInterface
    {
        try {
            return $this->httpClient->sendRequest($request);
        } catch (ClientExceptionInterface $exception) {
            throw TransportError::fromClientException($exception);
        }
    }

    /** @return array<string, mixed> */
    private function decodeResponse(ResponseInterface $response): array
    {
        $body = (string) $response->getBody();

        if (!$this->isSuccessful($response)) {
            throw new ApiError($response->getStatusCode(), $body);
        }

        try {
            $payload = json_decode(
                $body,
                associative: true,
                flags: JSON_THROW_ON_ERROR,
            );
        } catch (JsonException $exception) {
            throw MalformedResponse::because(
                'body is not valid JSON.',
                $exception,
            );
        }

        if (!is_array($payload) || array_is_list($payload)) {
            throw MalformedResponse::because('JSON root must be an object.');
        }

        return $payload;
    }

    private function isSuccessful(ResponseInterface $response): bool
    {
        return $response->getStatusCode() >= 200
            && $response->getStatusCode() < 300;
    }

}
