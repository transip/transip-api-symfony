<?php

declare(strict_types=1);

namespace Transip\Bundle\RestApi\Exception;

use Psr\Http\Message\ResponseInterface;
use Transip\Api\Library\Exception\ApiException;

final class InvalidTokenException extends ApiException
{
    public function __construct(ResponseInterface $response, int $code = 0)
    {
        parent::__construct('Access token is invalid.', $code, $response);
    }
}
