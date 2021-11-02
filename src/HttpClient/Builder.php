<?php

declare(strict_types=1);

namespace Transip\Bundle\RestApi\HttpClient;

use Http\Client\Common\HttpMethodsClient;
use Http\Client\Common\Plugin;
use Http\Client\Common\PluginClientFactory;
use Http\Client\HttpClient;
use Http\Message\RequestFactory;

use function array_merge;

/**
 * @internal
 */
final class Builder
{
    /**
     * The object that sends HTTP messages.
     */
    private HttpClient $httpClient;

    /**
     * A HTTP client with all our plugins.
     */
    private HttpMethodsClient $pluginClient;

    private RequestFactory $requestFactory;

    /**
     * True if we should create a new Plugin client at next request.
     */
    private bool $httpClientModified = true;

    /** @var Plugin[] */
    private array $plugins = [];

    /**
     * Http headers.
     *
     * @var array<string, string>
     */
    private array $headers = [];

    public function __construct(
        HttpClient $httpClient,
        RequestFactory $requestFactory
    ) {
        $this->httpClient     = $httpClient;
        $this->requestFactory = $requestFactory;
    }

    public function getHttpClient(): HttpMethodsClient
    {
        if ($this->httpClientModified) {
            $this->httpClientModified = false;

            $this->pluginClient = new HttpMethodsClient(
                (new PluginClientFactory())->createClient($this->httpClient, $this->plugins),
                $this->requestFactory
            );
        }

        return $this->pluginClient;
    }

    /**
     * Add a new plugin to the end of the plugin chain.
     */
    public function addPlugin(Plugin $plugin): void
    {
        $this->plugins[]          = $plugin;
        $this->httpClientModified = true;
    }

    /**
     * Remove a plugin by its fully qualified class name (FQCN).
     */
    public function removePlugin(string $fqcn): void
    {
        foreach ($this->plugins as $idx => $plugin) {
            if (! ($plugin instanceof $fqcn)) {
                continue;
            }

            unset($this->plugins[$idx]);
            $this->httpClientModified = true;
        }
    }

    /**
     * Clears used headers.
     */
    public function clearHeaders(): void
    {
        $this->headers = [];

        $this->removePlugin(Plugin\HeaderAppendPlugin::class);
        $this->addPlugin(new Plugin\HeaderAppendPlugin($this->headers));
    }

    /**
     * @param array<string, string> $headers
     */
    public function addHeaders(array $headers): void
    {
        $this->headers = array_merge($this->headers, $headers);

        $this->removePlugin(Plugin\HeaderAppendPlugin::class);
        $this->addPlugin(new Plugin\HeaderAppendPlugin($this->headers));
    }

    public function addHeaderValue(string $header, string $headerValue): void
    {
        if (! isset($this->headers[$header])) {
            $this->headers[$header] = $headerValue;
        } else {
            $this->headers[$header] = array_merge((array)$this->headers[$header], [$headerValue]);
        }

        $this->removePlugin(Plugin\HeaderAppendPlugin::class);
        $this->addPlugin(new Plugin\HeaderAppendPlugin($this->headers));
    }
}
