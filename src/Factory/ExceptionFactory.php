<?php

declare(strict_types=1);

namespace Transip\Bundle\RestApi\Factory;

use Symfony\Component\Cache\Adapter\AdapterInterface;
use Transip\Api\Library\TransipAPI;
use Transip\Bundle\RestApi\Exception\InvalidTokenException;
use Transip\Bundle\RestApi\HttpClient\Builder;
use Transip\Bundle\RestApi\HttpClient\Adapter\GenericHttpClient;

/**
 * @internal
 */
class ExceptionFactory
{
    private static $errorMessages = [
        'Your access token is invalid.' => InvalidTokenException::class
    ];

    public static function createFromMessage(string $message)
    {
        if (array_key_exists($message, self::$errorMessages)) {
            return new self::$errorMessages[$message]();
        }

        new \RuntimeException($message);
    }
}
