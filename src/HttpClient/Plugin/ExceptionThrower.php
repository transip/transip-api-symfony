<?php

namespace Transip\Bundle\RestApi\HttpClient\Plugin;

use Http\Client\Common\Plugin;
use Psr\Http\Message\RequestInterface;
use Transip\Bundle\RestApi\Factory\ExceptionFactory;

class ExceptionThrower implements Plugin
{
    use Plugin\VersionBridgePlugin;

    /**
     * {@inheritdoc}
     */
    public function doHandleRequest(?RequestInterface $request, callable $next, callable $first)
    {
        return $next($request)->then(function ($response) {
            if ($response->getStatusCode() < 400 || $response->getStatusCode() > 600) {
                return $response;
            }

            try {
                $responseData = json_decode((string)$response->getBody(), true);
                if ($responseData['error'] ?? null) {
                    $error = $responseData['error'];
                } else {
                    $error = (string)$response->getBody();
                }
            } catch (\Throwable $t) {
                $error = (string)$response->getBody();
            }

            throw ExceptionFactory::createFromMessage($error, $response);
        });
    }
}
