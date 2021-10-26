<?php
declare(strict_types=1);

namespace Transip\Bundle\RestApi\Exception;

use Throwable;

class InvalidTokenException extends ApiException
{
    public function __construct()
    {
        parent::__construct('Access token is invalid.');
    }
}
