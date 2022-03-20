<?php

declare(strict_types=1);

namespace Transip\Bundle\RestApi\Tests\HttpClient;

use Http\Client\Common\HttpMethodsClient;
use Http\Client\Common\Plugin\HeaderAppendPlugin;
use Transip\Bundle\RestApi\HttpClient\Builder;
use Transip\Bundle\RestApi\Tests\TestCase;

final class BuilderTest extends TestCase
{
    public function testShouldClearHeaders(): void
    {
        $builder = $this->getMockBuilder(Builder::class)
            ->setMethods(['addPlugin', 'removePlugin'])
            ->getMock();

        $builder->expects($this->once())
            ->method('addPlugin')
            ->with($this->isInstanceOf(HeaderAppendPlugin::class));

        $builder->expects($this->once())
            ->method('removePlugin')
            ->with(HeaderAppendPlugin::class);

        $builder->clearHeaders();
    }

    public function testShouldAddHeaders(): void
    {
        $headers = ['header1', 'header2'];

        $client = $this->getMockBuilder(Builder::class)
            ->setMethods(['addPlugin', 'removePlugin'])
            ->getMock();
        $client->expects($this->once())
            ->method('addPlugin')
            // TODO verify that headers exists
            ->with($this->isInstanceOf(HeaderAppendPlugin::class));

        $client->expects($this->once())
            ->method('removePlugin')
            ->with(HeaderAppendPlugin::class);

        $client->addHeaders($headers);
    }

    public function testAppendingHeaderShouldAddAndRemovePlugin(): void
    {
        $expectedHeaders = ['Test' => 'testing-1-2-3'];

        $client = $this->getMockBuilder(Builder::class)
            ->setMethods(['addPlugin', 'removePlugin'])
            ->getMock();

        $client->expects($this->once())
            ->method('removePlugin')
            ->with(HeaderAppendPlugin::class);

        $client->expects($this->once())
            ->method('addPlugin')
            ->with(new HeaderAppendPlugin($expectedHeaders));

        $client->addHeaderValue('Test', 'testing-1-2-3');
    }

    public function testGetHttpClient(): void
    {
        $builder = new Builder();
        self::assertInstanceOf(HttpMethodsClient::class, $builder->getHttpClient());
    }
}
