<?php

declare(strict_types=1);

namespace Transip\Bundle\RestApi\Factory;

use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Transip\Bundle\RestApi\Exception\ApiTemporarilyUnavailable;
use Transip\Bundle\RestApi\Exception\InvalidToken;

use function array_key_exists;

/**
 * @internal
 */
final class ExceptionFactory
{
    /** @var string[] */
    private static array $errorMessages = [
        'Your access token is invalid.' => InvalidToken::class,
        'Internal error occurred, please contact our support' => ApiTemporarilyUnavailable::class,
    ];

    public static function createFromMessage(string $message, ResponseInterface $response): RuntimeException
    {
        if (array_key_exists($message, self::$errorMessages)) {
            return new self::$errorMessages[$message]($response);
        }

        return new RuntimeException($message);
    }
}
