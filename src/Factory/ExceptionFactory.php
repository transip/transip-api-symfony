<?php

declare(strict_types=1);

namespace Transip\Bundle\RestApi\Factory;

use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Transip\Bundle\RestApi\Exception\ApiTemporarilyUnavailableException;
use Transip\Bundle\RestApi\Exception\InvalidTokenException;

use function array_key_exists;

/**
 * @internal
 */
final class ExceptionFactory
{
    private static $errorMessages = [
        'Your access token is invalid.' => InvalidTokenException::class,
        'Internal error occurred, please contact our support' => ApiTemporarilyUnavailableException::class,
    ];

    public static function createFromMessage(string $message, ResponseInterface $response)
    {
        if (array_key_exists($message, self::$errorMessages)) {
            return new self::$errorMessages[$message]($response);
        }

        return new RuntimeException($message);
    }
}
