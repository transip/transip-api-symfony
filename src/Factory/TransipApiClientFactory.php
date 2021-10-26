<?php

declare(strict_types=1);

namespace Transip\Bundle\RestApi\Factory;

use Symfony\Component\Cache\Adapter\AdapterInterface;
use Transip\Api\Library\TransipAPI;
use Transip\Bundle\RestApi\HttpClient\Builder;
use Transip\Bundle\RestApi\HttpClient\Adapter\GenericHttpClient;

/**
 * @internal
 */
class TransipApiClientFactory
{
    private Builder $clientBuilder;
    private AdapterInterface $cache;

    public function __construct(
        Builder $clientBuilder,
        AdapterInterface $cache
    ) {
        $this->clientBuilder = $clientBuilder;
        $this->cache         = $cache;
    }

    public function createApiClient(): TransipAPI
    {
        $clientAdapter = new GenericHttpClient($this->clientBuilder);
        return new TransipAPI(
            '',
            '',
            true,
            '',
            '',
            $this->cache,
            $clientAdapter
        );
    }
}
