<?php

declare(strict_types=1);

namespace Transip\Bundle\RestApi\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

/**
 * OneUI extension
 */
final class TransipApiExtension extends ConfigurableExtension
{
    /**
     * @param mixed[] $mergedConfig
     */
    protected function loadInternal(
        array $mergedConfig,
        ContainerBuilder $container
    ): void {
        // TODO: Do configuration set-up here
    }
}
