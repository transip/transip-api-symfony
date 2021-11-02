<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Transip\Api\Library\TransipAPI;

return static function (ContainerConfigurator $configurator): void {
    $services = $configurator->services();

    // Register TransIP API class
    $services->set('transip.client', TransipAPI::class)
        ->alias(TransipAPI::class, 'transip.client');
};
