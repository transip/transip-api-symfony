<?php

declare(strict_types=1);

namespace Transip\Bundle\RestApi\HttpClient\Plugin;

use Http\Client\Common\Plugin;
use Psr\Http\Message\RequestInterface;

use function sprintf;

final class TokenAuthenticationPlugin implements Plugin
{
    use Plugin\VersionBridgePlugin;

    public function __construct(private string $token, private string $userAgent = 'transip-api-symfony-bundle')
    {
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
