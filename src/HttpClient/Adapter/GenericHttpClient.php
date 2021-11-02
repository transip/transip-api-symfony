<?php

declare(strict_types=1);

namespace Transip\Bundle\RestApi\HttpClient\Adapter;

use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\RequestException;
use Http\Client\Common\Plugin;
use Http\Discovery\UriFactoryDiscovery;
use JsonException;
use LogicException;
use Psr\Http\Message\ResponseInterface;
use Throwable;
use Transip\Api\Library\Exception\ApiException;
use Transip\Api\Library\Exception\HttpBadResponseException;
use Transip\Api\Library\Exception\HttpClientException;
use Transip\Api\Library\Exception\HttpRequestException;
use Transip\Api\Library\HttpClient\HttpClient;
use Transip\Api\Library\TransipAPI;
use Transip\Bundle\RestApi\HttpClient\Builder;
use Transip\Bundle\RestApi\HttpClient\Plugin\ExceptionThrower;
use Transip\Bundle\RestApi\HttpClient\Plugin\TokenAuthenticationPlugin;

use function count;
use function http_build_query;
use function json_decode;
use function json_encode;
use function json_last_error;
use function strpos;

use const JSON_ERROR_NONE;
use const JSON_THROW_ON_ERROR;

/**
 * @internal
 */
final class GenericHttpClient extends HttpClient
{
    private Builder $client;

    public function __construct(
        Builder $httpClientBuilder,
        ?string $endpoint = null
    ) {
        parent::__construct($endpoint ?? TransipAPI::TRANSIP_API_ENDPOINT);
        $this->client = $httpClientBuilder;

        $this->setupHttpBuilder();
    }

    private function setupHttpBuilder(): void
    {
        $uri = UriFactoryDiscovery::find()->createUri($this->endpoint);
        $this->client->addPlugin(new Plugin\AddHostPlugin($uri));
        $this->client->addPlugin(new Plugin\AddPathPlugin($uri));
        $this->client->addPlugin(new ExceptionThrower());
    }

    /**
     * Set authentication token.
     */
    public function setToken(string $token): void
    {
        // Remove any generic authentication plugin
        $this->client->removePlugin(TokenAuthenticationPlugin::class);

        // Add new Authentication plugin
        $this->client->addPlugin(new TokenAuthenticationPlugin($token));
    }

    /**
     * @param array<mixed, mixed> $query
     *
     * @return array<mixed, mixed>
     */
    public function get(string $url, array $query = []): array
    {
        if (count($query) > 0) {
            $url .= '?' . http_build_query($query);
        }

        return $this->getContent(
            $this->client->getHttpClient()->get($url)
        );
    }

    /**
     * @param array<mixed, mixed> $body
     */
    public function post(string $url, array $body = []): void
    {
        $this->client->getHttpClient()->post($url, [], $this->createBody($body));
    }

    /**
     * @param array<mixed, mixed> $body
     *
     * @return array<mixed, mixed>
     */
    public function postAuthentication(string $url, string $signature, array $body): array
    {
        try {
            $response = $this->client->getHttpClient()->post(
                $url,
                ['Signature' => $signature],
                json_encode($body, JSON_THROW_ON_ERROR)
            );
        } catch (Throwable $exception) {
            $this->handleException($exception);
        }

        if (! isset($response)) {
            throw new LogicException('Variable $response is not defined.');
        }

        if ($response->getStatusCode() !== 201) {
            throw ApiException::unexpectedStatusCode($response);
        }

        if ($response->getBody() === null) {
            throw ApiException::emptyResponse($response);
        }

        $responseBody = json_decode(
            (string)$response->getBody(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        if ($responseBody === null) {
            throw ApiException::malformedJsonResponse($response);
        }

        return $responseBody;
    }

    /**
     * @param array<mixed, mixed> $body
     */
    public function put(string $url, array $body): void
    {
        $this->client->getHttpClient()->put($url, [], $this->createBody($body));
    }

    /**
     * @param array<mixed, mixed> $body
     */
    public function patch(string $url, array $body): void
    {
        $this->client->getHttpClient()->patch($url, [], $this->createBody($body));
    }

    /**
     * @param array<mixed, mixed> $body
     */
    public function delete(string $url, array $body = []): void
    {
        $this->client->getHttpClient()->delete($url, [], $this->createBody($body));
    }

    /**
     * Tries to decode JSON object returned from server. If response is not of type `application/json` or the JSON can
     * not be decoded, the original data will be returned
     *
     * @return array<mixed, mixed>|string
     */
    private function getContent(ResponseInterface $response): array|string
    {
        $body = $response->getBody()->__toString();
        if (strpos($response->getHeaderLine('Content-Type'), 'application/json') === 0) {
            $content = json_decode($body, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $content;
            }
        }

        return $body;
    }

    /**
     * @param array<mixed, mixed> $data
     *
     * @throws JsonException
     */
    private function createBody(array $data): string
    {
        return json_encode($data, JSON_THROW_ON_ERROR);
    }

    private function handleException(Throwable $exception): void
    {
        if ($exception instanceof BadResponseException) {
            if ($exception->hasResponse()) {
                throw HttpBadResponseException::badResponseException($exception, $exception->getResponse());
            }

            // Guzzle misclassified curl exception as a client exception (so there is no response)
            throw HttpClientException::genericRequestException($exception);
        }

        if ($exception instanceof RequestException) {
            throw HttpRequestException::requestException($exception);
        }

        throw HttpClientException::genericRequestException($exception);
    }
}
