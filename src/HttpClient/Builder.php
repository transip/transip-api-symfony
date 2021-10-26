<?php

declare(strict_types=1);

namespace Transip\Bundle\RestApi\HttpClient;

use Http\Client\Common\HttpMethodsClient;
use Http\Client\Common\Plugin;
use Http\Client\Common\PluginClientFactory;
use Http\Client\HttpClient;
use Http\Message\RequestFactory;

/**
 * @internal
 */
class Builder
{
    /**
     * The object that sends HTTP messages.
     *
     * @var HttpClient
     */
    private $httpClient;

    /**
     * A HTTP client with all our plugins.
     *
     * @var HttpMethodsClient
     */
    private $pluginClient;

    /**
     * @var RequestFactory
     */
    private $requestFactory;

    /**
     * True if we should create a new Plugin client at next request.
     *
     * @var bool
     */
    private $httpClientModified = true;

    /**
     * @var Plugin[]
     */
    private $plugins = [];

    /**
     * Http headers.
     *
     * @var array
     */
    private $headers = [];

    /**
     * @param HttpClient     $httpClient
     * @param RequestFactory $requestFactory
     */
    public function __construct(
        HttpClient $httpClient,
        RequestFactory $requestFactory
    ) {
        $this->httpClient = $httpClient;
        $this->requestFactory = $requestFactory;
    }

    /**
     * @return HttpMethodsClient
     */
    public function getHttpClient()
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
     *
     * @param Plugin $plugin
     */
    public function addPlugin(Plugin $plugin)
    {
        $this->plugins[] = $plugin;
        $this->httpClientModified = true;
    }

    /**
     * Remove a plugin by its fully qualified class name (FQCN).
     *
     * @param string $fqcn
     */
    public function removePlugin($fqcn)
    {
        foreach ($this->plugins as $idx => $plugin) {
            if ($plugin instanceof $fqcn) {
                unset($this->plugins[$idx]);
                $this->httpClientModified = true;
            }
        }
    }

    /**
     * Clears used headers.
     */
    public function clearHeaders()
    {
        $this->headers = [];

        $this->removePlugin(Plugin\HeaderAppendPlugin::class);
        $this->addPlugin(new Plugin\HeaderAppendPlugin($this->headers));
    }

    /**
     * @param array $headers
     */
    public function addHeaders(array $headers)
    {
        $this->headers = array_merge($this->headers, $headers);

        $this->removePlugin(Plugin\HeaderAppendPlugin::class);
        $this->addPlugin(new Plugin\HeaderAppendPlugin($this->headers));
    }

    /**
     * @param string $header
     * @param string $headerValue
     */
    public function addHeaderValue($header, $headerValue)
    {
        if (!isset($this->headers[$header])) {
            $this->headers[$header] = $headerValue;
        } else {
            $this->headers[$header] = array_merge((array) $this->headers[$header], [$headerValue]);
        }

        $this->removePlugin(Plugin\HeaderAppendPlugin::class);
        $this->addPlugin(new Plugin\HeaderAppendPlugin($this->headers));
    }
}
