<?php

declare(strict_types=1);

namespace Transip\Bundle\RestApi\HttpClient\Adapter;

use Exception;
use Http\Discovery\UriFactoryDiscovery;
use JsonException;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Transip\Api\Library\Exception\ApiException;
use Transip\Api\Library\HttpClient\HttpClient;
use Transip\Api\Library\TransipAPI;
use Http\Client\Common\Plugin;
use Transip\Bundle\RestApi\HttpClient\Builder;
use Transip\Bundle\RestApi\HttpClient\Plugin\TokenAuthenticationPlugin;

/**
 * @internal
 */
class GenericHttpClient extends HttpClient
{
    private Builder $client;

    /**
     * @param Builder $httpClientBuilder
     * @param string|null $endpoint
     */
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

    public function get(string $url, array $query = []): array
    {
        if (count($query) > 0) {
            $url .= '?' . http_build_query($query);
        }

        return $this->getContent(
            $this->client->getHttpClient()->get($url)
        );
    }

    public function post(string $url, array $body = []): void
    {
        $this->client->getHttpClient()->post($url, [], $this->createBody($body));
    }

    /**
     * @throws \Http\Client\Exception
     * @throws JsonException
     */
    public function postAuthentication(string $url, string $signature, array $body): array
    {
        try {
            $response = $this->client->getHttpClient()->post(
                $url,
                ['Signature' => $signature],
                json_encode($body, JSON_THROW_ON_ERROR)
            );
        } catch (Exception $exception) {
            $this->handleException($exception);
        }

        if ($response->getStatusCode() !== 201) {
            throw ApiException::unexpectedStatusCode($response);
        }

        if ($response->getBody() == null) {
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

    public function put(string $url, array $body): void
    {
        $this->client->getHttpClient()->put($url, [], $this->createBody($body));
    }

    public function patch(string $url, array $body): void
    {
        $this->client->getHttpClient()->patch($url, [], $this->createBody($body));
    }

    public function delete(string $url, array $body = []): void
    {
        $this->client->getHttpClient()->delete($url, [], $this->createBody($body));
    }

    /**
     * Tries to decode JSON object returned from server. If response is not of type `application/json` or the JSON can
     * not be decoded, the original data will be returned
     *
     * @param ResponseInterface $response
     *
     * @return array|string
     */
    private function getContent(ResponseInterface $response)
    {
        $body = $response->getBody()->__toString();
        if (strpos($response->getHeaderLine('Content-Type'), 'application/json') === 0) {
            $content = json_decode($body, true);
            if (JSON_ERROR_NONE === json_last_error()) {
                return $content;
            }
        }

        return $body;
    }

    /**
     * @param array $data
     * @return string
     * @throws JsonException
     */
    private function createBody(array $data): string
    {
        return json_encode($data, JSON_THROW_ON_ERROR);
    }
}
