<?php

declare(strict_types=1);

namespace Transip\Bundle\RestApi\Exception;

use Psr\Http\Message\ResponseInterface;
use Transip\Api\Library\Exception\ApiException;

final class ApiTemporarilyUnavailableException extends ApiException
{
    public function __construct(ResponseInterface $response, int $code = 0)
    {
        parent::__construct('API Temporarily unavailable Exception', $code, $response);
    }
}
