<?php

namespace Transip\Bundle\RestApi\HttpClient\Plugin;

use Http\Client\Common\Plugin;
use Psr\Http\Message\RequestInterface;

class TokenAuthenticationPlugin implements Plugin
{
    use Plugin\VersionBridgePlugin;

    private string $token;
    private string $userAgent;

    public function __construct(string $token, string $userAgent = 'transip-api-symfony-bundle')
    {
        $this->token = $token;
        $this->userAgent = $userAgent;
    }

    /**
     * {@inheritdoc}
     */
    public function doHandleRequest(RequestInterface $request, callable $next, callable $first)
    {
        $request = $request->withHeader('Authorization', sprintf('Bearer %s', $this->token))
            ->withHeader('User-Agent', $this->userAgent);

        return $next($request);
    }
}
