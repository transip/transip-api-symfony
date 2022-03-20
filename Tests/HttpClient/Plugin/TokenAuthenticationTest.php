<?php

declare(strict_types=1);

namespace Transip\Bundle\RestApi\Tests\HttpClient\Plugin;

use GuzzleHttp\Psr7\Request;
use Http\Promise\FulfilledPromise;
use RuntimeException;
use Transip\Bundle\RestApi\HttpClient\Plugin\TokenAuthenticationPlugin;
use Transip\Bundle\RestApi\Tests\TestCase;

use function sprintf;

final class TokenAuthenticationTest extends TestCase
{
    public function testAuthenticationMethods(): void
    {
        $token   = 'SOME-TOKEN-HERE';
        $request = new Request('GET', '/');
        $plugin  = new TokenAuthenticationPlugin($token);

        /** @var Request $newRequest */
        $newRequest = null;
        $plugin->handleRequest($request, static function ($request) use (&$newRequest) {
            /** @var Request $newRequest */
            $newRequest = $request;

            return new FulfilledPromise('FOO');
        }, static function (): void {
            throw new RuntimeException('Did not expect plugin to call first');
        });

        $this->assertNotNull($newRequest);
        $this->assertContains(sprintf('Bearer %s', $token), $newRequest->getHeader('Authorization'));
    }
}
