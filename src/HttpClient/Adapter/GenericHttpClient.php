<?php

declare(strict_types=1);

namespace Transip\Bundle\RestApi\HttpClient\Adapter;

use Http\Discovery\UriFactoryDiscovery;
use Psr\Http\Message\ResponseInterface;
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
        // TODO: Implement post() method.
    }

    public function postAuthentication(string $url, string $signature, array $body): array
    {
        // TODO: Implement postAuthentication() method.
    }

    public function put(string $url, array $body): void
    {
        // TODO: Implement put() method.
    }

    public function patch(string $url, array $body): void
    {
        // TODO: Implement patch() method.
    }

    public function delete(string $url, array $body = []): void
    {
        // TODO: Implement delete() method.
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
}
