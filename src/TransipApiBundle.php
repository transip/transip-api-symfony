<?php

declare(strict_types=1);

namespace Transip\Bundle\RestApi;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Transip\Bundle\RestApi\DependencyInjection\TransipApiExtension;

/**
 * Holds settings
 */
final class TransipApiBundle extends Bundle
{
    public function getContainerExtension(): TransipApiExtension
    {
        return new TransipApiExtension();
    }
}
